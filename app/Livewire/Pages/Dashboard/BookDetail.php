<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use App\Models\BookDetail as BookDetailModel;
use Linkxtr\QrCode\Facades\QrCode;
use Livewire\Attributes\On;
use Livewire\Component;

class BookDetail extends Component
{
    public ?int $bookDetailId = null;
    public ?int $bookId = null;
    public bool $isOpen = false;
    public bool $isLoading = false;

    // Delete copy confirmation modal properties
    public bool $showDeleteCopyModal = false;
    public ?int $copyIdToDelete = null;
    public ?string $copyAccessionToDelete = null;

    public function mount(): void
    {
        $detailId = request()->query('detail');
        if (!empty($detailId)) {
            $this->loadBookDetail((int) $detailId);
        }
    }

    #[On('open-book-details')]
    public function loadBookDetail($id = null, $bookDetailId = null, $bookId = null): void
    {
        $this->isLoading = true;
        $this->bookDetailId = null; // Clear previous book data immediately

        $targetDetailId = $bookDetailId ?? $id;

        if (!$targetDetailId && $bookId) {
            $book = Book::find($bookId);
            $targetDetailId = $book?->book_detail_id;
        }

        if ($targetDetailId) {
            $this->bookDetailId = (int) $targetDetailId;
            $this->bookId = $bookId ? (int) $bookId : null;
        }

        $this->isOpen = true;
        $this->isLoading = false;
        $this->dispatch('book-details-loaded', id: $this->bookDetailId);
    }

    #[On('copy-added')]
    #[On('copy-updated')]
    #[On('copy-deleted')]
    #[On('book-details-updated')]
    public function refreshBookDetail(): void
    {
        // Re-renders view with updated copies count and relationships
    }

    public function confirmDeleteCopy(int $copyId): void
    {
        abort_unless(auth()->user()?->can('delete', Book::class), 403);
        $this->dispatch('request-catalog-delete', type: 'copy', ids: [$copyId]);
    }

    public function cancelDeleteCopy(): void
    {
        $this->showDeleteCopyModal = false;
        $this->copyIdToDelete = null;
    }

    public function deleteCopy(?int $copyId = null): void
    {
        if ($copyId ?? $this->copyIdToDelete) {
            $this->confirmDeleteCopy($copyId ?? $this->copyIdToDelete);
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
            BookDetailModel::onlyTrashed()->find($book->book_detail_id)?->restore();
            $book->restore();
            $this->dispatch('toast', message: "Physical copy '{$book->accession_number}' was restored successfully.", type: 'success');
            $this->dispatch('book-details-updated');
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->bookDetailId = null;
        $this->bookId = null;
        $this->isLoading = false;
        $this->cancelDeleteCopy();
    }

    public function render()
    {
        $bookDetail = $this->bookDetailId
            ? BookDetailModel::with([
                'bookData.authors',
                'bookData.categories',
                'bookData.language',
                'publisher',
                'bookType',
                'periodicalIssue',
                'books' => fn ($q) => $q->whereNull('deleted_at')->with('condition'),
            ])->find($this->bookDetailId)
            : null;

        $qrCodeSvg = null;
        if ($bookDetail && in_array($bookDetail->catalog_kind, ['book', 'magazine', 'journal'], true)) {
            try {
                $payload = route('opac.book.detail', ['identifier' => $bookDetail->isbn ?: $bookDetail->id]);
                $qrCodeSvg = (string) QrCode::size(200)
                    ->margin(0)
                    ->color(16, 43, 112)
                    ->generate($payload);
            } catch (\Throwable $e) {
                $qrCodeSvg = null;
            }
        }

        return view('livewire.pages.dashboard.book-detail', [
            'bookDetail' => $bookDetail,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }
}
