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
    #[On('book-details-updated')]
    public function refreshBookDetail(): void
    {
        // Re-renders view with updated copies count and relationships
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->bookDetailId = null;
        $this->bookId = null;
        $this->isLoading = false;
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
                'books.condition',
            ])->find($this->bookDetailId)
            : null;

        $qrCodeSvg = null;
        if ($bookDetail) {
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
