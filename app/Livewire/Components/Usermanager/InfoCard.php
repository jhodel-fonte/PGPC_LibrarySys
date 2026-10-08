<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class InfoCard extends Component
{
    public $user;
    public ?string $fullName = null;
    public ?string $contactNum = null;
    public ?string $schoolId = null;
    public ?bool $isStudent = null;
    public ?bool $isLibrarian = null;

    public function mount(
        $user = null,
        ?string $fullName = null,
        ?string $contactNum = null,
        ?string $schoolId = null,
        ?bool $isStudent = null,
        ?bool $isLibrarian = null
    ) {
        $this->user = $user;

        $person = $user?->role?->name === 'Librarian' ? $user?->librarian : $user?->student;

        $this->fullName = $fullName ?? ($person ? implode(' ', array_filter([$person->first_name, $person->middle_name, $person->last_name])) : ($user?->username ?? 'N/A'));
        $this->contactNum = $contactNum ?? ($person?->contact_num ?? 'Not provided');
        $this->schoolId = $schoolId ?? ($person?->school_id_number ?? 'Not assigned');
        $this->isStudent = $isStudent ?? ($user?->role?->name === 'Member' || (bool)$user?->student);
        $this->isLibrarian = $isLibrarian ?? ($user?->role?->name === 'Librarian' || (bool)$user?->librarian);
    }

    public function render()
    {
        return view('livewire.components.usermanager.info-card');
    }
}
