<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class StudentPICard extends Component
{
    public $user;
    public $student;
    public ?string $fullName = null;
    public ?string $initials = null;
    public ?string $schoolId = null;
    public ?string $contactNum = null;
    public ?string $formattedDob = null;
    public ?string $gender = null;

    public function mount(
        $user = null,
        $student = null,
        ?string $fullName = null,
        ?string $initials = null,
        ?string $schoolId = null,
        ?string $contactNum = null,
        ?string $formattedDob = null,
        ?string $gender = null
    ) {
        $this->user = $user;
        $this->student = $student ?? $user?->student;
        $this->fullName = $fullName;
        $this->initials = $initials;
        $this->schoolId = $schoolId;
        $this->contactNum = $contactNum;
        $this->formattedDob = $formattedDob;
        $this->gender = $gender ?? ($this->student?->gender ? ucfirst(strtolower($this->student->gender)) : null);
    }

    public function openEditModal()
    {
        $this->dispatch('open-edit-modal');
    }

    public function render()
    {
        return view('livewire.components.usermanager.student-p-i-card', [
            'user' => $this->user,
            'student' => $this->student,
            'fullName' => $this->fullName,
            'initials' => $this->initials,
            'schoolId' => $this->schoolId,
            'contactNum' => $this->contactNum,
            'formattedDob' => $this->formattedDob,
            'gender' => $this->gender,
        ]);
    }
}
