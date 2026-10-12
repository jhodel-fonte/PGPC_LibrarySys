<?php

namespace App\Livewire\Forms;

use App\Models\Account;
use App\Models\Librarian;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\SanitizesRequestInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditLibrarianForm extends Component
{
    use WithFileUploads, SanitizesRequestInput;

    public int|string $userId;
    public ?int $librarianId = null;

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
    public ?string $existingPhotoUrl = null;
    public bool $removeExistingPhoto = false;

    // UI state
    public string $errorMessage = '';

    public function mount($userId): void
    {
        $this->userId = $userId;
        $account = Account::with(['librarian', 'role', 'permissions'])->findOrFail($this->userId);
        $librarian = $account->librarian;

        if (! $librarian) {
            abort(404, 'Librarian record not found for this user account.');
        }

        $this->authorize('update', $librarian);

        $this->librarianId = $librarian->id;
        $this->firstName = $librarian->first_name ?? '';
        $this->middleName = $librarian->middle_name ?? '';
        $this->lastName = $librarian->last_name ?? '';
        $this->contactNum = $librarian->contact_num ?? '';
        $this->email = $account->email ?? '';
        $this->schoolIdNumber = $librarian->school_id_number ?? '';
        $rawImage = $librarian->getRawOriginal('image_url');
        $this->existingPhotoUrl = $rawImage ? $librarian->image_url : null;

        // Determine role key
        $roleName = $account->role?->name ?? 'Librarian';
        $this->role = ($roleName === 'Head Librarian') ? 'head_librarian' : 'librarian';

        // Load permissions from presets
        $presets = config('settings.librarian_role_permission', []);
        $allowed = $presets[$this->role] ?? [];
        foreach (array_keys($this->permissions) as $key) {
            $this->permissions[$key] = in_array($key, $allowed, true);
        }

        // Overlay custom stored permissions
        foreach ($account->permissions as $perm) {
            if (array_key_exists($perm->name, $this->permissions)) {
                $this->permissions[$perm->name] = (bool) $perm->pivot->is_allowed;
            }
        }
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
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('accounts', 'email')->ignore($this->userId)],

            // Staff Identification & Role
            'schoolIdNumber' => ['required', 'string', 'max:50', Rule::unique('librarians', 'school_id_number')->ignore($this->librarianId)],
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
        $librarian = Librarian::findOrFail($this->librarianId);

        $this->authorize('update', $librarian);

        $this->errorMessage = '';
        $this->sanitizeInputs();
        $this->validate();

        $disk = config('settings.user_images.disk', 'public');
        $imagePathToSave = $librarian->getRawOriginal('image_url');

        try {
            $targetRoleName = ($this->role === 'librarian_head' || $this->role === 'head_librarian')
                ? 'Head Librarian'
                : 'Librarian';

            $roleId = Role::where('name', $targetRoleName)->value('id')
                ?? throw new \RuntimeException("{$targetRoleName} role is not configured in the system.");

            if ($this->photo) {
                $configuredDir = config('settings.profile_image_dir.librarian', '/storage/image/profile/librarian/');
                $diskDir = trim(preg_replace('#^/?storage/#', '', $configuredDir), '/');
                if (empty($diskDir)) {
                    $diskDir = 'image/profile/librarian';
                }

                Storage::disk($disk)->makeDirectory($diskDir);
                $storedPath = $this->photo->store($diskDir, $disk);

                if ($storedPath) {
                    $oldRaw = $librarian->getRawOriginal('image_url');
                    if ($oldRaw && Storage::disk($disk)->exists($oldRaw)) {
                        Storage::disk($disk)->delete($oldRaw);
                    }
                    $imagePathToSave = $storedPath;
                }
            } elseif ($this->removeExistingPhoto) {
                $oldRaw = $librarian->getRawOriginal('image_url');
                if ($oldRaw && Storage::disk($disk)->exists($oldRaw)) {
                    Storage::disk($disk)->delete($oldRaw);
                }
                $imagePathToSave = null;
            }

            DB::transaction(function () use ($account, $librarian, $roleId, $imagePathToSave) {
                $account->update([
                    'role_id' => $roleId,
                    'email' => $this->email,
                ]);

                $librarian->update([
                    'school_id_number' => $this->schoolIdNumber,
                    'first_name' => $this->firstName,
                    'middle_name' => $this->middleName ?: null,
                    'last_name' => $this->lastName,
                    'contact_num' => $this->contactNum ?: null,
                    'image_url' => $imagePathToSave,
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
            });

            $fullName = e(trim("{$librarian->first_name} {$librarian->last_name}"));
            session()->flash('status', "Librarian profile for {$fullName} ({$librarian->school_id_number}) updated successfully.");

            return $this->redirect(route('admin.user-details', $account->encrypted_id), navigate: true);
        } catch (\Throwable $e) {
            Log::error('Failed to update librarian: ' . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = 'Failed to update librarian profile. ' . $e->getMessage();
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
        return view('livewire.forms.edit-librarian-form');
    }
}
