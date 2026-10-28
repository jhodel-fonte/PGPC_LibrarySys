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
    use WithFileUploads;

    // Tabs: 'manual' (Manual Form Entry) or 'isbn' (Auto-Fill via ISBN)
    public string $activeTab = 'manual';
    public string $isbnLookupQuery = '';
    public bool $isLookingUpIsbn = false;
    public ?string $isbnLookupError = null;
    public ?string $isbnLookupSuccess = null;
    public ?string $retrievedCoverUrl = null;
    public ?array $retrievedBookSummary = null;

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
        if (auth()->check() && !auth()->user()->can('create', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to add catalog books.');
        }

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

    /**
     * Detects a matching catalog Category ID based on LC or Dewey Decimal classification / call number.
     */
    public function detectCategoryFromClassification(?string $input): ?int
    {
        if (empty($input)) {
            return null;
        }

        $clean = strtoupper(trim((string) $input));

        // 1. Library of Congress (LC) letter prefixes: e.g. "QA76.73", "PZ7.D153", "PR 6054", "QA", "PZ", "TK"
        if (preg_match('/^([A-Z]{1,3})/', $clean, $matches)) {
            $code = $matches[1];
            $cat = Category::where('code', $code)->first();
            if ($cat) {
                return $cat->id;
            }

            // Fallback: If 2 or 3 letters (e.g. PZ -> P, QA -> Q), try 1st letter broad category
            if (strlen($code) > 1) {
                $broadCat = Category::where('code', substr($code, 0, 1))->first();
                if ($broadCat) {
                    return $broadCat->id;
                }
            }
        }

        // 2. Dewey Decimal Classification (DDC) numbers: e.g. "823/.914", "823.914", "005.1", "510", "658"
        if (preg_match('/\b(\d{3})(?:[\.\/]\d+)?\b/', $clean, $dMatches)) {
            $dewey = (int) $dMatches[1];
            $targetCode = match(true) {
                $dewey >= 0 && $dewey < 100 => 'QA',    // Computer Science & Information -> QA
                $dewey >= 100 && $dewey < 200 => 'BF',  // Philosophy & Psychology -> BF
                $dewey >= 200 && $dewey < 300 => 'BL',  // Religion -> BL
                $dewey >= 300 && $dewey < 400 => 'H',   // Social Sciences -> H
                $dewey >= 400 && $dewey < 500 => 'P',   // Language -> P
                $dewey >= 500 && $dewey < 600 => 'Q',   // Science -> Q
                $dewey >= 600 && $dewey < 700 => 'T',   // Technology -> T
                $dewey >= 700 && $dewey < 800 => 'N',   // Arts -> N
                $dewey >= 820 && $dewey < 830 => 'PR',  // English Literature -> PR
                $dewey >= 810 && $dewey < 820 => 'PS',  // American Literature -> PS
                $dewey >= 800 && $dewey < 900 => 'PZ',  // Literature / Fiction & Juvenile -> PZ
                $dewey >= 900 && $dewey <= 999 => 'D',  // History & Geography -> D
                default => null,
            };

            if ($targetCode) {
                $cat = Category::where('code', $targetCode)->first();
                if ($cat) {
                    return $cat->id;
                }
            }
        }

        // 3. Keyword matching against standard catalog category names
        $words = array_filter(preg_split('/[^A-Za-z0-9]+/', $clean), fn($w) => strlen($w) >= 4);
        foreach ($words as $word) {
            $cat = Category::whereNotNull('code')
                ->where('name', 'ilike', "%{$word}%")
                ->first();
            if ($cat) {
                return $cat->id;
            }
        }

        return null;
    }

    /**
     * Automatically triggers and synchronizes category selection based on call number or classification.
     */
    public function autoTriggerCategory(?string $inputSource): void
    {
        $newCategoryId = $this->detectCategoryFromClassification($inputSource);

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

            $cat = Category::find($newCategoryId);
            if ($cat) {
                $this->dispatch('category-auto-selected', id: $cat->id, code: $cat->code, raw: (string) $inputSource);
            }
        }
    }

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

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['manual', 'isbn'])) {
            $this->activeTab = $tab;
            $this->isbnLookupError = null;
        }
    }

    public function clearIsbnLookup(): void
    {
        $this->isbnLookupQuery = '';
        $this->isbnLookupError = null;
        $this->isbnLookupSuccess = null;
        $this->retrievedBookSummary = null;
    }

    public function lookupIsbn(?string $inputIsbn = null): void
    {
        $rawIsbn = $inputIsbn ?? ($this->isbnLookupQuery ?: $this->isbn);
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', strtoupper(trim((string)$rawIsbn)));

        if (empty($cleanIsbn)) {
            $this->isbnLookupError = 'Please enter an ISBN number to retrieve book details.';
            $this->isbnLookupSuccess = null;
            return;
        }

        if (strlen($cleanIsbn) < 10 || strlen($cleanIsbn) > 13) {
            $this->isbnLookupError = 'Please enter a valid 10-digit or 13-digit ISBN number.';
            $this->isbnLookupSuccess = null;
            return;
        }

        $this->isLookingUpIsbn = true;
        $this->isbnLookupError = null;
        $this->isbnLookupSuccess = null;

        try {
            // 1. Query Open Library Books API with jscmd=data
            $apiUrl = "https://openlibrary.org/api/books?bibkeys=ISBN:{$cleanIsbn}&jscmd=data&format=json";
            $response = Http::timeout(12)->get($apiUrl);

            $data = null;
            $fbJson = null;
            if ($response->successful()) {
                $json = $response->json();
                $key = "ISBN:{$cleanIsbn}";
                if (!empty($json[$key])) {
                    $data = $json[$key];
                }
            }

            // Also query /isbn/{isbn}.json for supplemental works, descriptions, and classifications
            try {
                $fallbackUrl = "https://openlibrary.org/isbn/{$cleanIsbn}.json";
                $fallbackRes = Http::timeout(8)->get($fallbackUrl);
                if ($fallbackRes->successful()) {
                    $fbJson = $fallbackRes->json();
                    if (!$data && !empty($fbJson['title'])) {
                        $data = $fbJson;
                    }
                }
            } catch (\Throwable $fbErr) {
                Log::info('Supplemental ISBN json error: ' . $fbErr->getMessage());
            }

            if (!$data) {
                $this->isbnLookupError = "No catalog record found on Open Library for ISBN {$cleanIsbn}. You can still enter details manually.";
                $this->isLookingUpIsbn = false;
                $this->dispatch('toast', message: 'No book found for ISBN: ' . $cleanIsbn, type: 'warning');
                return;
            }

            // Populate ISBN
            $this->isbn = $cleanIsbn;
            $this->isbnLookupQuery = $cleanIsbn;

            // Title & Subtitle
            if (!empty($data['title'])) {
                $this->bookTitle = (string) $data['title'];
            }
            if (!empty($data['subtitle'])) {
                $this->subtitle = (string) $data['subtitle'];
            }

            // Authors
            $authorNames = [];
            if (!empty($data['authors']) && is_array($data['authors'])) {
                foreach ($data['authors'] as $auth) {
                    $name = is_array($auth) ? ($auth['name'] ?? '') : '';
                    if (!empty($name)) {
                        $authorNames[] = trim($name);
                    }
                }
            }

            if (!empty($authorNames)) {
                $primaryAuthor = $authorNames[0];
                $existingAuthor = Author::whereRaw("LOWER(CONCAT(first_name, ' ', last_name)) = ?", [strtolower($primaryAuthor)])
                    ->orWhereRaw("LOWER(CONCAT(last_name, ' ', first_name)) = ?", [strtolower($primaryAuthor)])
                    ->orWhere('first_name', 'ilike', "%{$primaryAuthor}%")
                    ->orWhere('last_name', 'ilike', "%{$primaryAuthor}%")
                    ->first();

                if ($existingAuthor) {
                    $this->selectAuthor($existingAuthor->id, trim($existingAuthor->first_name . ' ' . $existingAuthor->last_name));
                } else {
                    $parts = preg_split('/\s+/', $primaryAuthor);
                    if (count($parts) > 1) {
                        $this->newAuthorLastName = array_pop($parts);
                        $this->newAuthorFirstName = implode(' ', $parts);
                    } else {
                        $this->newAuthorFirstName = $primaryAuthor;
                        $this->newAuthorLastName = '';
                    }
                    $this->authorId = null;
                    $this->authorSearch = $primaryAuthor;
                    $this->selectedAuthorName = $primaryAuthor;
                    $this->showManualAuthor = true;
                }
            }

            // Publisher
            $pubName = '';
            if (!empty($data['publishers']) && is_array($data['publishers'])) {
                $firstPub = $data['publishers'][0];
                $pubName = is_array($firstPub) ? ($firstPub['name'] ?? '') : (string) $firstPub;
            } elseif (!empty($fbJson['publishers']) && is_array($fbJson['publishers'])) {
                $firstPub = $fbJson['publishers'][0];
                $pubName = is_array($firstPub) ? ($firstPub['name'] ?? '') : (string) $firstPub;
            }

            if (!empty($pubName)) {
                $pubName = trim($pubName);
                $existingPub = Publisher::where('name', 'ilike', $pubName)->first();
                if ($existingPub) {
                    $this->selectPublisher($existingPub->id, $existingPub->name);
                } else {
                    $this->selectPublisher(null, $pubName);
                }
            }

            // Publication & Copyright Year
            $publishDate = $data['publish_date'] ?? ($data['copyright_date'] ?? ($fbJson['publish_date'] ?? null));
            if (!empty($publishDate) && preg_match('/\b(19\d\d|20\d\d)\b/', (string) $publishDate, $m)) {
                $year = (int) $m[1];
                $this->publicationYear = $year;
                $this->copyrightYear = $year;
            }

            // Number of Pages
            if (!empty($data['number_of_pages'])) {
                $this->pages = (int) $data['number_of_pages'];
            } elseif (!empty($data['pagination']) && preg_match('/\d+/', (string) $data['pagination'], $pm)) {
                $this->pages = (int) $pm[0];
            } elseif (!empty($fbJson['number_of_pages'])) {
                $this->pages = (int) $fbJson['number_of_pages'];
            }

            // Edition
            if (!empty($data['edition_name'])) {
                $this->edition = (string) $data['edition_name'];
            } elseif (!empty($data['edition'])) {
                $this->edition = (string) $data['edition'];
            } elseif (!empty($fbJson['edition_name'])) {
                $this->edition = (string) $fbJson['edition_name'];
            }

            // Categories / Subjects
            $subjects = [];
            if (!empty($data['subjects']) && is_array($data['subjects'])) {
                foreach (array_slice($data['subjects'], 0, 5) as $subj) {
                    $sName = is_array($subj) ? ($subj['name'] ?? '') : (string) $subj;
                    if (!empty($sName) && strlen($sName) <= 50) {
                        $subjects[] = trim($sName);
                    }
                }
            }
            foreach ($subjects as $sName) {
                $this->createAndSelectCategory($sName);
            }

            // Classifications & Call Number (Library of Congress & Dewey Decimal)
            $lc = $data['classifications']['lc_classifications'] ?? ($data['lc_classifications'] ?? ($fbJson['lc_classifications'] ?? null));
            $ddc = $data['classifications']['dewey_decimal_class'] ?? ($data['dewey_decimal_class'] ?? ($fbJson['dewey_decimal_class'] ?? null));

            // Prioritize LC classification if available, else DDC
            if (!empty($lc) && is_array($lc) && !empty($lc[0])) {
                $this->classification = (string) $lc[0];
            } elseif (!empty($ddc) && is_array($ddc) && !empty($ddc[0])) {
                $this->classification = (string) $ddc[0];
            }

            if (!empty($this->classification)) {
                $authorInit = !empty($authorNames[0]) ? strtoupper(substr($authorNames[0], 0, 3)) : 'LIB';
                $this->callNumber = $this->classification . ' ' . $authorInit . ' ' . ($this->publicationYear ?: date('Y'));
            }

            // Auto-trigger category detection based on classification, call number, or retrieved subjects
            if (!empty($this->classification) || !empty($this->callNumber)) {
                $this->autoTriggerCategory($this->classification ?: $this->callNumber);
            }

            if (empty($this->autoDetectedCategoryId) && !empty($subjects)) {
                foreach ($subjects as $sName) {
                    $matchedId = $this->detectCategoryFromClassification($sName);
                    if ($matchedId) {
                        $this->autoTriggerCategory($sName);
                        break;
                    }
                }
            }

            // Description / Notes (Resolves from $data, /isbn/{isbn}.json, and /works/{workKey}.json)
            $desc = $data['description'] ?? null;
            if (is_array($desc) && isset($desc['value'])) {
                $desc = $desc['value'];
            }

            // Fallback 1: check /isbn/{isbn}.json
            if (empty($desc)) {
                $isbnJson = $fbJson;
                if (!$isbnJson) {
                    try {
                        $iRes = Http::timeout(8)->get("https://openlibrary.org/isbn/{$cleanIsbn}.json");
                        if ($iRes->successful()) {
                            $isbnJson = $iRes->json();
                        }
                    } catch (\Throwable $iErr) {
                        Log::info('ISBN json fetch error: ' . $iErr->getMessage());
                    }
                }

                if (!empty($isbnJson['description'])) {
                    $d = $isbnJson['description'];
                    $desc = is_array($d) ? ($d['value'] ?? '') : (string) $d;
                }

                // Fallback 2: check /works/{work_key}.json
                if (empty($desc)) {
                    $workKey = $data['works'][0]['key'] ?? ($isbnJson['works'][0]['key'] ?? null);
                    if ($workKey) {
                        try {
                            $workRes = Http::timeout(8)->get("https://openlibrary.org{$workKey}.json");
                            if ($workRes->successful()) {
                                $workData = $workRes->json();
                                $wDesc = $workData['description'] ?? null;
                                if (is_array($wDesc) && isset($wDesc['value'])) {
                                    $desc = $wDesc['value'];
                                } elseif (!empty($wDesc) && is_string($wDesc)) {
                                    $desc = $wDesc;
                                }
                            }
                        } catch (\Throwable $wErr) {
                            Log::info('Work json fetch error: ' . $wErr->getMessage());
                        }
                    }
                }
            }

            if (!empty($desc) && is_string($desc)) {
                $this->bookDescription = mb_substr(trim($desc), 0, 3000);
            }

            if (!empty($data['notes'])) {
                $notes = is_array($data['notes']) ? ($data['notes']['value'] ?? '') : (string) $data['notes'];
                if (!empty($notes)) {
                    $this->notes = mb_substr(trim($notes), 0, 1000);
                }
            }

            // Retrieve & Save Cover Image if Available
            $coverUrl = null;
            if (!empty($data['cover']['large'])) {
                $coverUrl = $data['cover']['large'];
            } elseif (!empty($data['cover']['medium'])) {
                $coverUrl = $data['cover']['medium'];
            } elseif (!empty($data['cover']['small'])) {
                $coverUrl = $data['cover']['small'];
            } else {
                $directCoverUrl = "https://covers.openlibrary.org/b/isbn/{$cleanIsbn}-L.jpg?default=false";
                $checkCover = Http::timeout(6)->get($directCoverUrl);
                if ($checkCover->successful() && strlen($checkCover->body()) > 200) {
                    $coverUrl = $directCoverUrl;
                }
            }

            $coverSaved = false;
            if (!empty($coverUrl)) {
                try {
                    $imgRes = Http::timeout(10)->get($coverUrl);
                    if ($imgRes->successful()) {
                        $bytes = $imgRes->body();
                        $info = @getimagesizefromstring($bytes);
                        if ($info && $info[0] > 20 && $info[1] > 20) {
                            $filename = 'cover_openlib_' . uniqid() . '_' . time() . '.webp';
                            try {
                                $image = Image::fromBytes($bytes)->toWebp()->quality(85);
                                $stored = $image->storeAs('book_cover', $filename, 'public');
                                if ($stored) {
                                    $this->coverImage = $filename;
                                    $this->retrievedCoverUrl = asset('storage/book_cover/' . $filename);
                                    $coverSaved = true;
                                }
                            } catch (\Throwable $imgErr) {
                                Storage::disk('public')->put('book_cover/' . $filename, $bytes);
                                $this->coverImage = $filename;
                                $this->retrievedCoverUrl = asset('storage/book_cover/' . $filename);
                                $coverSaved = true;
                            }

                            if ($coverSaved && $this->retrievedCoverUrl) {
                                $this->dispatch('cover-image-retrieved', url: $this->retrievedCoverUrl);
                            }
                        }
                    }
                } catch (\Throwable $coverErr) {
                    Log::warning('Failed downloading OpenLibrary cover: ' . $coverErr->getMessage());
                }
            }

            // Summary for UI Banner
            $this->retrievedBookSummary = [
                'title' => $this->bookTitle,
                'author' => !empty($authorNames) ? implode(', ', $authorNames) : 'N/A',
                'publisher' => $pubName ?: 'N/A',
                'year' => $this->publicationYear ?: 'N/A',
                'pages' => $this->pages ?: 'N/A',
                'isbn' => $cleanIsbn,
                'cover_saved' => $coverSaved,
                'cover_url' => $this->retrievedCoverUrl,
            ];

            $this->isbnLookupSuccess = "Successfully retrieved details and " . ($coverSaved ? "cover image" : "metadata") . " for '{$this->bookTitle}'!";
            $this->isLookingUpIsbn = false;
            $this->dispatch('toast', message: 'Book details auto-filled from Open Library!', type: 'success');

        } catch (\Throwable $e) {
            Log::error('OpenLibrary ISBN lookup error: ' . $e->getMessage(), ['exception' => $e]);
            $this->isbnLookupError = 'Unable to connect to Open Library right now. You can verify your connection or catalog the book details manually below.';
            $this->isLookingUpIsbn = false;
            $this->dispatch('toast', message: 'Error connecting to Open Library', type: 'error');
        }
    }

    public function save()
    {
        if (auth()->check() && !auth()->user()->can('create', Book::class)) {
            $this->errorMessage = 'Unauthorized action. You do not have permission to add catalog books.';
            $this->dispatch('toast', message: $this->errorMessage, type: 'error');
            return;
        }

        $this->errorMessage = '';

        // Check if author is provided (Required)
        $hasAuthorId = !empty($this->authorId);
        $hasAuthorSearch = !empty(trim($this->authorSearch));
        $hasNewNames = !empty(trim($this->newAuthorLastName)) || !empty(trim($this->newAuthorFirstName));

        if (!$hasAuthorId && !$hasAuthorSearch && !$hasNewNames) {
            $this->addError('authorSearch', 'The Author field is required.');
        }

        // Validation
        $maxSetting = config('settings.book_covers.max_size', config('settings.coverFile_max_size', '5MB'));
        $maxKb = $this->parseSizeToKilobytes($maxSetting);

        $this->validate([
            'accessionNumber' => 'required|string|max:50|unique:books,accession_number',
            'barcode' => 'nullable|string|max:50|unique:books,code',
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
        ], [
            'accessionNumber.required' => 'The Accession Number is required.',
            'accessionNumber.unique' => 'This Accession Number is already in use.',
            'bookTitle.required' => 'The Book Title is required.',
            'callNumber.required' => 'The Call Number is required.',
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
            $prefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
            return $this->redirect(route($prefix . 'book-management.index'), navigate: true);

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

        return view('livewire.forms.add-book-form', [
            'authors' => $authors,
            'categories' => $categories,
            'languages' => $languages,
            'conditions' => $conditions,
        ]);
    }
}
