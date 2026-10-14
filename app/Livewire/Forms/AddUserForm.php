<?php

namespace App\Livewire\Forms;

use App\Models\Account;
use App\Models\AccountStatus;
use App\Models\Role;
use App\Models\Student;
use App\Services\UserService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Traits\SanitizesRequestInput;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddUserForm extends Component
{
    use WithFileUploads, SanitizesRequestInput;


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

    // UI state
    public string $errorMessage = '';

    public function mount(): void
    {
        $this->authorize('create', Student::class);

        $this->enrollmentYear = (string) date('Y');
        $this->enrollmentStatus = 'Regular';
        $this->libraryStatus = '1';
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
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:accounts,email'],

            // Academic & Enrollment Details
            'schoolIdNumber' => ['required', 'string', 'max:50', 'unique:students,school_id_number'],
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
            'schoolIdNumber.required' => 'The School ID Number is required.',
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
            'gender.in' => 'The selected gender is invalid.',
            'contactNum.regex' => 'Please enter a valid contact number format.',
            'program.required' => 'Please select a valid Degree Program.',
            'program.in' => 'The selected Degree Program is invalid.',
            'yearLevel.required' => 'Please select a Year Level.',
            'yearLevel.in' => 'The selected Year Level is invalid.',
            'enrollmentStatus.in' => 'The selected Enrollment Status is invalid.',
            'enrollmentYear.required' => 'Enrollment Year is required.',
            'enrollmentYear.digits' => 'Enrollment Year must be a 4-digit year.',
            'enrollmentYear.between' => 'Enrollment Year must be between 2000 and ' . (date('Y') + 1) . '.',
            'photo.image' => 'The uploaded file must be a valid image (PNG, JPG, or WEBP).',
            'photo.mimes' => 'The profile photo must be a file of type: png, jpg, jpeg, webp.',
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
        $this->enrollmentStatus = $this->cleanString($this->enrollmentStatus, nullable: true) ?? 'Regular';
        $this->enrollmentYear = (string) $this->cleanString($this->enrollmentYear);
        $this->note = $this->cleanString($this->note, nullable: true);
    }

    protected function trimInputs(): void
    {
        $this->sanitizeInputs();
    }


    public function updated($propertyName): void
    {
        // Don't validate unique fields live to avoid database oracles/overload
        if (in_array($propertyName, ['schoolIdNumber', 'email'], true)) {
            return;
        }

        $this->validateOnly($propertyName);
    }

    public function removePhoto(): void
    {
        $this->photo = null;
        $this->resetErrorBag('photo');
    }

    public function clearForm(): void
    {
        $this->reset([
            'firstName',
            'middleName',
            'lastName',
            'gender',
            'dateOfBirth',
            'contactNum',
            'email',
            'schoolIdNumber',
            'program',
            'yearLevel',
            'note',
            'photo',
            'errorMessage',
        ]);

        $this->enrollmentStatus = 'Regular';
        $this->enrollmentYear = (string) date('Y');
        $this->libraryStatus = '1';
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->authorize('create', Student::class);

        $this->errorMessage = '';
        $this->sanitizeInputs();
        $this->validate();

        $disk = config('settings.user_images.disk', 'public');
        $storedPath = null;

        try {
            // 1. Fail loudly if required roles or statuses are not configured
            $roleId = Role::where('name', 'Student')->value('id')
                ?? throw new \RuntimeException('Student role is not configured in the system.');

            $statusId = AccountStatus::where('status_name', 'Active')->value('id')
                ?? throw new \RuntimeException('Active account status is not configured in the system.');

            // 2. Generate Account Credentials based on system configuration
            $credentials = UserService::generateCredentials([
                'school_id_number' => $this->schoolIdNumber,
                'email' => $this->email,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
            ]);

            // Ensure unique username with collision resolution
            $baseUsername = $credentials['username'];
            $username = $baseUsername;
            $counter = 1;
            while (Account::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
                if ($counter > 100) {
                    throw new \RuntimeException('Unable to generate unique username.');
                }
            }

            // 3. Atomically create Account and Student inside DB transaction
            $student = DB::transaction(function () use ($roleId, $statusId, $username, $credentials) {
                $account = Account::create([
                    'role_id' => $roleId,
                    'status_id' => $statusId,
                    'username' => $username,
                    'password_hash' => Hash::make($credentials['password']),
                    'email' => $this->email,
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                    'password_changed_at' => $credentials['force_change'] ? null : now(),
                ]);

                return Student::create([
                    'account_id' => $account->id,
                    'school_id_number' => $this->schoolIdNumber,
                    'first_name' => $this->firstName,
                    'middle_name' => $this->middleName ?: null,
                    'last_name' => $this->lastName,
                    'gender' => $this->gender ?: null,
                    'contact_num' => $this->contactNum ?: null,
                    'date_of_birth' => $this->normalizedDateOfBirth(),
                    'image_url' => null,
                    'library_status_id' => (int) $this->libraryStatus,
                    'note' => $this->note ?: null,
                    'program' => $this->program,
                    'year_level' => $this->yearLevel,
                    'enrollment_status' => $this->enrollmentStatus ?: 'Regular',
                    'enrollment_year' => $this->enrollmentYear ?: (string) date('Y'),
                ]);
            });

            // 4. Safely store profile picture after DB transaction succeeds
            if ($this->photo) {
                $configuredDir = config('settings.profile_image_dir.student', '/storage/image/profile/student/');
                $diskDir = trim(preg_replace('#^/?storage/#', '', $configuredDir), '/');
                if (empty($diskDir)) {
                    $diskDir = 'image/profile/student';
                }

                Storage::disk($disk)->makeDirectory($diskDir);
                $storedPath = $this->photo->store($diskDir, $disk);

                if ($storedPath) {
                    $student->update(['image_url' => $storedPath]);
                }
            }

            $fullName = e(trim("{$student->first_name} {$student->last_name}"));
            session()->flash('status', "Student account for {$fullName} ({$student->school_id_number}) created successfully.");

            $prefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
            return $this->redirect(route($prefix . 'user-management'), navigate: true);

        } catch (UniqueConstraintViolationException $e) {
            if ($storedPath) {
                Storage::disk($disk)->delete($storedPath);
            }

            Log::warning('Duplicate entry conflict when registering student: School ID or Email collision');
            $this->errorMessage = 'A student with this School ID Number or Email Address already exists.';
            $this->dispatch('toast', message: $this->errorMessage, type: 'error');

        } catch (\Throwable $e) {
            if ($storedPath) {
                Storage::disk($disk)->delete($storedPath);
            }

            Log::error('Student registration failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->errorMessage = 'Failed to create the student account. Please try again.';
            $this->dispatch('toast', message: $this->errorMessage, type: 'error');
        }
    }

    private function parseSizeToKilobytes(string $size): int
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

    public function render()
    {
        return view('livewire.forms.add-user-form');
    }
}
