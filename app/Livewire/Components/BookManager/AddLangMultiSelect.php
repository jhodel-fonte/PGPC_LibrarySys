<?php

namespace App\Livewire\Components\BookManager;

use App\Models\Language;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class AddLangMultiSelect extends Component
{
    #[Modelable]
    public array $selected = [];

    public function createAndSelectLanguage(string $name): array
    {
        $name = trim($name);
        if (!empty($name)) {
            $languageRecord = Language::firstOrCreate(['lang' => $name]);
            if (!in_array($languageRecord->lang, $this->selected)) {
                $this->selected[] = $languageRecord->lang;
            }
            return ['id' => $languageRecord->id, 'name' => $languageRecord->lang];
        }
        return [];
    }

    public function render()
    {
        $languages = Language::orderBy('lang')->get();

        return view('livewire.components.book-manager.add-lang-multi-select', [
            'languages' => $languages,
        ]);
    }
}
