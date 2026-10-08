<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class ProfileBanner extends Component
{
    public $user;
    public ?string $fullName = null;
    public ?string $initials = null;
    public ?string $roleName = null;
    public ?string $statusName = null;
    public ?string $statusLower = null;
    public ?bool $isStudent = null;
    public ?bool $isLibrarian = null;
    public ?string $schoolId = null;

    public function mount(
        $user = null,
        ?string $fullName = null,
        ?string $initials = null,
        ?string $roleName = null,
        ?string $statusName = null,
        ?string $statusLower = null,
        ?bool $isStudent = null,
        ?bool $isLibrarian = null,
        ?string $schoolId = null
    ) {
        $this->user = $user;

        $person = $user?->role?->name === 'Librarian' ? $user?->librarian : $user?->student;

        $this->fullName = $fullName ?? ($person ? implode(' ', array_filter([$person->first_name, $person->middle_name, $person->last_name])) : ($user?->username ?? 'N/A'));
        $this->initials = $initials ?? ($this->fullName ? collect(explode(' ', $this->fullName))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') : strtoupper(substr($user?->username ?? 'U', 0, 1)));
        $this->roleName = $roleName ?? ($user?->role?->name ?? 'User');
        $this->statusName = $statusName ?? ($user?->status?->status_name ?? 'Active');
        $this->statusLower = $statusLower ?? strtolower($this->statusName);
        $this->isStudent = $isStudent ?? ($this->roleName === 'Member' || (bool)$user?->student);
        $this->isLibrarian = $isLibrarian ?? ($this->roleName === 'Librarian' || (bool)$user?->librarian);
        $this->schoolId = $schoolId ?? ($person?->school_id_number ?? 'Not assigned');
    }

    public function render()
    {
        return view('livewire.components.usermanager.profile-banner');
    }
}
