<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'User Management', 'subpage' => 'Add New Student', 'activepageRoute' => 'admin.user-management'])]
class AddUser extends Component
{
    public function render()
    {
        return view('livewire.pages.dashboard.add-user');
    }
}
