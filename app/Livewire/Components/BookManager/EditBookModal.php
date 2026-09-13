<?php

namespace App\Livewire\Components\BookManager;

use App\Models\Book;
use App\Models\BookDetail;
use Linkxtr\QrCode\Facades\QrCode;
use Livewire\Attributes\On;
use Livewire\Component;

class EditBookModal extends Component
{
    public ?int $bookDetailId = null;
    public ?int $bookId = null;
    public bool $isOpen = false;

    #[On('open-book-details')]
    public function loadBookDetail($id = null, $bookDetailId = null, $bookId = null): void
    {
        $targetDetailId = $bookDetailId ?? $id;

        if (!$targetDetailId && $bookId) {
            $book = Book::find($bookId);
            $targetDetailId = $book?->book_detail_id;
        }

        if ($targetDetailId) {
            $this->bookDetailId = (int) $targetDetailId;
            $this->bookId = $bookId ? (int) $bookId : null;
            $this->isOpen = true;
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $bookDetail = $this->bookDetailId
            ? BookDetail::with([
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

        return view('livewire.components.book-manager.edit-book-modal', [
            'bookDetail' => $bookDetail,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }
}
