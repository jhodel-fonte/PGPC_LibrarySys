<?php

namespace App\Services;

use App\Models\StudentImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentSpreadsheetImportService
{
    public function __construct(private CatalogSpreadsheetImportService $reader, private StudentImportColumns $columns) {}

    public function rows(StudentImport $import): \Generator
    {
        $path = Storage::disk('local')->path($import->file_path);
        $extension = strtolower(pathinfo($import->filename, PATHINFO_EXTENSION));
        if ($extension === 'csv') {
            yield from $this->reader->readCsvRows($path);
        } else {
            $this->reader->guardSpreadsheetMemory($path, $extension);
            yield from $this->reader->readSpreadsheetRows($path);
        }
    }

    public function inspect(StudentImport $import): void
    {
        $sample = [];
        foreach ($this->rows($import) as $row) {
            $sample[] = $row;
            if (count($sample) === 10) break;
        }
        $index = $this->columns->headerRow($sample);
        if ($index === null) throw new \RuntimeException('No recognizable header row found in the first 10 rows.');
        $headers = array_map(fn ($value) => trim((string) $value), $sample[$index]);
        $import->update(['header_row' => $index, 'headers' => $headers, 'mapping' => $this->columns->detect($headers)]);
    }

    public function stage(StudentImport $import, array $mapping): void
    {
        $fields = array_filter($mapping, fn ($field) => $field !== '');
        if (count($fields) !== count(array_unique($fields)) || array_diff($fields, array_keys(StudentImportColumns::FIELDS))) {
            throw new \RuntimeException('Each field may only be mapped once.');
        }
        foreach (['school_id_number', 'first_name', 'last_name'] as $required) {
            if (! in_array($required, $fields, true)) throw new \RuntimeException('Map student ID, first name, and last name before reviewing.');
        }
        DB::transaction(function () use ($import, $mapping) {
            $import->rows()->delete();
            $seenIds = []; $seenEmails = []; $batch = [];
            foreach ($this->rows($import) as $index => $row) {
                if ($index <= $import->header_row || ! array_filter($row, fn ($v) => trim((string) $v) !== '')) continue;
                $data = [];
                foreach ($mapping as $column => $field) {
                    if ($field !== '') $data[$field] = trim((string) ($row[$column] ?? ''));
                }
                $data['email'] = mb_strtolower($data['email'] ?? '');
                $data['gender'] = $this->normalizeGender($data['gender'] ?? null);
                $validator = Validator::make($data, [
                    'school_id_number' => 'required|string|max:50|unique:students,school_id_number',
                    'first_name' => 'required|string|max:100', 'last_name' => 'required|string|max:100',
                    'middle_name' => 'nullable|string|max:100', 'email' => 'nullable|email:rfc|max:255|unique:accounts,email',
                    'program' => 'nullable|string|max:255', 'year_level' => 'nullable|string|max:50',
                    'contact_num' => 'nullable|string|max:20', 'gender' => 'nullable|in:Male,Female',
                    'date_of_birth' => ['nullable', 'regex:/^(0[1-9]|1[0-2])-\d{4}$/'],
                ]);
                $errors = $validator->errors()->all();
                $id = mb_strtolower($data['school_id_number'] ?? ''); $email = $data['email'];
                $username = UserService::generateDefaultUsername($data, 'student');
                if ($id !== '' && ! $validator->errors()->has('school_id_number') && DB::table('students')->whereRaw('LOWER(school_id_number) = ?', [$id])->exists()) $errors[] = 'Student ID is already registered.';
                if ($email !== '' && ! $validator->errors()->has('email') && DB::table('accounts')->whereRaw('LOWER(email) = ?', [$email])->exists()) $errors[] = 'Email is already registered.';
                if ($username !== '' && DB::table('accounts')->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])->exists()) $errors[] = 'Generated username is already in use.';
                if ($id !== '' && isset($seenIds[$id])) $errors[] = 'Duplicate student ID in this file.';
                if ($email !== '' && isset($seenEmails[$email])) $errors[] = 'Duplicate email in this file.';
                $seenIds[$id] = true; $seenEmails[$email] = true;
                $batch[] = ['student_import_id' => $import->id, 'row_number' => $index + 1, 'data' => json_encode($data, JSON_THROW_ON_ERROR),
                    'status' => $errors ? 'invalid' : 'pending', 'error' => $errors ? implode(' ', $errors) : null,
                    'created_at' => now(), 'updated_at' => now()];
                if (count($batch) === 100) { DB::table('student_import_rows')->insert($batch); $batch = []; }
            }
            if ($batch) DB::table('student_import_rows')->insert($batch);
            $import->update(['mapping' => $mapping, 'status' => 'reviewed']);
        });
    }

    private function normalizeGender(?string $gender): ?string
    {
        return match (mb_strtolower(trim($gender ?? ''))) {
            '' => null,
            'm', 'male', 'lalaki' => 'Male',
            'f', 'female', 'babae' => 'Female',
            default => trim($gender),
        };
    }
}
