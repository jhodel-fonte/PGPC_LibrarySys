<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'User Management', 'activepageRoute' => 'admin.user-management'])]
class AddUser extends Component
{
    /**
     * Which registration form to show. Driven by the ?type= query string
     * set by the "Add Student" / "Add Librarian" buttons on User Management.
     */
    #[Url(as: 'type', except: 'student')]
    public string $type = 'student';

    protected const TYPES = ['student', 'librarian'];

    public function mount(): void
    {
        $user = auth()->user();
        $canCreateStudent = $user && ($user->hasRole('Admin') || $user->hasPermission('create_students') || $user->hasPermission('manage_users'));
        $canManageLibrarians = $user && ($user->hasRole('Admin') || $user->hasPermission('manage_librarians'));

        if ($this->type === 'librarian' && ! $canManageLibrarians) {
            abort(403, 'Unauthorized action. You do not have permission to add library staff.');
        }

        if ($this->type === 'student' && ! $canCreateStudent) {
            abort(403, 'Unauthorized action. You do not have permission to add students.');
        }

        // Fall back to the student form for any unexpected value.
        if (! in_array($this->type, self::TYPES, true)) {
            $this->type = 'student';
        }
    }

    public function render()
    {
        return view('livewire.pages.dashboard.add-user')
            ->layoutData([
                'subpage' => $this->type === 'librarian' ? 'Add New Librarian' : 'Add New Student',
            ]);
    }
}
