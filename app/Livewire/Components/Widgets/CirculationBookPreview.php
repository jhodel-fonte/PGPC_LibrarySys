<?php

namespace App\Livewire\Components\Widgets;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class CirculationBookPreview extends Component
{
    #[Reactive]
    public $book = null;

    #[Reactive]
    public $returnedBooks = [];

    #[Reactive]
    public $checkoutBooks = [];

    public string $mode = 'check-in'; // 'check-in' or 'check-out'

    public function undoReturn($accession)
    {
        $this->dispatch('undo-return', accession: $accession);
    }

    public function returnBook($accession)
    {
        $this->dispatch('return-book', accession: $accession);
    }

    public function removeCheckoutBook($accession)
    {
        $this->dispatch('remove-checkout-book', accession: $accession);
    }

    public function addCheckoutBook($accession)
    {
        $this->dispatch('add-checkout-book', accession: $accession);
    }

    public function render()
    {
        $bookData = $this->book;
        if ($bookData && is_object($bookData) && !($bookData instanceof \ArrayAccess)) {
            $bookData = (array) $bookData;
        }

        $returnedList = is_array($this->returnedBooks) ? $this->returnedBooks : [];
        $checkoutList = is_array($this->checkoutBooks) ? $this->checkoutBooks : [];

        $isReturned = false;
        if ($bookData) {
            $isReturned = !empty($bookData['is_returned']) || in_array($bookData['book_id'] ?? null, $returnedList);
        }

        $isInCheckout = false;
        if ($bookData && !empty($checkoutList)) {
            $acc = $bookData['accession'] ?? null;
            $id = $bookData['book_id'] ?? null;
            foreach ($checkoutList as $cb) {
                if (($acc && ($cb['accession'] ?? null) === $acc) || ($id && ($cb['book_id'] ?? null) === $id)) {
                    $isInCheckout = true;
                    break;
                }
            }
        }

        return view('livewire.components.widgets.circulation-book-preview', [
            'bookData' => $bookData,
            'isReturned' => $isReturned,
            'isInCheckout' => $isInCheckout,
        ]);
    }
}
