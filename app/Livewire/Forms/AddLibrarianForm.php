<?php

namespace App\Livewire\Forms;

use App\Models\Account;
use App\Models\AccountStatus;
use App\Models\Librarian;
use App\Models\Permission;
use App\Models\Role;
use App\Services\UserService;
use App\Traits\SanitizesRequestInput;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddLibrarianForm extends Component
{
    use WithFileUploads, SanitizesRequestInput;

    // Personal Information
    public string $firstName = '';
    public ?string $middleName = '';
    public string $lastName = '';
    public ?string $contactNum = '';

    // Account & Credentials
    public string $email = '';
    public string $schoolIdNumber = '';

    // Role & Permissions
    public string $role = '';
    public array $permissions = [
        'manage_users' => false,
        'manage_librarian' => false,
        'manage_catalog' => false,
        'manage_circulation' => false,
        'manage_fines' => false,
        'manage_system' => false,
        'view_catalog' => false,
    ];

    // Profile Photo
    public $photo = null;

    // UI state
    public string $errorMessage = '';

    public function mount(): void
    {
        $this->authorize('create', Librarian::class);
    }

    public function rules(): array
    {
        $maxSetting = config('settings.user_images.max_size', config('settings.user_image_max_size', '5MB'));
        $maxKb = $this->parseSizeToKilobytes($maxSetting);

        return [
            // Personal Information
            'firstName' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-.\']+$/u'],
            'middleName' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\-.\']+$/u'],
            'lastName' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-.\']+$/u'],
            'contactNum' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:accounts,email'],

            // Staff Identification & Role
            'schoolIdNumber' => ['required', 'string', 'max:50', 'unique:librarians,school_id_number'],
            'role' => ['required', 'string', Rule::in(['librarian', 'librarian_head', 'head_librarian'])],
            'permissions' => ['array'],
            'permissions.*' => ['boolean'],

            // Profile Picture
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', "max:{$maxKb}"],
        ];
    }

    protected function messages(): array
    {
        return [
            'schoolIdNumber.required' => 'The Employee ID Number is required.',
            'schoolIdNumber.unique' => 'This Employee ID Number is already registered.',
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already associated with an existing account.',
            'firstName.required' => 'First Name is required.',
            'firstName.regex' => 'First Name may only contain letters, spaces, hyphens, and apostrophes.',
            'middleName.regex' => 'Middle Name may only contain letters, spaces, hyphens, and apostrophes.',
            'lastName.required' => 'Last Name is required.',
            'lastName.regex' => 'Last Name may only contain letters, spaces, hyphens, and apostrophes.',
            'contactNum.regex' => 'Please enter a valid contact number format.',
            'role.required' => 'Please select a User Type (Librarian or Head Librarian).',
            'role.in' => 'The selected User Type is invalid.',
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
        $this->contactNum = $this->cleanString($this->contactNum, nullable: true);
        $this->email = (string) $this->cleanString($this->email);
        $this->schoolIdNumber = (string) $this->cleanString($this->schoolIdNumber);
        $this->role = (string) $this->cleanString($this->role);
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
            'contactNum',
            'email',
            'schoolIdNumber',
            'role',
            'photo',
            'errorMessage',
        ]);

        $this->permissions = [
            'manage_users' => false,
            'manage_librarian' => false,
            'manage_catalog' => false,
            'manage_circulation' => false,
            'manage_fines' => false,
            'manage_system' => false,
            'view_catalog' => false,
        ];

        $this->resetErrorBag();
    }

    public function save()
    {
        $this->authorize('create', Librarian::class);

        $this->errorMessage = '';
        $this->sanitizeInputs();
        $this->validate();

        $disk = config('settings.user_images.disk', 'public');
        $storedPath = null;

        try {
            // 1. Resolve role and active status
            $targetRoleName = ($this->role === 'librarian_head' || $this->role === 'head_librarian')
                ? 'Head Librarian'
                : 'Librarian';

            $roleId = Role::where('name', $targetRoleName)->value('id')
                ?? throw new \RuntimeException("{$targetRoleName} role is not configured in the system.");

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

            // 3. Atomically create Account, Librarian and assign permissions in DB transaction
            $librarian = DB::transaction(function () use ($roleId, $statusId, $username, $credentials) {
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

                $librarianRecord = Librarian::create([
                    'account_id' => $account->id,
                    'school_id_number' => $this->schoolIdNumber,
                    'first_name' => $this->firstName,
                    'middle_name' => $this->middleName ?: null,
                    'last_name' => $this->lastName,
                    'contact_num' => $this->contactNum ?: null,
                    'image_url' => null,
                ]);

                // Sync custom account permissions
                if (!empty($this->permissions)) {
                    $permissionRecords = Permission::whereIn('name', array_keys($this->permissions))
                        ->get()
                        ->keyBy('name');

                    $syncData = [];
                    foreach ($this->permissions as $permName => $isAllowed) {
                        if (isset($permissionRecords[$permName])) {
                            $syncData[$permissionRecords[$permName]->id] = [
                                'is_allowed' => (bool) $isAllowed,
                            ];
                        }
                    }

                    if (!empty($syncData)) {
                        $account->permissions()->sync($syncData);
                    }
                }

                return $librarianRecord;
            });

            // 4. Safely store profile picture after DB transaction succeeds
            if ($this->photo) {
                $configuredDir = config('settings.profile_image_dir.librarian', '/storage/image/profile/librarian/');
                $diskDir = trim(preg_replace('#^/?storage/#', '', $configuredDir), '/');
                if (empty($diskDir)) {
                    $diskDir = 'image/profile/librarian';
                }

                Storage::disk($disk)->makeDirectory($diskDir);
                $storedPath = $this->photo->store($diskDir, $disk);

                if ($storedPath) {
                    $librarian->update(['image_url' => $storedPath]);
                }
            }

            $fullName = e(trim("{$librarian->first_name} {$librarian->last_name}"));
            session()->flash('status', "Librarian account for {$fullName} ({$librarian->school_id_number}) created successfully.");

            return $this->redirect(route('admin.user-management'), navigate: true);

        } catch (UniqueConstraintViolationException $e) {
            if ($storedPath) {
                Storage::disk($disk)->delete($storedPath);
            }

            Log::warning('Duplicate entry conflict when registering librarian: Employee ID or Email collision');
            $this->errorMessage = 'A staff account with this Employee ID Number or Email Address already exists.';
            $this->dispatch('toast', message: $this->errorMessage, type: 'error');

        } catch (\Throwable $e) {
            if ($storedPath) {
                Storage::disk($disk)->delete($storedPath);
            }

            Log::error('Librarian registration failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->errorMessage = 'Failed to create the librarian account. Please try again.';
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
        return view('livewire.forms.add-librarian-form');
    }
}
