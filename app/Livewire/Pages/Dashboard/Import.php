<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Import Records', 'activepageRoute' => 'admin.book-management.index'])]
class Import extends Component
{
    public function render()
    {
        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';

        return view('livewire.pages.dashboard.import', [
            'routePrefix' => $routePrefix,
        ]);
    }
}
