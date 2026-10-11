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
        // Fall back to student for any unexpected value.
        if (! in_array($this->type, self::TYPES, true)) {
            $this->type = 'student';
        }
    }

    public function render()
    {
        return view('livewire.pages.dashboard.user-management');
    }
}
