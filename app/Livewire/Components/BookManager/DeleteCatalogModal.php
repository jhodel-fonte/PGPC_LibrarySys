<?php

namespace App\Livewire\Components\BookManager;

use App\Models\Book;
use App\Models\BookDetail;
use App\Services\CatalogDeletionService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DeleteCatalogModal extends Component
{
    #[Locked] public array $ids = [];
    #[Locked] public string $type = 'copy';
    public bool $open = false;
    public string $confirmation = '';
    public array $impact = [];
    public string $label = '';

    #[On('request-catalog-delete')]
    public function show(string $type, array $ids): void
    {
        abort_unless(auth()->user()?->can('delete', Book::class), 403);
        abort_unless(in_array($type, ['copy', 'title'], true), 422);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
        abort_if(! $ids, 422);
        $this->type = $type;
        $this->ids = $ids;
        $this->confirmation = '';
        $this->resetErrorBag();
        $this->impact = app(CatalogDeletionService::class)->inspect($type, $ids);
        $this->label = $type === 'title'
            ? (BookDetail::with('bookData')->find($ids[0])?->bookData?->book_title ?? 'Selected title')
            : (Book::find($ids[0])?->accession_number ?? 'Selected copy');
        $this->open = true;
    }

    public function confirm(): void
    {
        abort_unless(auth()->user()?->can('delete', Book::class), 403);
        if (! $this->open || ! $this->ids) {
            return;
        }
        try {
            app(CatalogDeletionService::class)->delete($this->type, $this->ids, $this->confirmation === 'DELETE');
            $this->open = false;
            $this->dispatch('book-details-updated');
            $this->dispatch('catalog-deletion-completed');
            $this->dispatch('toast', message: 'Catalog records deleted.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->impact = app(CatalogDeletionService::class)->inspect($this->type, $this->ids);
            $this->addError('confirmation', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.components.book-manager.delete-catalog-modal');
    }
}
