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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddBookForm extends Component
{
    use WithFileUploads, OpenLibraryLookup;


    // 1. Initial Physical Copy
    public string $accessionNumber = '';
    public string $barcode = '';
    public string $status = 'Available';
    public string $location = '';
    public string $dateAcquired = '';

    // 2. Book Cover (Managed via UploadBookCoverImage subcomponent or OpenLibrary retrieval)
    public $coverImage = null;          // Livewire temporary upload object or saved file name

    // 3. Bibliographic Information
    public string $bookTitle = '';
    public string $catalogKind = 'book';
    public ?int $periodicalTitleId = null;
    public string $issueVolume = '';
    public string $issueNumber = '';
    public string $issueLabel = '';
    public string $issueDate = '';
    public bool $issueBorrowable = true;
    public $issueLoanDays = null;
    public $dailyFine = null;
    public string $manufacturer = '';
    public string $modelNumber = '';
    public string $packageContents = '';
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
    public string $resourceUrl = '';
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
        if (auth()->check() && !auth()->user()->can('create', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to add catalog books.');
        }

        $this->dateAcquired = Carbon::now()->format('Y-m-d');
        if (empty($this->selectedLanguages)) {
            $this->selectedLanguages = !empty($this->language) ? [$this->language] : ['English'];
        }
    }

    public function updatedPeriodicalTitleId($value): void
    {
        if (! $value || ! in_array($this->catalogKind, ['magazine', 'journal'], true)) {
            return;
        }
        $detail = BookDetail::where('book_data_id', $value)->where('catalog_kind', $this->catalogKind)->with(['bookData', 'publisher'])->first();
        if ($detail?->bookData) {
            $this->bookTitle = $detail->bookData->book_title;
            $this->issn = $detail->issn ?? '';
            $this->resourceUrl = $detail->url ?? '';
            $this->callNumber = $detail->call_number ?? '';
            $this->classification = $detail->classification ?? '';
            $this->publisherId = $detail->publisher_id;
            $this->publisherName = $detail->publisher?->name ?? '';
            $this->selectedPublisherName = $this->publisherName;
        }
    }

    public function generateBarcode()
    {
        $prefix = $this->catalogKind === 'book' ? 'BK' : 'AST';
        $this->barcode = $prefix . '-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $this->dispatch('toast', message: 'Barcode generated: ' . $this->barcode, type: 'info');
    }

    public function updatedCatalogKind(string $value): void
    {
        $this->periodicalTitleId = null;
        $this->reset('issueVolume', 'issueNumber', 'issueLabel', 'issueDate', 'issueBorrowable', 'issueLoanDays', 'manufacturer', 'modelNumber', 'packageContents');
        $this->resetErrorBag();
        if ($value === 'book') return;

        $this->isbn = '';
        $this->issn = '';
        $this->authorId = null;
        $this->authorSearch = '';
        $this->selectedAuthorName = '';
        $this->newAuthorLastName = '';
        $this->newAuthorFirstName = '';
        $this->publisherId = null;
        $this->publisherName = '';
        $this->selectedPublisherName = '';
        $this->callNumber = '';
        $this->classification = '';
        $this->publicationYear = null;
        $this->copyrightYear = null;
        $this->edition = '';
        $this->pages = null;
        $this->selectedCategories = [];
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

    #[On('publisher-selected')]
    public function selectPublisher(?int $id = null, string $name = '')
    {
        $this->publisherId = $id;
        $this->selectedPublisherName = $name;
        $this->publisherName = $name;
        $this->resetErrorBag('publisherName');
        $this->dispatch('publisher-auto-filled', id: $id, name: $name);
    }

    #[On('publisher-cleared')]
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

    public ?int $autoDetectedCategoryId = null;

    public function updatedCallNumber($value): void
    {
        $this->autoTriggerCategory($value ?: $this->classification);
    }

    public function updatedClassification($value): void
    {
        $this->autoTriggerCategory($value ?: $this->callNumber);
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

    public function updatedCoverImage(): void
    {
        $maxSetting = config('settings.book_covers.max_size', config('settings.coverFile_max_size', '5MB'));
        $maxKb = $this->parseSizeToKilobytes($maxSetting);

        $this->validate([
            'coverImage' => 'nullable|image|max:' . $maxKb,
        ], [
            'coverImage.image' => 'The file must be a valid image (PNG, JPG, JPEG, WEBP).',
            'coverImage.max' => 'The cover image size may not exceed ' . $maxSetting . '.',
        ]);
    }

    public function removeCoverImage(): void
    {
        if (is_string($this->coverImage) && Storage::disk('public')->exists('book_cover/' . $this->coverImage)) {
            Storage::disk('public')->delete('book_cover/' . $this->coverImage);
        }
        $this->coverImage = null;
        $this->retrievedCoverUrl = null;
        $this->resetErrorBag('coverImage');
        $this->dispatch('cover-image-removed');
    }

    public bool $showFeedback = false;

    public function submit(): void
    {
        if ($this->successMessage !== '') {
            return;
        }

        try {
            $this->save();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        }

        $this->showFeedback = $this->successMessage !== '' || $this->errorMessage !== '' || $this->getErrorBag()->any();
    }

    public function save()
    {
        if (auth()->check() && !auth()->user()->can('create', Book::class)) {
            $this->errorMessage = 'Unauthorized action. You do not have permission to add catalog books.';
            return;
        }

        $this->errorMessage = '';

        // Check if author is provided (Required)
        $hasAuthorId = !empty($this->authorId);
        $hasAuthorSearch = !empty(trim($this->authorSearch));
        $hasNewNames = !empty(trim($this->newAuthorLastName)) || !empty(trim($this->newAuthorFirstName));


        // Validation
        $isBook = $this->catalogKind === 'book';
        $isPeriodical = in_array($this->catalogKind, ['magazine', 'journal'], true);
        $isEquipment = $this->catalogKind === 'equipment';

        $this->validate([
            'accessionNumber' => 'required|string|max:50|unique:books,accession_number',
            'barcode' => 'nullable|string|max:50|unique:books,code',
            'status' => 'required|in:Available,Maintenance,Reserved',
            'location' => 'nullable|string|max:100',
            'dateAcquired' => 'nullable|date',

            'bookTitle' => 'required|string|max:255',
            'catalogKind' => 'required|in:book,magazine,journal,equipment',
            'dailyFine' => 'nullable|numeric|min:0|max:999999.99',
            'periodicalTitleId' => ($isPeriodical ? '' : 'exclude|').'nullable|integer|exists:book_datas,id',
            'issueVolume' => ($isPeriodical ? '' : 'exclude|').'nullable|string|max:50',
            'issueNumber' => ($isPeriodical ? 'required_without_all:issueVolume,issueLabel,issueDate' : 'exclude|nullable').'|string|max:50',
            'issueLabel' => ($isPeriodical ? '' : 'exclude|').'nullable|string|max:255',
            'issueDate' => ($isPeriodical ? '' : 'exclude|').'nullable|date',
            'issueBorrowable' => ($isPeriodical ? '' : 'exclude|').'boolean',
            'issueLoanDays' => ($isPeriodical ? '' : 'exclude|').'nullable|integer|min:1|max:3650',
            'manufacturer' => ($isEquipment ? '' : 'exclude|').'nullable|string|max:255',
            'modelNumber' => ($isEquipment ? '' : 'exclude|').'nullable|string|max:255',
            'packageContents' => ($isEquipment ? '' : 'exclude|').'nullable|string|max:3000',
            'subtitle' => 'nullable|string|max:255',
            'authorId' => ($isBook ? '' : 'exclude|').'nullable|exists:authors,id',
            'authorSearch' => ($isBook ? '' : 'exclude|').'nullable|string|max:200',
            'newAuthorLastName' => ($isBook ? '' : 'exclude|').'nullable|string|max:100',
            'newAuthorFirstName' => ($isBook ? '' : 'exclude|').'nullable|string|max:100',

            'isbn' => ($isBook ? '' : 'exclude|').'nullable|string|max:15',
            'issn' => [($isPeriodical ? 'nullable' : 'exclude'), 'nullable', 'regex:/^\d{4}-?\d{3}[\dXx]$/'],
            'resourceUrl' => ['nullable', 'url:http,https', 'max:255'],
            'callNumber' => ($isBook ? 'required' : ($isEquipment ? 'exclude|nullable' : 'nullable')).'|string|max:30',
            'classification' => ($isEquipment ? 'exclude|' : '').'nullable|string|max:30',
            'publisherName' => ($isEquipment ? 'exclude|' : '').'nullable|string|max:150',
            'publicationYear' => ($isEquipment ? 'exclude|' : '').'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'edition' => ($isEquipment ? 'exclude|' : '').'nullable|string|max:30',
            'pages' => ($isEquipment ? 'exclude|' : '').'nullable|integer|min:1|max:50000',

            'selectedCategories' => ($isEquipment ? 'exclude|' : '').'nullable|array',
            'selectedCategories.*' => ($isEquipment ? 'exclude|' : '').'exists:categories,id',
            'selectedLanguages' => ($isEquipment ? 'exclude|' : '').'nullable|array',
            'language' => ($isEquipment ? 'exclude|' : '').'nullable|string|max:150',
            'copyrightYear' => ($isEquipment ? 'exclude|' : '').'nullable|integer|min:1000|max:' . (date('Y') + 1),

            'bookDescription' => 'nullable|string|max:3000',
            'notes' => 'nullable|string|max:1000',
        ], [
            'accessionNumber.required' => 'The Accession Number is required.',
            'accessionNumber.unique' => 'This Accession Number is already in use.',
            'bookTitle.required' => 'The title or item name is required.',
            'callNumber.required' => 'The Call Number is required.',
            'issueNumber.required_without_all' => 'Enter a volume, issue number, issue label, or publication date.',
            'issn.regex' => 'Enter an ISSN in the format 1234-567X.',
        ]);

        if ($isBook && !$hasAuthorId && !$hasAuthorSearch && !$hasNewNames) {
            throw \Illuminate\Validation\ValidationException::withMessages(['authorSearch' => 'The Author field is required.']);
        }
        if ($isPeriodical && $this->periodicalTitleId && ! BookData::whereHas('bookDetails', fn ($q) => $q->where('catalog_kind', $this->catalogKind))->whereKey($this->periodicalTitleId)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['periodicalTitleId' => 'Select a valid title for this item type.']);
        }

        try {
            \App\Services\AuditTrail::transaction('Catalog entry created', function () {
                // 1. Resolve or Create Languages
                $languageNames = $this->catalogKind === 'equipment' ? ['Unknown'] : (!empty($this->selectedLanguages)
                    ? array_unique(array_filter(array_map('trim', $this->selectedLanguages)))
                    : [trim($this->language) ?: 'English']);

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
                $existingPeriodicalTitle = in_array($this->catalogKind, ['magazine', 'journal'], true) && $this->periodicalTitleId;
                $bookData = $existingPeriodicalTitle
                    ? BookData::whereHas('bookDetails', fn ($q) => $q->where('catalog_kind', $this->catalogKind))->lockForUpdate()->findOrFail($this->periodicalTitleId)
                    : BookData::create([
                    'book_title' => trim($this->bookTitle),
                    'subtitle' => trim($this->subtitle) ?: null,
                    'description' => trim($this->bookDescription) ?: null,
                    'note' => trim($this->notes) ?: null,
                    'language_id' => $primaryLanguage->id,
                    'copyright_year' => $this->catalogKind === 'equipment' ? null : ($this->copyrightYear ?: $this->publicationYear),
                ]);

                // 3. Resolve Authors
                $authorIds = [];
                if ($this->catalogKind === 'book' && $this->authorId) {
                    $authorIds[] = $this->authorId;
                } elseif ($this->catalogKind === 'book') {
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

                if (!$existingPeriodicalTitle && !empty($authorIds)) {
                    \App\Services\AuditTrail::syncRelationship($bookData, 'authors', array_unique($authorIds));
                }

                // 4. Sync Categories
                if ($this->catalogKind !== 'equipment' && !$existingPeriodicalTitle && !empty($this->selectedCategories)) {
                    \App\Services\AuditTrail::syncRelationship($bookData, 'categories', $this->selectedCategories);
                }

                // 5. Resolve Publisher
                $pub = null;
                if ($this->catalogKind !== 'equipment' && $this->publisherId) {
                    $pub = Publisher::find($this->publisherId);
                }
                if (!$pub) {
                    $pubName = $this->catalogKind === 'equipment' ? '' : trim($this->publisherName);
                    if (empty($pubName)) {
                        $pub = Publisher::firstOrCreate(['name' => $this->catalogKind === 'equipment' ? 'Unknown' : 'Independent / Unknown']);
                    } else {
                        $pub = Publisher::firstOrCreate(['name' => $pubName]);
                    }
                }

                if ($existingPeriodicalTitle && ($this->issueVolume !== '' || $this->issueNumber !== '' || $this->issueDate !== '' || $this->issueLabel !== '')) {
                    $duplicate = BookDetail::where('book_data_id', $bookData->id)->where('catalog_kind', $this->catalogKind)
                        ->whereHas('periodicalIssue', fn ($q) => $q->where('volume', trim($this->issueVolume) ?: null)
                            ->where('issue_number', trim($this->issueNumber) ?: null)
                            ->where('issue_label', trim($this->issueLabel) ?: null)
                            ->where('publication_date', $this->issueDate ?: null))->exists();
                    if ($duplicate) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['issueNumber' => 'This issue already exists for the selected title. Add another copy to that issue instead.']);
                    }
                }

                // 6. Convert & Store Book Cover (only upon confirmed save)
                $coverPath = null;
                if ($this->coverImage) {
                    $filename = 'cover_' . uniqid() . '_' . time() . '.webp';

                    try {
                        $image = null;
                        if (is_object($this->coverImage) && $this->coverImage instanceof \Illuminate\Http\UploadedFile) {
                            $image = Image::fromUpload($this->coverImage);
                        } elseif (is_object($this->coverImage) && method_exists($this->coverImage, 'get')) {
                            $image = Image::fromBytes($this->coverImage->get());
                        } elseif (is_object($this->coverImage) && method_exists($this->coverImage, 'getRealPath') && file_exists($this->coverImage->getRealPath())) {
                            $image = Image::fromPath($this->coverImage->getRealPath());
                        } elseif (is_string($this->coverImage)) {
                            if (Storage::disk('local')->exists($this->coverImage)) {
                                $image = Image::fromStorage($this->coverImage, 'local');
                            } elseif (Storage::disk('public')->exists($this->coverImage)) {
                                $image = Image::fromStorage($this->coverImage, 'public');
                            } elseif (file_exists($this->coverImage)) {
                                $image = Image::fromPath($this->coverImage);
                            } elseif (Storage::disk('public')->exists('book_cover/' . $this->coverImage)) {
                                $coverPath = basename($this->coverImage);
                            }
                        }

                        if ($image) {
                            $image = $image->toWebp()->quality(85);
                            $stored = $image->storeAs('book_cover', $filename, 'public');
                            if ($stored) {
                                $coverPath = $filename;
                                Log::info('Book cover converted to WebP and stored on save', ['path' => $filename]);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Image facade WebP conversion fallback on save: ' . $e->getMessage());
                        try {
                            if (is_object($this->coverImage) && method_exists($this->coverImage, 'store')) {
                                $stored = $this->coverImage->store('book_cover', 'public');
                                $coverPath = $stored ? basename($stored) : null;
                            }
                        } catch (\Throwable $fallbackErr) {
                            Log::error('Image upload fallback error on save: ' . $fallbackErr->getMessage());
                        }
                    }
                }

                // 7. Resolve default Book Type
                $defaultBookType = in_array($this->catalogKind, ['magazine', 'journal'], true)
                    ? BookType::firstOrCreate(['type' => ucfirst($this->catalogKind)])
                    : (BookType::first() ?? BookType::firstOrCreate(['type' => 'Book']));
                $bookTypeId = $defaultBookType->id;

                // 8. Create BookDetail
                $bookDetail = BookDetail::create([
                    'book_data_id' => $bookData->id,
                    'catalog_kind' => $this->catalogKind,
                    'daily_fine' => $this->dailyFine === '' ? null : $this->dailyFine,
                    'manufacturer' => $this->catalogKind === 'equipment' ? (trim($this->manufacturer) ?: null) : null,
                    'model_number' => $this->catalogKind === 'equipment' ? (trim($this->modelNumber) ?: null) : null,
                    'package_contents' => $this->catalogKind === 'equipment' ? (trim($this->packageContents) ?: null) : null,
                    'isbn' => $this->catalogKind === 'book' ? (trim($this->isbn) ?: null) : null,
                    'issn' => in_array($this->catalogKind, ['magazine', 'journal'], true) ? (trim($this->issn) ?: null) : null,
                    'url' => trim($this->resourceUrl) ?: null,
                    'publication_year' => $this->catalogKind === 'equipment' ? null : $this->publicationYear,
                    'copyright_year' => $this->catalogKind === 'equipment' ? null : ($this->copyrightYear ?: $this->publicationYear),
                    'edition' => $this->catalogKind === 'equipment' ? null : (trim($this->edition) ?: null),
                    'pages' => $this->catalogKind === 'equipment' ? null : $this->pages,
                    'format' => $this->catalogKind === 'book' ? 'Paperback' : ucfirst($this->catalogKind),
                    'book_type_id' => $bookTypeId,
                    'call_number' => $this->catalogKind === 'equipment' ? '' : trim($this->callNumber),
                    'classification' => $this->catalogKind === 'equipment' ? null : (trim($this->classification) ?: null),
                    'publisher_id' => $pub->id,
                    'cover_image' => $coverPath,
                ]);

                if (in_array($this->catalogKind, ['magazine', 'journal'], true)) {
                    $bookDetail->periodicalIssue()->updateOrCreate([], [
                        'volume' => trim($this->issueVolume) ?: null,
                        'issue_number' => trim($this->issueNumber) ?: null,
                        'issue_label' => trim($this->issueLabel) ?: null,
                        'publication_date' => $this->issueDate ?: null,
                        'borrowable' => $this->issueBorrowable,
                        'loan_days' => $this->issueLoanDays ?: null,
                    ]);
                }

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
                    \App\Services\AuditTrail::syncRelationship($book, 'languages', array_unique($languageIds));
                }
            });

            $this->successMessage = 'Catalog item "' . $this->bookTitle . '" registered successfully.';

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = 'Please review and fix the highlighted fields before submitting.';
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

        } catch (\Throwable $e) {
            Log::error('Failed to register book: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->errorMessage = 'The catalog item could not be saved. Please try again or contact the system administrator.';
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
        $authors = Author::orderBy('first_name')->get();
        $categories = Category::orderBy('name')->get();
        $languages = Language::orderBy('lang')->get();
        $conditions = BookCondition::all();

        $periodicalTitles = in_array($this->catalogKind, ['magazine', 'journal'], true)
            ? BookData::whereHas('bookDetails', fn ($q) => $q->where('catalog_kind', $this->catalogKind))->orderBy('book_title')->limit(200)->get(['id', 'book_title'])
            : collect();

        return view('livewire.forms.add-book-form', [
            'periodicalTitles' => $periodicalTitles,
            'authors' => $authors,
            'categories' => $categories,
            'languages' => $languages,
            'conditions' => $conditions,
        ]);
    }
}
