<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Fine;
use App\Models\Student;
use App\Services\EmployeeRoleService;
use App\Services\FineService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Process Fine Payment', 'activepageRoute' => 'admin.circulation-desk.index'])]
class ProcessFinePayment extends Component
{
    public string $search = '';
    public ?int $studentId = null;
    public ?int $fineId = null;
    public string $paymentDate = '';
    public string $receiptNumber = '';
    public bool $reviewing = false;
    public ?string $success = null;

    public function mount(): void
    {
        abort_unless(EmployeeRoleService::canManageFines(), 403);
        $this->paymentDate = now()->toDateString();
        $this->studentId = request()->integer('student') ?: null;
        $this->fineId = request()->integer('fine') ?: null;
    }

    public function selectStudent(int $id): void
    {
        abort_unless(EmployeeRoleService::canManageFines(), 403);
        $this->studentId = Student::findOrFail($id)->id;
        $this->fineId = null;
        $this->receiptNumber = '';
        $this->reviewing = false;
        $this->resetValidation();
        $this->search = '';
        $this->success = null;
    }

    #[On('code-scanned')]
    public function scanStudent($code): void
    {
        abort_unless(EmployeeRoleService::canManageFines(), 403);
        if (! is_string($code) && ! is_numeric($code)) {
            $this->addError('scan', 'The scanned code is invalid. Try again or search for the student.');
            return;
        }
        $code = trim((string) $code);
        $student = Student::where('school_id_number', $code)->first();
        if (! $student && ctype_digit($code)) {
            $student = Student::find((int) $code);
        }
        if (! $student) {
            $this->addError('scan', 'No student matches this QR code. Try again or search for the student.');
            return;
        }
        $this->selectStudent($student->id);
        $this->dispatch('student-qr-selected');
    }

    private function selectedFine(): Fine
    {
        return Fine::with(['fineType', 'transaction.book.bookDetail.bookData'])
            ->where('student_id', $this->studentId)->where('fine_status', 'unpaid')
            ->whereHas('transaction', fn ($query) => $query->whereNotNull('return_date'))
            ->whereHas('fineType', fn ($query) => $query->where('name', 'Overdue'))
            ->findOrFail($this->fineId);
    }

    private function validatePayment(): array
    {
        $this->receiptNumber = trim($this->receiptNumber);
        $this->validate([
            'studentId' => 'required|integer',
            'fineId' => 'required|integer',
            'paymentDate' => 'required|date_format:Y-m-d|before_or_equal:today',
            'receiptNumber' => 'required|string|max:255',
        ], ['fineId.required' => 'Select a fine to process.']);
        return app(FineService::class)->paymentCalculation($this->selectedFine(), $this->paymentDate);
    }

    public function review(): void
    {
        abort_unless(EmployeeRoleService::canManageFines(), 403);
        try {
            $this->validatePayment();
            $this->reviewing = true;
        } catch (\DomainException $e) {
            $this->addError('paymentDate', $e->getMessage());
        }
    }

    public function confirm(): void
    {
        abort_unless(EmployeeRoleService::canManageFines(), 403);
        abort_unless($this->reviewing, 403);
        $librarian = auth()->user()?->librarian;
        if (! $librarian) {
            $this->addError('payment', 'A librarian profile is required to record a payment.');
            return;
        }
        try {
            $this->validatePayment();
            app(FineService::class)->confirmOverduePayment($this->fineId, $this->studentId, $librarian, $this->paymentDate, trim($this->receiptNumber));
            $this->reviewing = false;
            $this->fineId = null;
            $this->receiptNumber = '';
            $this->success = 'Payment recorded on ' . now()->format('M d, Y') . '. The fine is paid.';
        } catch (\DomainException $e) {
            $this->reviewing = false;
            $this->addError('payment', $e->getMessage());
        }
    }

    public function render()
    {
        abort_unless(EmployeeRoleService::canManageFines(), 403);
        $students = strlen(trim($this->search)) >= 2 ? Student::where(function ($query) {
            $term = '%' . trim($this->search) . '%';
            $query->where('school_id_number', 'like', $term)->orWhere('first_name', 'like', $term)->orWhere('last_name', 'like', $term);
        })->limit(10)->get() : collect();
        $student = $this->studentId ? Student::find($this->studentId) : null;
        $fines = $student ? Fine::with(['transaction.book.bookDetail.bookData', 'fineType'])
            ->where('student_id', $student->id)->where('fine_status', 'unpaid')
            ->whereHas('transaction', fn ($query) => $query->whereNotNull('return_date'))
            ->whereHas('fineType', fn ($query) => $query->where('name', 'Overdue'))->get() : collect();
        $fine = $fines->firstWhere('id', $this->fineId);
        $calculation = null;
        if ($fine && $this->paymentDate && \Illuminate\Support\Facades\Validator::make(['date' => $this->paymentDate], ['date' => 'date_format:Y-m-d'])->passes()) {
            try { $calculation = app(FineService::class)->paymentCalculation($fine, $this->paymentDate); } catch (\DomainException $e) { }
        }
        return view('livewire.pages.dashboard.process-fine-payment', compact('students', 'student', 'fines', 'fine', 'calculation'));
    }
}
