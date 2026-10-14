<?php

namespace App\Livewire\Pages\Student;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Profile'])]
class Profile extends Component
{
    public function render()
    {
        $user = Auth::user();
        $student = $user?->student ?? Student::with(['libraryStatus', 'account'])->first();

        return view('livewire.pages.student.profile', [
            'user' => $user,
            'student' => $student,
        ]);
    }
}
