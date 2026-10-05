<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Edit Book', 'activepageRoute' => 'admin.book-management.index'])]
class EditBook extends Component
{
    public int|string $id;

    public function mount($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        return view('livewire.pages.dashboard.edit-book');
    }
}
