<?php

namespace App\Livewire\Components\BookManager;

use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadBookCoverImage extends Component
{
    use WithFileUploads;

    #[Modelable]
    public $coverImage = null;

    /**
     * Validate the uploaded image file size and format.
     */
    public function updatedCoverImage(): void
    {
        $maxSetting = config('settings.coverFile_max_size', '5MB');
        $maxKb = $this->parseSizeToKilobytes($maxSetting);
        $coverType = config('settings.coverFile_types', 'png,jpg,jpeg,webp');
        $coverType = str_replace(',', '|', $coverType);

        $this->validate([
            'coverImage' => 'nullable|image|max:' . $maxKb . '|mimes:' . $coverType,
        ], [
            'coverImage.image' => 'The file must be a valid image (PNG, JPG, JPEG, WEBP).',
            'coverImage.max' => 'The cover image size may not exceed ' . $maxSetting . '.',
        ]);
    }

    public function removeCoverImage(): void
    {
        $this->coverImage = null;
        $this->resetErrorBag('coverImage');
        $this->dispatch('cover-image-removed');
    }

    protected function parseSizeToKilobytes($size): int
    {
        if (is_numeric($size)) {
            return (int) $size;
        }
        $size = strtoupper(trim((string)$size));
        if (str_ends_with($size, 'MB')) {
            return (int) rtrim($size, 'MB') * 1024;
        }
        if (str_ends_with($size, 'KB')) {
            return (int) rtrim($size, 'KB');
        }
        if (str_ends_with($size, 'GB')) {
            return (int) rtrim($size, 'GB') * 1024 * 1024;
        }
        return 5120;
    }

    public function render()
    {
        return view('livewire.components.book-manager.upload-book-cover-image');
    }
}

