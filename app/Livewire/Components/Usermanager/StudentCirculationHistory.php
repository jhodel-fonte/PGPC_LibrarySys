<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class StudentCirculationHistory extends Component
{
    public $circulationHistory;
    public $user;
    public $student;

    public function mount($circulationHistory = null, $user = null, $student = null)
    {
        $this->user = $user;
        $this->student = $student ?? $user?->student;
        $this->circulationHistory = $circulationHistory ?? ($this->student?->borrowingTransactions ?? collect());
    }

    public function render()
    {
        return view('livewire.components.usermanager.student-circulation-history', [
            'circulationHistory' => $this->circulationHistory ?? collect(),
            'user' => $this->user,
            'student' => $this->student,
        ]);
    }
}
