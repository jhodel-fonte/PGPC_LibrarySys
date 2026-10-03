<?php

namespace App\Livewire\Forms;

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
use Livewire\Component;
use Livewire\WithFileUploads;

class AddBookForm extends Component
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
    public string $authorSearch = '';
    public string $selectedAuthorName = '';
    public string $newAuthorLastName = '';
    public string $newAuthorFirstName = '';
    public bool $showManualAuthor = false;

    // Identifiers & Publication
    public string $isbn = '';
    public string $issn = '';
    public string $callNumber = '';
    public string $classification = '';
    public ?int $publisherId = null;
    public string $publisherName = '';
    public string $selectedPublisherName = '';
    public ?int $publicationYear = null;
    public string $edition = '';
    public ?int $pages = null;

    // Categories & Language
    public array $selectedCategories = [];
    public array $selectedLanguages = ['English'];
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
        if (empty($this->selectedLanguages)) {
            $this->selectedLanguages = !empty($this->language) ? [$this->language] : ['English'];
        }
    }

    public function generateBarcode()
    {
        $this->barcode = 'BK-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $this->dispatch('toast', message: 'Barcode generated: ' . $this->barcode, type: 'info');
    }

    public function selectAuthor(int $id, string $name)
    {
        $this->authorId = $id;
        $this->selectedAuthorName = $name;
        $this->authorSearch = $name;
        $this->newAuthorLastName = '';
        $this->newAuthorFirstName = '';
        $this->resetErrorBag('authorSearch');
    }

    public function clearAuthor()
    {
        $this->authorId = null;
        $this->selectedAuthorName = '';
        $this->authorSearch = '';
        $this->newAuthorLastName = '';
        $this->newAuthorFirstName = '';
    }

    public function toggleManualAuthor()
    {
        $this->showManualAuthor = !$this->showManualAuthor;
    }

    public function selectPublisher(int $id, string $name)
    {
        $this->publisherId = $id;
        $this->selectedPublisherName = $name;
        $this->publisherName = $name;
        $this->resetErrorBag('publisherName');
    }

    public function clearPublisher()
    {
        $this->publisherId = null;
        $this->selectedPublisherName = '';
        $this->publisherName = '';
    }

    public function updatedSelectedLanguages(): void
    {
        $this->language = implode(', ', $this->selectedLanguages);
    }

    public function createAndSelectCategory(string $name): array
    {
        $name = trim($name);
        if (!empty($name)) {
            $category = Category::firstOrCreate(['name' => $name]);
            if (!in_array($category->id, $this->selectedCategories)) {
                $this->selectedCategories[] = $category->id;
            }
            return ['id' => $category->id, 'name' => $category->name];
        }
        return [];
    }

    public function createAndSelectLanguage(string $name): array
    {
        $name = trim($name);
        if (!empty($name)) {
            $languageRecord = Language::firstOrCreate(['lang' => $name]);
            if (!in_array($languageRecord->lang, $this->selectedLanguages)) {
                $this->selectedLanguages[] = $languageRecord->lang;
                $this->language = implode(', ', $this->selectedLanguages);
            }
            return ['id' => $languageRecord->id, 'name' => $languageRecord->lang];
        }
        return [];
    }

    public function save()
    {
        $this->errorMessage = '';

        // Check if author is provided (Required)
        $hasAuthorId = !empty($this->authorId);
        $hasAuthorSearch = !empty(trim($this->authorSearch));
        $hasNewNames = !empty(trim($this->newAuthorLastName)) || !empty(trim($this->newAuthorFirstName));

        if (!$hasAuthorId && !$hasAuthorSearch && !$hasNewNames) {
            $this->addError('authorSearch', 'The Author field is required.');
        }

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
            'authorSearch' => 'nullable|string|max:200',
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
            'selectedLanguages' => 'nullable|array',
            'language' => 'nullable|string|max:150',
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
                // 1. Resolve or Create Languages
                $languageNames = !empty($this->selectedLanguages)
                    ? array_unique(array_filter(array_map('trim', $this->selectedLanguages)))
                    : [trim($this->language) ?: 'English'];

                $languageIds = [];
                $primaryLanguage = null;

                foreach ($languageNames as $langName) {
                    if (!empty($langName)) {
                        $langModel = Language::firstOrCreate(['lang' => $langName]);
                        $languageIds[] = $langModel->id;
                        if (!$primaryLanguage) {
                            $primaryLanguage = $langModel;
                        }
                    }
                }

                if (!$primaryLanguage) {
                    $primaryLanguage = Language::firstOrCreate(['lang' => 'English']);
                    $languageIds[] = $primaryLanguage->id;
                }

                $this->language = implode(', ', $languageNames);

                // 2. Create BookData
                $bookData = BookData::create([
                    'book_title' => trim($this->bookTitle),
                    'subtitle' => trim($this->subtitle) ?: null,
                    'description' => trim($this->bookDescription) ?: null,
                    'note' => trim($this->notes) ?: null,
                    'language_id' => $primaryLanguage->id,
                    'copyright_year' => $this->copyrightYear ?: $this->publicationYear,
                ]);

                // 3. Resolve Authors
                $authorIds = [];
                if ($this->authorId) {
                    $authorIds[] = $this->authorId;
                } else {
                    $firstName = trim($this->newAuthorFirstName);
                    $lastName = trim($this->newAuthorLastName);
                    $searchVal = trim($this->authorSearch);

                    if (empty($firstName) && empty($lastName) && !empty($searchVal)) {
                        $parts = preg_split('/\s+/', $searchVal);
                        if (count($parts) > 1) {
                            $lastName = array_pop($parts);
                            $firstName = implode(' ', $parts);
                        } else {
                            $firstName = $searchVal;
                            $lastName = $searchVal;
                        }
                    }

                    if (!empty($firstName) || !empty($lastName)) {
                        $existingAuthor = Author::where('first_name', $firstName ?: $lastName)
                            ->where('last_name', $lastName ?: $firstName)
                            ->first();

                        if ($existingAuthor) {
                            $authorIds[] = $existingAuthor->id;
                        } else {
                            $newAuthor = Author::create([
                                'first_name' => $firstName ?: $lastName,
                                'last_name' => $lastName ?: $firstName,
                            ]);
                            $authorIds[] = $newAuthor->id;
                        }
                    }
                }

                if (!empty($authorIds)) {
                    $bookData->authors()->sync(array_unique($authorIds));
                }

                // 4. Sync Categories
                if (!empty($this->selectedCategories)) {
                    $bookData->categories()->sync($this->selectedCategories);
                }

                // 5. Resolve Publisher
                $pub = null;
                if ($this->publisherId) {
                    $pub = Publisher::find($this->publisherId);
                }
                if (!$pub) {
                    $pubName = trim($this->publisherName);
                    if (empty($pubName)) {
                        $pub = Publisher::firstOrCreate(['name' => 'Independent / Unknown']);
                    } else {
                        $pub = Publisher::firstOrCreate(['name' => $pubName]);
                    }
                }

                // 6. Handle Cover Image
                $coverPath = null;
                if ($this->coverImage) {
                    $coverPath = $this->coverImage->store('book-covers', 'public');
                }

                // 7. Resolve default Book Type
                $defaultBookType = BookType::first() ?? BookType::firstOrCreate(['type' => 'Book']);
                $bookTypeId = $defaultBookType->id;

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
                $condition = BookCondition::where('status', 'Good')->first()
                    ?? BookCondition::first()
                    ?? BookCondition::firstOrCreate(['status' => 'Good']);
                $conditionId = $condition->id;

                // 10. Create Physical Book Copy
                $book = Book::create([
                    'book_detail_id' => $bookDetail->id,
                    'book_condition_id' => $conditionId,
                    'accession_number' => trim($this->accessionNumber),
                    'code' => trim($this->barcode) ?: null,
                    'location' => trim($this->location) ?: null,
                    'status' => strtolower($this->status),
                    'date_acquired' => $this->dateAcquired ? Carbon::parse($this->dateAcquired)->toDateString() : Carbon::now()->toDateString(),
                ]);

                // 11. Sync Multiple Languages to Physical Book Copy (via language_books pivot)
                if (!empty($languageIds)) {
                    $book->languages()->sync(array_unique($languageIds));
                }
            });

            session()->flash('successMessage', 'Book "' . $this->bookTitle . '" registered successfully into library catalog.');
            return $this->redirect(route('admin.book-management.index'), navigate: true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = 'Please review and fix the highlighted fields before submitting.';
            $this->dispatch('toast', message: 'Form validation failed. Please check highlighted errors.', type: 'warning');
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error registering book: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            $errorCode = $e->getCode();
            $sqlMessage = $e->getMessage();

            if ($errorCode === '23505' || str_contains($sqlMessage, '23505') || str_contains($sqlMessage, 'Unique violation')) {
                if (str_contains($sqlMessage, 'accession_number')) {
                    $this->addError('accessionNumber', 'This Accession Number is already in use.');
                    $this->errorMessage = 'A book with this Accession Number already exists in the system.';
                } elseif (str_contains($sqlMessage, 'code')) {
                    $this->addError('barcode', 'This Barcode / Code is already registered.');
                    $this->errorMessage = 'This Barcode / Item Code is already assigned to another book.';
                } elseif (str_contains($sqlMessage, '_pkey')) {
                    // Attempt self-healing of postgres sequence if pkey collision happens
                    $this->healDatabaseSequences();
                    $this->errorMessage = 'A database ID sequence conflict occurred and was automatically corrected. Please click Save again.';
                } else {
                    $this->errorMessage = 'A unique record conflict occurred while saving the book.';
                }
            } elseif ($errorCode === '23503' || str_contains($sqlMessage, 'foreign key constraint')) {
                $this->errorMessage = 'A referenced record (category, language, or publisher) could not be resolved.';
            } elseif ($errorCode === '23502' || str_contains($sqlMessage, 'not-null constraint')) {
                $this->errorMessage = 'A required database field was left empty.';
            } else {
                $this->errorMessage = 'A database error occurred while saving the book. Please try again.';
            }

            $this->dispatch('toast', message: $this->errorMessage, type: 'error');
        } catch (\Throwable $e) {
            Log::error('Failed to register book: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->errorMessage = 'An unexpected error occurred: ' . $e->getMessage();
            $this->dispatch('toast', message: 'Failed to save book record: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Self-healing helper for Postgres primary key sequences
     */
    private function healDatabaseSequences(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            try {
                $tables = ['book_datas', 'book_details', 'books', 'authors', 'publishers', 'categories', 'languages', 'language_books'];
                foreach ($tables as $table) {
                    $sequenceName = $table . '_id_seq';
                    $seqExists = DB::selectOne("SELECT to_regclass('{$sequenceName}') as exists");
                    if ($seqExists && $seqExists->exists) {
                        $maxId = DB::table($table)->max('id');
                        if ($maxId !== null) {
                            DB::statement("SELECT setval('{$sequenceName}', {$maxId})");
                        }
                    }
                }
            } catch (\Throwable $t) {
                Log::warning('Sequence self-healing error: ' . $t->getMessage());
            }
        }
    }

    public function render()
    {
        $authors = Author::orderBy('first_name')->get();
        $categories = Category::orderBy('name')->get();
        $languages = Language::orderBy('lang')->get();
        $conditions = BookCondition::all();

        return view('livewire.forms.add-book-form', [
            'authors' => $authors,
            'categories' => $categories,
            'languages' => $languages,
            'conditions' => $conditions,
        ]);
    }
}
