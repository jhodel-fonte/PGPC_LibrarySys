<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Account;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'User Management', 'activepageRoute' => 'admin.user-management'])]
class EditUserPage extends Component
{
    public int|string $id;

    public function mount($id = null): void
    {
        $rawId = $id ?? request()->route('id') ?? request()->route('userId') ?? request()->route('user');
        $resolvedId = Account::decodeId($rawId);

        if (! $resolvedId) {
            abort(404, 'User account not found.');
        }

        $this->id = $resolvedId;

        $targetUser = $this->user;
        if (! $targetUser) {
            abort(404, 'User account not found.');
        }

        // Determine if the current authenticated user has authorization to edit this account
        $this->checkAuthorization($targetUser);
    }

    /**
     * Check authorization of the current logged-in user to edit the target patron.
     */
    protected function checkAuthorization(Account $targetUser): void
    {
        $currentUser = auth()->user();

        if (! $currentUser) {
            abort(403, 'Unauthorized. Please sign in to edit user accounts.');
        }

        // 1. System administrators always have permission
        if ($currentUser->role?->name === 'Admin') {
            return;
        }

        // 2. Users can edit their own profile if permitted
        if ($currentUser->id === $targetUser->id) {
            return;
        }

        // 3. Role-based check depending on whether the target is a Librarian or Student
        if ($this->isLibrarian) {
            // Editing a librarian requires 'manage_librarians' or Head Librarian role
            $canEditLibrarian = $currentUser->hasPermission('manage_librarians')
                || $currentUser->role?->name === 'Head Librarian';

            if (! $canEditLibrarian) {
                abort(403, 'You do not have permission to edit librarian staff profiles.');
            }
        } else {
            // Editing a student requires 'edit_students' or 'manage_users' permission
            $canEditStudent = $currentUser->hasPermission('edit_students') || $currentUser->hasPermission('manage_users');

            if (! $canEditStudent) {
                abort(403, 'You do not have permission to edit student information.');
            }
        }
    }

    #[Computed]
    public function user()
    {
        return Account::with(['role', 'student', 'librarian'])->find($this->id);
    }

    #[Computed]
    public function isLibrarian(): bool
    {
        $roleName = $this->user?->role?->name;
        return in_array($roleName, ['Librarian', 'Head Librarian'], true) || (bool) $this->user?->librarian;
    }

    public function render()
    {
        return view('livewire.pages.dashboard.edit-user-page')
            ->layoutData([
                'title' => 'User Management',
                'subpage' => $this->isLibrarian ? 'Edit Librarian Profile' : 'Edit Student Information',
                'activepageRoute' => 'admin.user-management',
            ]);
    }
}
