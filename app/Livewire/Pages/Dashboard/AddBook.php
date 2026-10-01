<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Add New Book', 'activepageRoute' => 'admin.book-management.index'])]
class AddBook extends Component
{
    public function render()
    {
        return view('livewire.pages.dashboard.add-book');
    }
}
