<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.register-auth')]
class Verify extends Component
{
    use WithFileUploads;

    public string $documentType = 'school_id';
    public $uploadedFile = null;
    public ?string $capturedImageBase64 = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->redirect(route('login'), navigate: false);
            return;
        }

        if (! $user->isStudent()) {
            $roleName = strtolower(str_replace(' ', '', $user->role?->name ?? ''));
            $this->redirect($roleName === 'admin' ? route('admin.dashboard') : route('librarian.dashboard'), navigate: false);
            return;
        }

        if ($user->isApprovedStudent()) {
            $this->redirect(route('student.dashboard'), navigate: false);
            return;
        }

        // If the student already uploaded a document and is waiting for approval (status 4),
        // redirect to the registration status page (cannot replace document while waiting).
        $isRejected = (int) $user->status_id === 5 || strtolower($user->status?->status_name ?? '') === 'not approved';
        if ($user->hasSubmittedVerificationDocument() && ! $isRejected) {
            $this->redirect(route('verification.status'), navigate: false);
            return;
        }

        if ($user->student?->verification_document_type) {
            $this->documentType = $user->student->verification_document_type;
        }
    }

    public function submitVerification(): void
    {
        $this->validate([
            'documentType' => ['required', 'string', 'in:school_id,cor,enrollment_form'],
            'uploadedFile' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'capturedImageBase64' => ['nullable', 'string'],
        ]);

        if (! $this->uploadedFile && empty($this->capturedImageBase64)) {
            throw ValidationException::withMessages([
                'uploadedFile' => 'Please upload a verification file or capture a photo using your camera.',
            ]);
        }

        $user = Auth::user();
        $student = $user?->student;

        if (! $user || ! $student) {
            throw ValidationException::withMessages([
                'uploadedFile' => 'Student profile not found for this account.',
            ]);
        }

        $storedPath = null;

        if ($this->uploadedFile) {
            $ext = strtolower($this->uploadedFile->getClientOriginalExtension() ?: 'jpg');
            $filename = 'verify_' . $student->id . '_' . time() . '_' . Str::random(6) . '.' . $ext;
            $storedPath = $this->uploadedFile->storeAs('verifications/students', $filename, 'public');
        } elseif (! empty($this->capturedImageBase64)) {
            if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/i', $this->capturedImageBase64, $matches)) {
                throw ValidationException::withMessages([
                    'uploadedFile' => 'Invalid captured camera image format. Please retake the photo.',
                ]);
            }

            $binary = base64_decode($matches[2], true);
            if ($binary === false || strlen($binary) > 5 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'uploadedFile' => 'Captured photo exceeds the 5MB size limit. Please retake the photo.',
                ]);
            }

            $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $storedPath = 'verifications/students/capture_' . $student->id . '_' . time() . '_' . Str::random(6) . '.' . $ext;
            Storage::disk('public')->put($storedPath, $binary);
        }

        // Delete previous declined file if replacing after rejection
        if (! empty($student->verification_document_path) && Storage::disk('public')->exists($student->verification_document_path)) {
            Storage::disk('public')->delete($student->verification_document_path);
        }

        $student->update([
            'verification_document_path' => $storedPath,
            'verification_document_type' => $this->documentType,
            'verification_submitted_at' => now(),
            'verification_remarks' => null,
        ]);

        $user->update([
            'status_id' => 4, // Pending Review
        ]);

        $this->redirect(route('verification.status'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.auth.verify');
    }
}
