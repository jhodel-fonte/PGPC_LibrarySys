<?php

namespace App\Livewire\Pages\Main;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.home')]
class BookDetails extends Component
{
    public $identifier = null;

    public function mount($identifier = null)
    {
        $this->identifier = $identifier ?? request('identifier') ?? request('id');
    }

    public function render()
    {
        return view('livewire.pages.main.book-details');
    }
}
