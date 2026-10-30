<?php

namespace App\Services;

class StudentImportColumns
{
    public const FIELDS = [
        'school_id_number' => 'Student ID',
        'first_name' => 'First name',
        'middle_name' => 'Middle name',
        'last_name' => 'Last name',
        'email' => 'Email',
        'program' => 'Program',
        'year_level' => 'Year level',
        'contact_num' => 'Contact number',
        'date_of_birth' => 'Date of birth',
        'gender' => 'Gender',
    ];

    private const ALIASES = [
        'school_id_number' => ['student id', 'student id number', 'school id', 'school id number', 'id number', 'id no', 'id'],
        'first_name' => ['first name', 'given name', 'firstname', 'given names'],
        'middle_name' => ['middle name', 'middle initial', 'middlename'],
        'last_name' => ['last name', 'family name', 'surname', 'lastname'],
        'email' => ['email', 'email address', 'e mail', 'e mail address'],
        'program' => ['program', 'programme', 'course', 'degree program'],
        'year_level' => ['year', 'year level', 'grade level'],
        'contact_num' => ['contact number', 'contact no', 'phone', 'phone number', 'mobile', 'mobile number'],
        'date_of_birth' => ['date of birth', 'birth date', 'birthdate', 'dob', 'birthday'],
        'gender' => ['gender', 'sex'],
    ];

    public function detect(array $headers): array
    {
        $mapping = [];
        $used = [];
        foreach ($headers as $index => $header) {
            $normalized = $this->normalize((string) $header);
            foreach (self::ALIASES as $field => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    if (isset($used[$field])) {
                        $mapping[$used[$field]] = '';
                        $mapping[$index] = '';
                    } else {
                        $mapping[$index] = $field;
                        $used[$field] = $index;
                    }
                    continue 2;
                }
            }
            $mapping[$index] = '';
        }
        return $mapping;
    }

    public function headerRow(array $rows): ?int
    {
        $best = null;
        $bestCount = 0;
        foreach (array_slice($rows, 0, 10, true) as $index => $row) {
            $count = count(array_filter($this->detect($row)));
            if ($count > $bestCount) {
                $best = $index;
                $bestCount = $count;
            }
        }
        return $bestCount >= 2 ? $best : null;
    }

    private function normalize(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $header = mb_strtolower(trim($header));
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $header));
    }
}
