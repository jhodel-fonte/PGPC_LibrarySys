<?php

namespace App\Livewire\Components\Usermanager;

use App\Models\Account;
use App\Models\AccountStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LibrarianDetail extends Component
{
    public int|string $userId;
    public string $activeTab = 'personal'; // 'personal', 'account'

    // Status management modal
    public bool $showStatusModal = false;
    public ?int $selectedStatusId = null;

    // Edit profile modal
    public bool $showEditModal = false;
    public string $editFirstName = '';
    public string $editMiddleName = '';
    public string $editLastName = '';
    public string $editEmail = '';
    public string $editContact = '';
    public string $editSchoolId = '';

    public function mount($userId): void
    {
        $this->userId = $userId;
        $user = $this->user;

        if ($user) {
            $this->selectedStatusId = $user->status_id;
            $librarian = $user->librarian;
            $this->editFirstName = $librarian->first_name ?? '';
            $this->editMiddleName = $librarian->middle_name ?? '';
            $this->editLastName = $librarian->last_name ?? '';
            $this->editEmail = $user->email ?? '';
            $this->editContact = $librarian->contact_num ?? '';
            $this->editSchoolId = $librarian->school_id_number ?? '';
        }
    }

    #[Computed]
    public function user()
    {
        return Account::with([
            'role',
            'status',
            'librarian.issuedTransactions',
            'librarian.receivedTransactions',
            'librarian.receivedPayments',
            'permissions',
        ])->find($this->userId);
    }

    #[Computed]
    public function librarian()
    {
        return $this->user?->librarian;
    }

    #[Computed]
    public function fullName(): string
    {
        $librarian = $this->librarian;
        if ($librarian) {
            $parts = array_filter([$librarian->first_name, $librarian->middle_name, $librarian->last_name]);
            return implode(' ', $parts);
        }
        return $this->user->username ?? 'N/A';
    }

    #[Computed]
    public function initials(): string
    {
        $name = $this->fullName;
        return collect(explode(' ', $name))
            ->map(fn($n) => substr($n, 0, 1))
            ->take(2)
            ->join('');
    }

    #[Computed]
    public function statuses()
    {
        return AccountStatus::orderBy('id')->get();
    }

    #[Computed]
    public function assignedPermissions(): array
    {
        $user = $this->user;
        $isHead = $user?->role?->name === 'Head Librarian';
        $defaultPerms = config('settings.librarian_role_permission.' . ($isHead ? 'librarian_head' : 'librarian'), []);

        $catalog = [
            // Administration
            'manage_users' => [
                'title' => 'User Management',
                'description' => 'Full access to student profile operations.',
                'group' => 'Administration',
            ],
            'view_students' => [
                'title' => 'View Student Records',
                'description' => 'Search, filter, and view student profiles and library cards.',
                'group' => 'Administration',
            ],
            'create_students' => [
                'title' => 'Register Students',
                'description' => 'Register new student accounts into the system.',
                'group' => 'Administration',
            ],
            'edit_students' => [
                'title' => 'Edit Student Info',
                'description' => 'Update student identity, contact numbers, and year levels.',
                'group' => 'Administration',
            ],
            'delete_students' => [
                'title' => 'Delete Students',
                'description' => 'Soft-delete student patron accounts and revoke access.',
                'group' => 'Administration',
            ],
            'manage_librarian' => [
                'title' => 'Staff Management',
                'description' => 'Manage library staff accounts and role privileges.',
                'group' => 'Administration',
            ],

            // Cataloging
            'manage_catalog' => [
                'title' => 'Catalog Management',
                'description' => 'Full access to bibliographic records, copies, and classifications.',
                'group' => 'Cataloging',
            ],
            'view_catalog' => [
                'title' => 'Catalog Access (View Only)',
                'description' => 'Search and browse through collection items and metadata.',
                'group' => 'Cataloging',
            ],
            'create_book' => [
                'title' => 'Add Books & Copies',
                'description' => 'Register new titles and physical accession copies.',
                'group' => 'Cataloging',
            ],
            'edit_book' => [
                'title' => 'Edit Book Metadata',
                'description' => 'Edit classifications, publishers, editions, and copy status.',
                'group' => 'Cataloging',
            ],
            'delete_book' => [
                'title' => 'Delete Book Copies',
                'description' => 'Archive or delete physical copies and book records.',
                'group' => 'Cataloging',
            ],

            // Circulation
            'manage_circulation' => [
                'title' => 'Circulation Operations',
                'description' => 'Full access to issue books, process returns, and reservations.',
                'group' => 'Circulation',
            ],
            'view_circulation' => [
                'title' => 'View Circulation Logs',
                'description' => 'Inspect active loans, overdue lists, and return logs.',
                'group' => 'Circulation',
            ],
            'borrow_book' => [
                'title' => 'Issue Loans (Borrow)',
                'description' => 'Check out physical books to authenticated students.',
                'group' => 'Circulation',
            ],
            'return_book' => [
                'title' => 'Process Returns (Check-In)',
                'description' => 'Receive returned copies and record physical conditions.',
                'group' => 'Circulation',
            ],
            'manage_fines' => [
                'title' => 'Fines & Payments',
                'description' => 'Assess overdue penalties and collect fine payments.',
                'group' => 'Circulation',
            ],

            // System & Reports
            'manage_system' => [
                'title' => 'System Settings',
                'description' => 'Access system configuration, policies, and backups.',
                'group' => 'System & Reports',
            ],
            'view_reports' => [
                'title' => 'Analytics & Reports',
                'description' => 'Generate and export circulation, inventory, and activity reports.',
                'group' => 'System & Reports',
            ],
        ];

        $result = [];
        foreach ($catalog as $key => $info) {
            $allowed = in_array($key, $defaultPerms, true);
            // Check direct account overrides if present
            $override = $user?->permissions->firstWhere('name', $key);
            if ($override) {
                $allowed = (bool) $override->pivot->is_allowed;
            }

            $result[$key] = array_merge($info, ['allowed' => $allowed]);
        }

        return $result;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function openEditModal(): void
    {
        $librarian = $this->librarian;
        if ($librarian) {
            $this->editFirstName = $librarian->first_name ?? '';
            $this->editMiddleName = $librarian->middle_name ?? '';
            $this->editLastName = $librarian->last_name ?? '';
            $this->editEmail = $this->user->email ?? '';
            $this->editContact = $librarian->contact_num ?? '';
            $this->editSchoolId = $librarian->school_id_number ?? '';
        }
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function openStatusModal(): void
    {
        $this->selectedStatusId = $this->user?->status_id;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'selectedStatusId' => 'required|exists:account_statuses,id',
        ]);

        $account = Account::findOrFail($this->userId);
        $oldStatus = $account->status?->status_name ?? 'Unknown';
        $account->update(['status_id' => $this->selectedStatusId]);

        $newStatus = AccountStatus::find($this->selectedStatusId)?->status_name ?? 'Updated';

        $this->showStatusModal = false;
        unset($this->user);

        $this->dispatch('toast', message: "Account status updated from {$oldStatus} to {$newStatus}.", type: 'success');
    }

    public function toggleEmailVerification(): void
    {
        $account = Account::findOrFail($this->userId);
        $newVerified = !$account->is_email_verified;

        $account->update([
            'is_email_verified' => $newVerified,
            'email_verified_at' => $newVerified ? now() : null,
        ]);

        unset($this->user);
        $statusStr = $newVerified ? 'Verified' : 'Unverified';
        $this->dispatch('toast', message: "Email status marked as {$statusStr}.", type: 'success');
    }

    public function sendPasswordReset(): void
    {
        $account = $this->user;

        if (!$account || empty($account->email)) {
            $this->dispatch('toast', message: 'Cannot send password reset: User has no valid email address.', type: 'error');
            return;
        }

        try {
            $token = app('auth.password.broker')->createToken($account);
            $account->sendPasswordResetNotification($token);
            $this->dispatch('toast', message: "Password reset instructions sent to {$account->email}.", type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Failed to send password reset email: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.components.usermanager.librarian-detail');
    }
}
