<?php

namespace App\Livewire\Components\Widgets;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class CirculationBookPreview extends Component
{
    #[Reactive]
    public $book = null;

    #[Reactive]
    public array $returnedBooks = [];

    #[Reactive]
    public array $checkoutBooks = [];

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

        $isReturned = false;
        if ($bookData) {
            $isReturned = !empty($bookData['is_returned']) || in_array($bookData['book_id'] ?? null, $this->returnedBooks);
        }

        $isInCheckout = false;
        if ($bookData && !empty($this->checkoutBooks)) {
            $acc = $bookData['accession'] ?? null;
            $id = $bookData['book_id'] ?? null;
            foreach ($this->checkoutBooks as $cb) {
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
