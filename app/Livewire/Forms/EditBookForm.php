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
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditBookForm extends Component
{
    use WithFileUploads;

    public int $bookDetailId;
    public ?int $bookId = null;

    // 1. Physical Copy
    public string $accessionNumber = '';
    public string $barcode = '';
    public string $status = 'Available';
    public string $location = '';
    public string $dateAcquired = '';

    // 2. Book Cover
    public $coverImage = null;          // Livewire temporary upload object
    public ?string $existingCoverUrl = null;
    public ?string $existingCoverImage = null;
    public bool $removeExistingCover = false;

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

    public ?int $autoDetectedCategoryId = null;

    public function mount($id)
    {
        // Find BookDetail or Book
        $bookDetail = BookDetail::with([
            'bookData.authors',
            'bookData.categories',
            'bookData.language',
            'publisher',
            'books.languages',
            'books.condition'
        ])->find($id);

        if (!$bookDetail) {
            $book = Book::with([
                'bookDetail.bookData.authors',
                'bookDetail.bookData.categories',
                'bookDetail.bookData.language',
                'bookDetail.publisher',
                'languages',
                'condition'
            ])->find($id);

            if ($book) {
                $bookDetail = $book->bookDetail;
                $this->bookId = $book->id;
            }
        }

        if (!$bookDetail) {
            session()->flash('errorMessage', 'Book record not found.');
            return redirect()->route('admin.book-management.index');
        }

        $this->bookDetailId = $bookDetail->id;
        $bookData = $bookDetail->bookData;

        // 1. Physical Copy Data
        $primaryBook = $this->bookId ? Book::find($this->bookId) : $bookDetail->books->first();
        if ($primaryBook) {
            $this->bookId = $primaryBook->id;
            $this->accessionNumber = $primaryBook->accession_number ?? '';
            $this->barcode = $primaryBook->code ?? '';
            $this->status = ucfirst($primaryBook->status ?? 'Available');
            $this->location = $primaryBook->location ?? '';
            $this->dateAcquired = $primaryBook->date_acquired ? Carbon::parse($primaryBook->date_acquired)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

            $bookLangs = $primaryBook->languages->pluck('lang')->toArray();
            if (!empty($bookLangs)) {
                $this->selectedLanguages = $bookLangs;
            }
        } else {
            $this->dateAcquired = Carbon::now()->format('Y-m-d');
        }

        // 2. Languages fallback
        if (empty($this->selectedLanguages) && $bookData && $bookData->language) {
            $this->selectedLanguages = [$bookData->language->lang];
        }
        if (empty($this->selectedLanguages)) {
            $this->selectedLanguages = ['English'];
        }
        $this->language = implode(', ', $this->selectedLanguages);

        // 3. Book Cover
        $this->existingCoverImage = $bookDetail->cover_image;
        $this->existingCoverUrl = $bookDetail->cover_url;

        // 4. Bibliographic Data
        if ($bookData) {
            $this->bookTitle = $bookData->book_title ?? '';
            $this->subtitle = $bookData->subtitle ?? '';
            $this->bookDescription = $bookData->description ?? '';
            $this->notes = $bookData->note ?? '';
            $this->copyrightYear = $bookData->copyright_year;

            // Authors
            $primaryAuthor = $bookData->authors->first();
            if ($primaryAuthor) {
                $this->authorId = $primaryAuthor->id;
                $this->authorSearch = trim($primaryAuthor->first_name . ' ' . $primaryAuthor->last_name);
                $this->selectedAuthorName = $this->authorSearch;
            }

            // Categories
            $this->selectedCategories = $bookData->categories->pluck('id')->toArray();
        }

        // 5. Identifiers & Publication
        $this->isbn = $bookDetail->isbn ?? '';
        $this->issn = $bookDetail->issn ?? '';
        $this->callNumber = $bookDetail->call_number ?? '';
        $this->classification = $bookDetail->classification ?? '';
        $this->publicationYear = $bookDetail->publication_year;
        if (!$this->copyrightYear) {
            $this->copyrightYear = $bookDetail->copyright_year;
        }
        $this->edition = $bookDetail->edition ?? '';
        $this->pages = $bookDetail->pages;

        // 6. Publisher
        if ($bookDetail->publisher) {
            $this->publisherId = $bookDetail->publisher->id;
            $this->publisherName = $bookDetail->publisher->name;
            $this->selectedPublisherName = $bookDetail->publisher->name;
        }
    }

    public function generateBarcode(): void
    {
        $this->barcode = 'BK-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $this->dispatch('toast', message: 'Barcode generated: ' . $this->barcode, type: 'info');
    }

    public function selectAuthor(int $id, string $name): void
    {
        $this->authorId = $id;
        $this->selectedAuthorName = $name;
        $this->authorSearch = $name;
        $this->newAuthorLastName = '';
        $this->newAuthorFirstName = '';
        $this->resetErrorBag('authorSearch');
    }

    public function clearAuthor(): void
    {
        $this->authorId = null;
        $this->selectedAuthorName = '';
        $this->authorSearch = '';
        $this->newAuthorLastName = '';
        $this->newAuthorFirstName = '';
    }

    public function toggleManualAuthor(): void
    {
        $this->showManualAuthor = !$this->showManualAuthor;
    }

    #[On('publisher-selected')]
    public function selectPublisher(?int $id = null, string $name = ''): void
    {
        $this->publisherId = $id;
        $this->selectedPublisherName = $name;
        $this->publisherName = $name;
        $this->resetErrorBag('publisherName');
    }

    #[On('publisher-cleared')]
    public function clearPublisher(): void
    {
        $this->publisherId = null;
        $this->selectedPublisherName = '';
        $this->publisherName = '';
    }

    public function updatedSelectedLanguages(): void
    {
        $this->language = implode(', ', $this->selectedLanguages);
    }

    public function updatedCallNumber($value): void
    {
        $newCategoryId = null;
        if (preg_match('/^([a-zA-Z]{1,3})\s*\d+/', trim((string) $value), $matches)) {
            $code = strtoupper($matches[1]);
            $category = Category::where('code', $code)->first();
            if ($category) {
                $newCategoryId = $category->id;
            }
        }

        if ($this->autoDetectedCategoryId && (int)$this->autoDetectedCategoryId !== (int)$newCategoryId) {
            $this->selectedCategories = array_values(array_filter(
                $this->selectedCategories,
                fn($id) => (int)$id !== (int)$this->autoDetectedCategoryId
            ));
            $this->autoDetectedCategoryId = null;
        }

        if ($newCategoryId && !in_array($newCategoryId, $this->selectedCategories)) {
            $this->selectedCategories[] = $newCategoryId;
            $this->autoDetectedCategoryId = $newCategoryId;
        }
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
        $maxSetting = config('settings.coverFile_max_size', '5MB');
        $maxKb = $this->parseSizeToKilobytes($maxSetting);

        $this->validate([
            'coverImage' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:' . $maxKb,
        ]);

        $this->removeExistingCover = false;
    }

    public function removeCoverImage(): void
    {
        $this->coverImage = null;
        $this->resetErrorBag('coverImage');
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
        $maxSetting = config('settings.coverFile_max_size', '5MB');
        $maxKb = $this->parseSizeToKilobytes($maxSetting);

        $bookCopyId = $this->bookId ?: 'NULL';

        $this->validate([
            'accessionNumber' => 'required|string|max:50|unique:books,accession_number,' . $bookCopyId . ',id',
            'barcode' => 'nullable|string|max:50|unique:books,code,' . $bookCopyId . ',id',
            'status' => 'required|string',
            'location' => 'nullable|string|max:100',
            'dateAcquired' => 'nullable|date',

            'bookTitle' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'authorId' => 'nullable|exists:authors,id',
            'authorSearch' => 'nullable|string|max:200',
            'newAuthorLastName' => 'nullable|string|max:100',
            'newAuthorFirstName' => 'nullable|string|max:100',

            'isbn' => 'nullable|string|max:15',
            'issn' => 'nullable|string|max:15',
            'callNumber' => 'required|string|max:30',
            'classification' => 'nullable|string|max:30',
            'publisherName' => 'nullable|string|max:150',
            'publicationYear' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'edition' => 'nullable|string|max:30',
            'pages' => 'nullable|integer|min:1|max:50000',

            'selectedCategories' => 'nullable|array',
            'selectedCategories.*' => 'exists:categories,id',
            'selectedLanguages' => 'nullable|array',
            'language' => 'nullable|string|max:150',
            'copyrightYear' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),

            'bookDescription' => 'nullable|string|max:3000',
            'notes' => 'nullable|string|max:1000',
            'coverImage' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:' . $maxKb,
        ], [
            'accessionNumber.required' => 'The Accession Number is required.',
            'accessionNumber.unique' => 'This Accession Number is already in use.',
            'bookTitle.required' => 'The Book Title is required.',
            'callNumber.required' => 'The Call Number is required.',
        ]);

        try {
            DB::transaction(function () {
                $bookDetail = BookDetail::with(['bookData', 'books'])->findOrFail($this->bookDetailId);
                $bookData = $bookDetail->bookData;

                // 1. Resolve Languages
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

                // 2. Update BookData
                if ($bookData) {
                    $bookData->update([
                        'book_title' => trim($this->bookTitle),
                        'subtitle' => trim($this->subtitle) ?: null,
                        'description' => trim($this->bookDescription) ?: null,
                        'note' => trim($this->notes) ?: null,
                        'language_id' => $primaryLanguage->id,
                        'copyright_year' => $this->copyrightYear ?: $this->publicationYear,
                    ]);
                } else {
                    $bookData = BookData::create([
                        'book_title' => trim($this->bookTitle),
                        'subtitle' => trim($this->subtitle) ?: null,
                        'description' => trim($this->bookDescription) ?: null,
                        'note' => trim($this->notes) ?: null,
                        'language_id' => $primaryLanguage->id,
                        'copyright_year' => $this->copyrightYear ?: $this->publicationYear,
                    ]);
                    $bookDetail->book_data_id = $bookData->id;
                }

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
                } else {
                    $bookData->categories()->detach();
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

                // 6. Handle Book Cover Update (Keep existing if no new upload)
                $oldCover = $bookDetail->cover_image;
                $coverPath = $oldCover;

                if ($this->coverImage) {
                    $filename = 'cover_' . uniqid() . '_' . time() . '.webp';
                    $newCoverStored = false;

                    try {
                        $image = null;
                        if (is_object($this->coverImage) && $this->coverImage instanceof \Illuminate\Http\UploadedFile) {
                            $image = Image::fromUpload($this->coverImage);
                        } elseif (is_object($this->coverImage) && method_exists($this->coverImage, 'get')) {
                            $image = Image::fromBytes($this->coverImage->get());
                        } elseif (is_object($this->coverImage) && method_exists($this->coverImage, 'getRealPath') && file_exists($this->coverImage->getRealPath())) {
                            $image = Image::fromPath($this->coverImage->getRealPath());
                        }

                        if ($image) {
                            $image = $image->toWebp()->quality(85);
                            $stored = $image->storeAs('book_cover', $filename, 'public');
                            if ($stored) {
                                $coverPath = $filename;
                                $newCoverStored = true;
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Cover conversion fallback in EditBookForm: ' . $e->getMessage());
                        try {
                            if (is_object($this->coverImage) && method_exists($this->coverImage, 'store')) {
                                $stored = $this->coverImage->store('book_cover', 'public');
                                if ($stored) {
                                    $coverPath = basename($stored);
                                    $newCoverStored = true;
                                }
                            }
                        } catch (\Throwable $fbErr) {
                            Log::error('Cover fallback failed: ' . $fbErr->getMessage());
                        }
                    }

                    // If a new cover was saved, delete the old cover file from disk
                    if ($newCoverStored && $oldCover && $oldCover !== $coverPath) {
                        $cleanOld = basename($oldCover);
                        if ($cleanOld && Storage::disk('public')->exists('book_cover/' . $cleanOld)) {
                            Storage::disk('public')->delete('book_cover/' . $cleanOld);
                        }
                    }
                }

                // 7. Update BookDetail
                $bookDetail->update([
                    'isbn' => trim($this->isbn) ?: null,
                    'issn' => trim($this->issn) ?: null,
                    'publication_year' => $this->publicationYear,
                    'copyright_year' => $this->copyrightYear ?: $this->publicationYear,
                    'edition' => trim($this->edition) ?: null,
                    'pages' => $this->pages,
                    'call_number' => trim($this->callNumber),
                    'classification' => trim($this->classification) ?: null,
                    'publisher_id' => $pub->id,
                    'cover_image' => $coverPath,
                ]);

                // 8. Update or Create Physical Book Copy
                $book = $this->bookId ? Book::find($this->bookId) : $bookDetail->books->first();
                if ($book) {
                    $book->update([
                        'accession_number' => trim($this->accessionNumber),
                        'code' => trim($this->barcode) ?: null,
                        'location' => trim($this->location) ?: null,
                        'status' => strtolower($this->status),
                        'date_acquired' => $this->dateAcquired ? Carbon::parse($this->dateAcquired)->toDateString() : Carbon::now()->toDateString(),
                    ]);
                } else {
                    $condition = BookCondition::where('status', 'Good')->first() ?? BookCondition::first();
                    $book = Book::create([
                        'book_detail_id' => $bookDetail->id,
                        'book_condition_id' => $condition ? $condition->id : 1,
                        'accession_number' => trim($this->accessionNumber),
                        'code' => trim($this->barcode) ?: null,
                        'location' => trim($this->location) ?: null,
                        'status' => strtolower($this->status),
                        'date_acquired' => $this->dateAcquired ? Carbon::parse($this->dateAcquired)->toDateString() : Carbon::now()->toDateString(),
                    ]);
                    $this->bookId = $book->id;
                }

                // 9. Sync Languages to Book Copy
                if (!empty($languageIds) && $book) {
                    $book->languages()->sync(array_unique($languageIds));
                }
            });

            session()->flash('successMessage', 'Book "' . $this->bookTitle . '" updated successfully.');
            $this->dispatch('toast', message: 'Book updated successfully.', type: 'success');

            return redirect()->route('admin.book-management.index');
        } catch (\Throwable $e) {
            Log::error('Edit Book Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->errorMessage = 'Failed to update book: ' . $e->getMessage();
            $this->dispatch('toast', message: 'Failed to update book. Please check the form errors.', type: 'error');
        }
    }

    private function parseSizeToKilobytes(string $sizeStr): int
    {
        $sizeStr = trim($sizeStr);
        $num = (int)$sizeStr;
        $unit = strtoupper(substr($sizeStr, -2));

        if ($unit === 'MB') {
            return $num * 1024;
        } elseif ($unit === 'KB') {
            return $num;
        } elseif (str_ends_with(strtoupper($sizeStr), 'M')) {
            return $num * 1024;
        } elseif (str_ends_with(strtoupper($sizeStr), 'K')) {
            return $num;
        }

        return 5120; // 5MB default
    }

    public function render()
    {
        return view('livewire.forms.edit-book-form');
    }
}
