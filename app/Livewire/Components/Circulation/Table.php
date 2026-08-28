<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class Table extends Component
{
    #[Reactive]
    public $borrowedBooks = [];

    #[Reactive]
    public $scannedMember = null;

    public $mode = 'check-in'; // check-in, check-out
    public $title = null;
    public $class = 'md:col-span-8 h-[400px] md:h-full';

    public function render()
    {
        // Set default titles based on mode if not provided
        $displayTitle = $this->title;
        if (empty($displayTitle)) {
            $displayTitle = $this->mode === 'check-out' ? 'Books to Issue' : 'Borrowed Books';
        }

        return view('livewire.components.circulation.table', [
            'displayTitle' => $displayTitle
        ]);
    }
}
