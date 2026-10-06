<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Edit Book', 'activepageRoute' => 'admin.book-management.index'])]
class EditBook extends Component
{
    public int|string $id;

    public function mount($id)
    {
        if (auth()->check() && !auth()->user()->can('update', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to edit catalog books.');
        }

        $this->id = $id;
    }

    public function render()
    {
        return view('livewire.pages.dashboard.edit-book');
    }
}
