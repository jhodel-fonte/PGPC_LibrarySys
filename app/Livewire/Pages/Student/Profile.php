<?php

namespace App\Livewire\Pages\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Profile'])]
class Profile extends Component
{
    public function render()
    {
        $user = Auth::user();

        return view('livewire.pages.student.profile', [
            'user' => $user,
        ]);
    }
}
