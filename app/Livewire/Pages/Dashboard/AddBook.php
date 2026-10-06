<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Add New Book', 'activepageRoute' => 'admin.book-management.index'])]
class AddBook extends Component
{
    public function mount()
    {
        if (auth()->check() && !auth()->user()->can('create', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to add catalog books.');
        }
    }

    public function render()
    {
        return view('livewire.pages.dashboard.add-book');
    }
}
