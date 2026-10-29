<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.verification-status')]
class RegistrationStatus extends Component
{
    public string $state = 'pending';

    public function refreshStatus(): void
    {
        $user = Auth::user()?->fresh(['role', 'status', 'student']);

        if (! $user) {
            $this->redirect(route('login'), navigate: false);
            return;
        }

        if ($user->isStudent() && $user->isApprovedStudent()) {
            $this->redirect(route('student.dashboard'), navigate: true);
            return;
        }

        $isRejected = (int) $user->status_id === 5
            || strtolower($user->status?->status_name ?? '') === 'not approved';

        $this->state = $isRejected ? 'rejected' : 'pending';
    }

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->redirect(route('login'), navigate: false);
            return;
        }

        if ($user->isStudent() && $user->isApprovedStudent()) {
            $this->redirect(route('student.dashboard'), navigate: false);
            return;
        }

        $isRejected = (int) $user->status_id === 5 || strtolower($user->status?->status_name ?? '') === 'not approved';

        if ($user->isStudent() && ! $user->hasSubmittedVerificationDocument() && ! $isRejected) {
            $this->redirect(route('verification.upload'), navigate: false);
            return;
        }

        $this->state = $isRejected ? 'rejected' : 'pending';
    }

    public function render()
    {
        $user = Auth::user();
        $student = $user?->student;

        $studentName = $student
            ? trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name)
            : ($user?->username ?? 'Student');
        $studentIdNumber = $student?->school_id_number ?: '—';
        $programYear = $student
            ? trim(($student->program ?: '—') . ($student->year_level ? ' · ' . $student->year_level : ''))
            : '—';
        $documentTypeLabel = $student?->verification_document_type_label ?: 'School ID Card';
        $submittedAt = $student?->verification_submitted_at
            ? $student->verification_submitted_at->format('M d, Y · g:i A')
            : ($student?->created_at?->format('M d, Y · g:i A') ?? now()->format('M d, Y · g:i A'));
        $remarks = $student?->verification_remarks ?: 'Please upload a clearer photo of your School ID or Certificate of Registration.';
        $reviewedAt = $student?->verified_at
            ? $student->verified_at->format('M d, Y · g:i A')
            : ($student?->updated_at?->format('M d, Y · g:i A') ?? now()->format('M d, Y · g:i A'));

        return view('livewire.pages.auth.registration-status', [
            'studentName' => $studentName,
            'studentIdNumber' => $studentIdNumber,
            'programYear' => $programYear,
            'documentTypeLabel' => $documentTypeLabel,
            'submittedAt' => $submittedAt,
            'remarks' => $remarks,
            'reviewedAt' => $reviewedAt,
        ]);
    }
}
