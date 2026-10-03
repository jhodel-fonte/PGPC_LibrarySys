<?php

namespace App\Livewire\Components\BookManager;

use App\Models\Category;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class AddCategoryMultiSelect extends Component
{
    #[Modelable]
    public array $selected = [];

    public function createAndSelectCategory(string $name): array
    {
        $name = trim($name);
        if (!empty($name)) {
            $category = Category::firstOrCreate(['name' => $name]);
            if (!in_array($category->id, $this->selected)) {
                $this->selected[] = $category->id;
            }
            return ['id' => $category->id, 'name' => $category->name];
        }
        return [];
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();

        return view('livewire.components.book-manager.add-category-multi-select', [
            'categories' => $categories,
        ]);
    }
}
