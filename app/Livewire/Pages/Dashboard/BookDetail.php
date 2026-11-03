<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use App\Models\BookDetail as BookDetailModel;
use App\Models\BorrowingTransaction;
use App\Services\EmployeeRoleService;
use Illuminate\Support\Facades\Http;
use Linkxtr\QrCode\Facades\QrCode;
use Livewire\Attributes\On;
use Livewire\Component;

class BookDetail extends Component
{
    public ?int $bookDetailId = null;
    public ?int $bookId = null;
    public bool $isOpen = false;
    public bool $isLoading = false;
    public ?string $autofillError = null;

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
        $this->autofillError = null;
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

    public function autoFill(): void
    {
        abort_unless(auth()->user()?->can('update', Book::class), 403);
        $detail = BookDetailModel::findOrFail($this->bookDetailId);
        abort_unless($detail->catalog_kind === 'book', 404);
        $isbn = preg_replace('/[^0-9X]/i', '', strtoupper(trim((string) $detail->isbn)));
        $this->autofillError = null;
        if (!in_array(strlen($isbn), [10, 13], true)) {
            $this->autofillError = 'This book needs a valid ISBN before it can be auto-filled.';
            return;
        }

        try {
            $response = Http::timeout(12)->get('https://openlibrary.org/api/books', [
                'bibkeys' => 'ISBN:'.$isbn, 'jscmd' => 'data', 'format' => 'json',
            ]);
            if (!$response->successful()) {
                throw new \RuntimeException('Open Library request failed.');
            }
            $data = $response->json('ISBN:'.$isbn);
            if (empty($data)) {
                $fallback = Http::timeout(8)->get("https://openlibrary.org/isbn/{$isbn}.json");
                if (!$fallback->successful() && $fallback->status() !== 404) {
                    throw new \RuntimeException('Open Library request failed.');
                }
                $data = $fallback->json();
                if (empty($data['title'])) {
                    $this->autofillError = 'No Open Library match was found for this ISBN.';
                    return;
                }
            }
        } catch (\Throwable $e) {
            $this->autofillError = 'Unable to reach Open Library. Please try again.';
            return;
        }

        session()->flash('open_library_autofill', ['detail_id' => $detail->id, 'isbn' => $isbn, 'data' => $data]);
        $this->dispatch('open-library-match-found', url: route(EmployeeRoleService::getRoutePrefix().'book-management.edit', ['id' => $detail->id]));
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
                'books' => fn ($q) => $q->whereNull('deleted_at')->with('condition')
                    ->when(auth()->user()?->can('viewAny', Book::class), fn ($query) => $query->addSelect([
                        'last_return_condition_note' => BorrowingTransaction::select('return_condition_note')
                            ->whereColumn('book_id', 'books.id')->whereNotNull('return_date')
                            ->orderByDesc('return_date')->orderByDesc('id')->limit(1),
                    ])),
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
