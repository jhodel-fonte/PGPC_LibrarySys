<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class BookList extends Component
{
    #[Reactive]
    public $books = [];

    public function render()
    {
        return view('livewire.components.circulation.book-list');
    }
}
