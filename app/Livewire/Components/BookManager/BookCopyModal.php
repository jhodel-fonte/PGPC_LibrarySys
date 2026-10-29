<?php

namespace App\Livewire\Components\BookManager;

use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookDetail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class BookCopyModal extends Component
{
    public bool $isOpen = false;
    public bool $isMultiple = false;
    public bool $isEditing = false;
    public ?int $copyId = null;
    public int $addedCount = 0;
    public ?int $bookDetailId = null;
    public ?BookDetail $bookDetail = null;

    public string $accessionNumber = '';
    public string $uniqueCode = '';
    public string $location = '';
    public ?int $conditionId = null;
    public string $status = 'available';
    public string $notes = '';

    protected function rules(): array
    {
        $accessionRule = Rule::unique('books', 'accession_number');
        $codeRule = Rule::unique('books', 'code');

        if ($this->isEditing && $this->copyId) {
            $accessionRule = $accessionRule->ignore($this->copyId);
            $codeRule = $codeRule->ignore($this->copyId);
        }

        return [
            'accessionNumber' => ['required', 'string', 'max:255', $accessionRule],
            'uniqueCode' => ['nullable', 'string', 'max:255', $codeRule],
            'location' => ['required', 'string', 'max:255'],
            'conditionId' => ['required', 'exists:book_conditions,id'],
            'status' => ['required', 'string', 'in:available,borrowed,reserved,damaged,lost,maintenance'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'accessionNumber.required' => 'Accession number is required.',
            'accessionNumber.unique' => 'This accession number is already assigned to another copy.',
            'location.required' => 'Shelf location is required.',
            'conditionId.required' => 'Physical condition is required.',
            'status.required' => 'Status is required.',
            'uniqueCode.unique' => 'This unique code is already assigned to another book copy.',
        ];
    }

    #[On('open-add-copy')]
    #[On('open-book-copy-modal')]
    public function openAddCopy($bookDetailId = null, $isMultiple = false): void
    {
        $this->isEditing = false;
        $this->copyId = null;

        $id = is_array($bookDetailId) ? ($bookDetailId['bookDetailId'] ?? $bookDetailId['id'] ?? null) : $bookDetailId;
        if (is_array($bookDetailId) && isset($bookDetailId['isMultiple'])) {
            $this->isMultiple = (bool) $bookDetailId['isMultiple'];
        } else {
            $this->isMultiple = (bool) $isMultiple;
        }

        if (!$id) {
            return;
        }

        $this->bookDetailId = (int) $id;
        $this->bookDetail = BookDetail::with([
            'bookData.authors',
            'publisher',
            'books.condition',
        ])->find($this->bookDetailId);

        if (!$this->bookDetail) {
            return;
        }

        $this->resetErrorBag();
        $this->accessionNumber = '';
        $this->notes = '';
        $this->status = 'available';
        $this->addedCount = 0;

        // Auto-generate unique barcode
        $this->generateUniqueCode();

        // Pre-fill location from existing copies or call number
        $existingLocation = $this->bookDetail->books->pluck('location')->filter()->first();
        $this->location = $existingLocation ?: ($this->bookDetail->call_number ?: '231');

        // Pre-fill default condition (Good or first)
        $defaultCondition = BookCondition::whereRaw('LOWER(status) = ?', ['good'])->first()
            ?: BookCondition::first();
        $this->conditionId = $defaultCondition ? $defaultCondition->id : null;

        $this->isOpen = true;
        $this->dispatch('focus-accession-input');
    }

    #[On('open-edit-copy')]
    public function openEditCopy($copyId = null): void
    {
        $id = is_array($copyId) ? ($copyId['copyId'] ?? $copyId['id'] ?? null) : $copyId;
        if (!$id) {
            return;
        }

        $copy = Book::with([
            'bookDetail.bookData.authors',
            'bookDetail.publisher',
            'condition',
        ])->find($id);

        if (!$copy) {
            return;
        }

        $this->resetErrorBag();
        $this->isEditing = true;
        $this->isMultiple = false;
        $this->copyId = $copy->id;
        $this->bookDetailId = $copy->book_detail_id;
        $this->bookDetail = $copy->bookDetail;

        $this->accessionNumber = (string) $copy->accession_number;
        $this->uniqueCode = (string) ($copy->code ?? '');
        $this->location = (string) ($copy->location ?? '');
        $this->conditionId = $copy->book_condition_id;
        $this->status = (string) ($copy->status ?? 'available');
        $this->notes = '';

        $this->isOpen = true;
        $this->dispatch('focus-accession-input');
    }

    public function generateUniqueCode(): void
    {
        $this->uniqueCode = 'BK-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
    }

    public function saveCopy(): void
    {
        if ($this->isEditing) {
            if (auth()->check() && !auth()->user()->can('update', Book::class)) {
                abort(403, 'Unauthorized action. You do not have permission to update book copies.');
            }

            $this->validate();

            if (!$this->copyId) {
                return;
            }

            $book = Book::find($this->copyId);
            if ($book) {
                $book->update([
                    'book_condition_id' => $this->conditionId,
                    'accession_number' => trim($this->accessionNumber),
                    'code' => trim($this->uniqueCode) ?: null,
                    'location' => trim($this->location),
                    'status' => strtolower($this->status),
                ]);

                $this->dispatch('toast', message: "Physical copy '{$book->accession_number}' updated successfully.", type: 'success');
                $this->dispatch('copy-updated', bookDetailId: $this->bookDetailId, copyId: $this->copyId);
                $this->dispatch('book-details-updated');
            }

            $this->close();
            return;
        }

        if (auth()->check() && !auth()->user()->can('create', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to add book copies.');
        }

        $this->validate();

        if (!$this->bookDetailId) {
            return;
        }

        $book = Book::create([
            'book_detail_id' => $this->bookDetailId,
            'book_condition_id' => $this->conditionId,
            'accession_number' => trim($this->accessionNumber),
            'code' => trim($this->uniqueCode) ?: null,
            'location' => trim($this->location),
            'status' => strtolower($this->status),
            'date_acquired' => now(),
        ]);

        $this->addedCount++;
        $this->dispatch('toast', message: "Physical copy '{$book->accession_number}' added successfully.", type: 'success');
        $this->dispatch('copy-added', bookDetailId: $this->bookDetailId, isMultiple: $this->isMultiple);
        $this->dispatch('book-details-updated');

        if ($this->isMultiple) {
            $this->accessionNumber = '';
            $this->generateUniqueCode();
            $this->resetErrorBag();
            $this->dispatch('focus-accession-input');
        } else {
            $this->close();
        }
    }

    public bool $showDeleteModal = false;

    public function confirmDelete(): void
    {
        abort_unless(auth()->user()?->can('delete', Book::class), 403);
        if ($this->copyId) {
            $this->dispatch('request-catalog-delete', type: 'copy', ids: [$this->copyId]);
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteCopy(): void
    {
        $this->confirmDelete();
    }

    #[On('catalog-deletion-completed')]
    public function closeAfterCatalogDeletion(): void
    {
        if ($this->isOpen) {
            $this->close();
        }
    }
    #[On('restore-book-copy')]
    public function restoreBookCopy($copyId = null): void
    {
        $id = is_array($copyId) ? ($copyId['copyId'] ?? $copyId['id'] ?? null) : $copyId;
        if (!$id) {
            return;
        }

        $book = Book::withTrashed()->find($id);
        if ($book && $book->trashed()) {
            abort_unless(auth()->user()?->can('update', Book::class) || auth()->user()?->can('create', Book::class), 403);
            BookDetail::onlyTrashed()->find($book->book_detail_id)?->restore();
            $book->restore();
            $this->dispatch('toast', message: "Physical copy '{$book->accession_number}' was restored successfully.", type: 'success');
            $this->dispatch('copy-updated', bookDetailId: $book->book_detail_id, copyId: $book->id);
            $this->dispatch('book-details-updated');
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->isEditing = false;
        $this->copyId = null;
        $this->showDeleteModal = false;
        $this->resetErrorBag();
        $this->dispatch('close-add-copy');
    }

    public function render()
    {
        $conditions = BookCondition::orderBy('id')->get();
        $locations = Book::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->toArray();

        // Ensure default location if empty
        if (empty($locations)) {
            $locations = ['231', 'Main Library', 'Section A', 'Section B'];
        }

        return view('livewire.components.book-manager.book-copy-modal', [
            'conditions' => $conditions,
            'locations' => $locations,
        ]);
    }
}
