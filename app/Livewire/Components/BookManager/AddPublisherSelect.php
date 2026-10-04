<?php

namespace App\Livewire\Components\BookManager;

use App\Models\Publisher;
use Livewire\Component;

class AddPublisherSelect extends Component
{
    public ?int $publisherId = null;
    public string $publisherName = '';
    public string $selectedPublisherName = '';

    public function mount(?int $publisherId = null, string $publisherName = '', string $selectedPublisherName = '')
    {
        $this->publisherId = $publisherId;
        $this->publisherName = $publisherName;
        $this->selectedPublisherName = $selectedPublisherName;
    }

    public function selectPublisher(int $id, string $name): void
    {
        $this->publisherId = $id;
        $this->selectedPublisherName = $name;
        $this->publisherName = $name;
        $this->dispatch('publisher-selected', id: $id, name: $name);
    }

    public function setAsNewPublisher(string $name): void
    {
        $name = trim($name);
        $this->publisherId = null;
        $this->selectedPublisherName = $name;
        $this->publisherName = $name;
        $this->dispatch('publisher-selected', id: null, name: $name);
    }

    public function clearPublisher(): void
    {
        $this->publisherId = null;
        $this->selectedPublisherName = '';
        $this->publisherName = '';
        $this->dispatch('publisher-cleared');
    }

    public function render()
    {
        $publishers = Publisher::orderBy('name')->get();

        return view('livewire.components.book-manager.add-publisher-select', [
            'publishers' => $publishers,
        ]);
    }
}
