<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'User Management'])]
class UserManagement extends Component
{
    /**
     * Which user table to show. Driven by the ?type= query string
     * ('student' or 'librarian').
     */
    #[Url(as: 'type', except: 'student')]
    public string $type = 'student';

    protected const TYPES = ['student', 'librarian'];

    public function mount(): void
    {
        $user = auth()->user();
        $canManageLibrarians = $user && ($user->hasRole('Admin') || $user->hasPermission('manage_librarians'));
        $canManageStudents = $user && ($user->hasRole('Admin') || $user->hasPermission('manage_users') || $user->hasPermission('view_students'));

        if ($this->type === 'librarian' && ! $canManageLibrarians) {
            $this->type = 'student';
        }

        if (! in_array($this->type, self::TYPES, true)) {
            $this->type = $canManageStudents ? 'student' : ($canManageLibrarians ? 'librarian' : 'student');
        }

        if ($this->type === 'student' && ! $canManageStudents && ! $canManageLibrarians) {
            abort(403, 'Unauthorized action. You do not have permission to view users.');
        }
    }

    public function render()
    {
        return view('livewire.pages.dashboard.user-management');
    }
}
