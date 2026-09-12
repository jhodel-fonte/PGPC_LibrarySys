<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookType;
use App\Models\Category;
use App\Models\Language;
use App\Models\Publisher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Add New Book', 'activepageRoute' => 'admin.book-management.index'])]
class AddBook extends Component
{
    use WithFileUploads;

    // 1. Initial Physical Copy
    public string $accessionNumber = '';
    public string $barcode = '';
    public string $status = 'Available';
    public string $location = '';
    public string $dateAcquired = '';

    // 2. Book Cover
    public $coverImage = null;

    // 3. Bibliographic Information
    public string $bookTitle = '';
    public string $subtitle = '';

    // Authors
    public ?int $authorId = null;
    public string $newAuthorLastName = '';
    public string $newAuthorFirstName = '';

    // Identifiers & Publication
    public string $isbn = '';
    public string $issn = '';
    public string $callNumber = '';
    public string $classification = '';
    public string $publisherName = '';
    public ?int $publicationYear = null;
    public string $edition = '';
    public ?int $pages = null;

    // Categories & Language
    public array $selectedCategories = [];
    public string $language = 'English';
    public ?int $copyrightYear = null;

    // Descriptions
    public string $bookDescription = '';
    public string $notes = '';

    // Alerts
    public string $errorMessage = '';
    public string $successMessage = '';

    public function mount()
    {
        $this->dateAcquired = Carbon::now()->format('Y-m-d');
    }

    public function generateBarcode()
    {
        $this->barcode = 'BK-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $this->dispatch('toast', message: 'Barcode generated: ' . $this->barcode, type: 'info');
    }

    public function save()
    {
        $this->errorMessage = '';

        // Validation
        $this->validate([
            'accessionNumber' => 'required|string|max:50|unique:books,accession_number',
            'barcode' => 'nullable|string|max:50|unique:books,code',
            'status' => 'required|string',
            'location' => 'nullable|string|max:100',
            'dateAcquired' => 'nullable|date',
            'coverImage' => 'nullable|image|max:2048',

            'bookTitle' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'authorId' => 'nullable|exists:authors,id',
            'newAuthorLastName' => 'nullable|string|max:100',
            'newAuthorFirstName' => 'nullable|string|max:100',

            'isbn' => 'nullable|string|max:30',
            'issn' => 'nullable|string|max:30',
            'callNumber' => 'required|string|max:100',
            'classification' => 'nullable|string|max:100',
            'publisherName' => 'nullable|string|max:150',
            'publicationYear' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'edition' => 'nullable|string|max:100',
            'pages' => 'nullable|integer|min:1|max:50000',

            'selectedCategories' => 'nullable|array',
            'selectedCategories.*' => 'exists:categories,id',
            'language' => 'nullable|string|max:50',
            'copyrightYear' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),

            'bookDescription' => 'nullable|string|max:3000',
            'notes' => 'nullable|string|max:1000',
        ], [
            'accessionNumber.required' => 'The Accession Number is required.',
            'accessionNumber.unique' => 'This Accession Number is already in use.',
            'bookTitle.required' => 'The Book Title is required.',
            'callNumber.required' => 'The Call Number is required.',
            'coverImage.max' => 'The cover image may not be greater than 2MB.',
        ]);

        try {
            DB::transaction(function () {
                // 1. Resolve or Create Language
                $langName = trim($this->language) ?: 'English';
                $languageModel = Language::firstOrCreate(['lang' => $langName]);

                // 2. Create BookData
                $bookData = BookData::create([
                    'book_title' => trim($this->bookTitle),
                    'subtitle' => trim($this->subtitle) ?: null,
                    'description' => trim($this->bookDescription) ?: null,
                    'note' => trim($this->notes) ?: null,
                    'language_id' => $languageModel->id,
                    'copyright_year' => $this->copyrightYear ?: $this->publicationYear,
                ]);

                // 3. Resolve Authors
                $authorIds = [];
                if ($this->authorId) {
                    $authorIds[] = $this->authorId;
                }
                if (!empty(trim($this->newAuthorLastName)) || !empty(trim($this->newAuthorFirstName))) {
                    $newAuthor = Author::create([
                        'first_name' => trim($this->newAuthorFirstName) ?: trim($this->newAuthorLastName),
                        'last_name' => trim($this->newAuthorLastName) ?: null,
                    ]);
                    $authorIds[] = $newAuthor->id;
                }

                if (!empty($authorIds)) {
                    $bookData->authors()->sync(array_unique($authorIds));
                }

                // 4. Sync Categories
                if (!empty($this->selectedCategories)) {
                    $bookData->categories()->sync($this->selectedCategories);
                }

                // 5. Resolve Publisher
                $pubName = trim($this->publisherName);
                if (empty($pubName)) {
                    $pub = Publisher::firstOrCreate(['name' => 'Independent / Unknown']);
                } else {
                    $pub = Publisher::firstOrCreate(['name' => $pubName]);
                }

                // 6. Handle Cover Image
                $coverPath = null;
                if ($this->coverImage) {
                    $coverPath = $this->coverImage->store('book-covers', 'public');
                }

                // 7. Resolve default Book Type
                $defaultBookType = BookType::first();
                $bookTypeId = $defaultBookType ? $defaultBookType->id : 1;

                // 8. Create BookDetail
                $bookDetail = BookDetail::create([
                    'book_data_id' => $bookData->id,
                    'isbn' => trim($this->isbn) ?: null,
                    'issn' => trim($this->issn) ?: null,
                    'publication_year' => $this->publicationYear,
                    'copyright_year' => $this->copyrightYear ?: $this->publicationYear,
                    'edition' => trim($this->edition) ?: null,
                    'pages' => $this->pages,
                    'format' => 'Paperback',
                    'book_type_id' => $bookTypeId,
                    'call_number' => trim($this->callNumber),
                    'classification' => trim($this->classification) ?: null,
                    'publisher_id' => $pub->id,
                    'cover_image' => $coverPath,
                ]);

                // 9. Resolve default Book Condition
                $condition = BookCondition::where('status', 'Good')->first() ?? BookCondition::first();
                $conditionId = $condition ? $condition->id : 2;

                // 10. Create Physical Book Copy
                Book::create([
                    'book_detail_id' => $bookDetail->id,
                    'book_condition_id' => $conditionId,
                    'accession_number' => trim($this->accessionNumber),
                    'code' => trim($this->barcode) ?: null,
                    'location' => trim($this->location) ?: null,
                    'status' => strtolower($this->status),
                    'date_acquired' => $this->dateAcquired ?: Carbon::now(),
                ]);
            });

            session()->flash('successMessage', 'Book "' . $this->bookTitle . '" registered successfully into library catalog.');
            return $this->redirect(route('admin.book-management.index'), navigate: true);

        } catch (\Throwable $e) {
            Log::error('Failed to register book: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->errorMessage = 'Error saving book: ' . $e->getMessage();
            $this->dispatch('toast', message: 'Failed to save book record: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $authors = Author::orderBy('first_name')->get();
        $categories = Category::orderBy('name')->get();
        $conditions = BookCondition::all();

        return view('livewire.pages.dashboard.add-book', [
            'authors' => $authors,
            'categories' => $categories,
            'conditions' => $conditions,
        ]);
    }
}
