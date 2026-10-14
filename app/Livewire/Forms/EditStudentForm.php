<?php

namespace App\Livewire\Forms;

use App\Models\Account;
use App\Models\Student;
use App\Traits\SanitizesRequestInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditStudentForm extends Component
{
    use WithFileUploads, SanitizesRequestInput;

    public int|string $userId;
    public ?int $studentId = null;

    // Personal Information
    public string $firstName = '';
    public ?string $middleName = '';
    public string $lastName = '';
    public string $gender = '';
    public ?string $dateOfBirth = '';
    public ?string $contactNum = '';
    public string $email = '';

    // Academic & Enrollment Details
    public string $schoolIdNumber = '';
    public string $program = '';
    public string $yearLevel = '';
    public ?string $enrollmentStatus = 'Regular';
    public string $enrollmentYear = '';
    public string $libraryStatus = '1';

    // Remarks & Photo
    public ?string $note = '';
    public $photo = null;
    public ?string $existingPhotoUrl = null;
    public bool $removeExistingPhoto = false;

    // UI state
    public string $errorMessage = '';

    public function mount($userId): void
    {
        $this->userId = $userId;
        $account = Account::with('student')->findOrFail($this->userId);
        $student = $account->student;

        if (! $student) {
            abort(404, 'Student record not found for this user account.');
        }

        $this->authorize('update', $student);

        $this->studentId = $student->id;
        $this->firstName = $student->first_name ?? '';
        $this->middleName = $student->middle_name ?? '';
        $this->lastName = $student->last_name ?? '';
        $this->gender = $student->gender ? ucfirst(strtolower($student->gender)) : '';
        $this->contactNum = $student->contact_num ?? '';
        $this->email = $account->email ?? '';
        $this->schoolIdNumber = $student->school_id_number ?? '';
        $this->program = $student->program ?? '';
        $this->yearLevel = $student->year_level ?? '';
        $this->enrollmentStatus = $student->enrollment_status ?? 'Regular';
        $this->enrollmentYear = (string) ($student->enrollment_year ?? date('Y'));
        $this->libraryStatus = (string) ($student->library_status_id ?? '1');
        $this->note = $student->note ?? '';
        $rawImage = $student->getRawOriginal('image_url');
        $this->existingPhotoUrl = $rawImage ? $student->image_url : null;

        // Convert MM-YYYY to YYYY-MM for the datepicker if needed
        if ($student->date_of_birth) {
            if (preg_match('/^(\d{2})-(\d{4})$/', $student->date_of_birth, $m)) {
                $this->dateOfBirth = "{$m[2]}-{$m[1]}";
            } else {
                $this->dateOfBirth = $student->date_of_birth;
            }
        }
    }

    /**
     * Parse a month/year value into [month, year].
     * Accepts YYYY-MM (TallStackUI month-year-only output), YYYY-MM-DD, or MM-YYYY.
     */
    protected function parseMonthYear(string $value): ?array
    {
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])(?:-\d{2})?$/', $value, $m)) {
            return [(int) $m[2], (int) $m[1]];
        }

        if (preg_match('/^(0[1-9]|1[0-2])-(\d{4})$/', $value, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        return null;
    }

    /**
     * Normalize date of birth to the stored MM-YYYY format.
     */
    protected function normalizedDateOfBirth(): ?string
    {
        if (empty($this->dateOfBirth)) {
            return null;
        }

        $parts = $this->parseMonthYear($this->dateOfBirth);

        return $parts ? sprintf('%02d-%04d', $parts[0], $parts[1]) : null;
    }

    protected function allowedPrograms(): array
    {
        return collect(config('settings.college.programs', []))
            ->map(fn($name, $code) => "{$code} - {$name}")
            ->values()
            ->all();
    }

    public function rules(): array
    {
        $maxSetting = config('settings.user_images.max_size', config('settings.user_image_max_size', '5MB'));
        $maxKb = $this->parseSizeToKilobytes($maxSetting);
        $currentYear = (int) date('Y');

        return [
            // Personal Information
            'firstName' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-.\']+$/u'],
            'middleName' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\-.\']+$/u'],
            'lastName' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-.\']+$/u'],
            'gender' => ['required', 'string', Rule::in(['Male', 'Female'])],
            'dateOfBirth' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($currentYear) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $parts = $this->parseMonthYear((string) $value);
                    if ($parts === null) {
                        $fail('Date of Birth must be a valid month and year (e.g. 05-2004).');
                        return;
                    }

                    [$month, $year] = $parts;
                    if ($year < 1920 || $year > $currentYear || ($year === $currentYear && $month > (int) date('n'))) {
                        $fail("Date of Birth must be between 1920 and the current month.");
                    }
                },
            ],
            'contactNum' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('accounts', 'email')->ignore($this->userId)],

            // Academic & Enrollment Details
            'schoolIdNumber' => ['required', 'string', 'max:50', Rule::unique('students', 'school_id_number')->ignore($this->studentId)],
            'program' => ['required', 'string', Rule::in($this->allowedPrograms())],
            'yearLevel' => ['required', 'string', Rule::in(['1st Year', '2nd Year', '3rd Year', '4th Year'])],
            'enrollmentStatus' => ['nullable', 'string', Rule::in(['Regular', 'Irregular', 'Transferee', 'Returnee'])],
            'enrollmentYear' => ['required', 'digits:4', 'integer', 'between:2000,' . ($currentYear + 1)],
            'libraryStatus' => ['required', 'exists:library_statuses,id'],

            // Remarks & Profile Picture
            'note' => ['nullable', 'string', 'max:300'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', "max:{$maxKb}"],
        ];
    }

    protected function messages(): array
    {
        return [
            'schoolIdNumber.required' => 'School ID Number is required.',
            'schoolIdNumber.unique' => 'This School ID Number is already registered.',
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already associated with an existing account.',
            'firstName.required' => 'First Name is required.',
            'firstName.regex' => 'First Name may only contain letters, spaces, hyphens, and apostrophes.',
            'middleName.regex' => 'Middle Name may only contain letters, spaces, hyphens, and apostrophes.',
            'lastName.required' => 'Last Name is required.',
            'lastName.regex' => 'Last Name may only contain letters, spaces, hyphens, and apostrophes.',
            'gender.required' => 'Please select a gender.',
            'gender.in' => 'Gender must be either Male or Female.',
            'contactNum.regex' => 'Please enter a valid contact number format.',
            'program.required' => 'Please select a degree program.',
            'program.in' => 'The selected degree program is invalid.',
            'yearLevel.required' => 'Please select a year level.',
            'yearLevel.in' => 'The selected year level is invalid.',
            'enrollmentStatus.in' => 'The selected enrollment status is invalid.',
            'enrollmentYear.required' => 'Enrollment Year is required.',
            'enrollmentYear.digits' => 'Enrollment Year must be a 4-digit year.',
            'enrollmentYear.between' => 'Enrollment Year must be between 2000 and next year.',
            'libraryStatus.required' => 'Please select a library standing.',
            'libraryStatus.exists' => 'The selected library standing is invalid.',
            'note.max' => 'Notes and remarks cannot exceed 300 characters.',
            'photo.image' => 'The uploaded file must be a valid image (PNG, JPG, or WEBP).',
            'photo.mimes' => 'The profile picture must be a file of type: png, jpg, jpeg, webp.',
            'photo.max' => 'The profile picture size cannot exceed 5MB.',
        ];
    }

    protected function sanitizeInputs(): void
    {
        $this->firstName = (string) $this->cleanString($this->firstName);
        $this->middleName = $this->cleanString($this->middleName, nullable: true);
        $this->lastName = (string) $this->cleanString($this->lastName);
        $this->gender = (string) $this->cleanString($this->gender);
        $this->dateOfBirth = $this->cleanString($this->dateOfBirth, nullable: true);
        $this->contactNum = $this->cleanString($this->contactNum, nullable: true);
        $this->email = (string) $this->cleanString($this->email);
        $this->schoolIdNumber = (string) $this->cleanString($this->schoolIdNumber);
        $this->program = (string) $this->cleanString($this->program);
        $this->yearLevel = (string) $this->cleanString($this->yearLevel);
        $this->enrollmentStatus = $this->cleanString($this->enrollmentStatus, nullable: true);
        $this->enrollmentYear = (string) $this->cleanString($this->enrollmentYear);
        $this->libraryStatus = (string) $this->cleanString($this->libraryStatus);
        $this->note = $this->cleanString($this->note, nullable: true);
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['schoolIdNumber', 'email'], true)) {
            return;
        }

        $this->validateOnly($propertyName);
    }

    public function removePhoto(): void
    {
        $this->photo = null;
        $this->existingPhotoUrl = null;
        $this->removeExistingPhoto = true;
        $this->resetErrorBag('photo');
    }

    public function save()
    {
        $account = Account::findOrFail($this->userId);
        $student = Student::findOrFail($this->studentId);

        $this->authorize('update', $student);

        $this->errorMessage = '';
        $this->sanitizeInputs();
        $this->validate();

        $disk = config('settings.user_images.disk', 'public');
        $imagePathToSave = $student->getRawOriginal('image_url');

        try {
            if ($this->photo) {
                $configuredDir = config('settings.profile_image_dir.student', '/storage/image/profile/student/');
                $diskDir = trim(preg_replace('#^/?storage/#', '', $configuredDir), '/');
                if (empty($diskDir)) {
                    $diskDir = 'image/profile/student';
                }

                Storage::disk($disk)->makeDirectory($diskDir);
                $storedPath = $this->photo->store($diskDir, $disk);

                if ($storedPath) {
                    $oldRaw = $student->getRawOriginal('image_url');
                    if ($oldRaw && Storage::disk($disk)->exists($oldRaw)) {
                        Storage::disk($disk)->delete($oldRaw);
                    }
                    $imagePathToSave = $storedPath;
                }
            } elseif ($this->removeExistingPhoto) {
                $oldRaw = $student->getRawOriginal('image_url');
                if ($oldRaw && Storage::disk($disk)->exists($oldRaw)) {
                    Storage::disk($disk)->delete($oldRaw);
                }
                $imagePathToSave = null;
            }

            DB::transaction(function () use ($account, $student, $imagePathToSave) {
                $account->update([
                    'email' => $this->email,
                ]);

                $student->update([
                    'school_id_number' => $this->schoolIdNumber,
                    'first_name' => $this->firstName,
                    'middle_name' => $this->middleName ?: null,
                    'last_name' => $this->lastName,
                    'gender' => $this->gender ?: null,
                    'contact_num' => $this->contactNum ?: null,
                    'date_of_birth' => $this->normalizedDateOfBirth(),
                    'program' => $this->program,
                    'year_level' => $this->yearLevel,
                    'enrollment_status' => $this->enrollmentStatus ?: null,
                    'enrollment_year' => $this->enrollmentYear ?: null,
                    'library_status_id' => $this->libraryStatus ?: 1,
                    'note' => $this->note ?: null,
                    'image_url' => $imagePathToSave,
                ]);
            });

            $fullName = e(trim("{$student->first_name} {$student->last_name}"));
            session()->flash('status', "Student information for {$fullName} ({$student->school_id_number}) updated successfully.");

            $prefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
            return $this->redirect(route($prefix . 'user-details', $account->encrypted_id), navigate: true);
        } catch (\Throwable $e) {
            Log::error('Failed to update student: ' . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = 'Failed to update student record. ' . $e->getMessage();
        }
    }

    protected function parseSizeToKilobytes(string $size): int
    {
        $size = trim(strtoupper($size));
        if (str_ends_with($size, 'MB')) {
            return (int) substr($size, 0, -2) * 1024;
        }
        if (str_ends_with($size, 'KB')) {
            return (int) substr($size, 0, -2);
        }
        if (str_ends_with($size, 'M')) {
            return (int) substr($size, 0, -1) * 1024;
        }
        if (str_ends_with($size, 'K')) {
            return (int) substr($size, 0, -1);
        }

        return is_numeric($size) ? (int) $size : 5120;
    }

    #[Computed]
    public function encryptedId(): string
    {
        return Account::encodeId($this->userId);
    }

    public function render()
    {
        return view('livewire.forms.edit-student-form');
    }
}
