<?php

namespace App\Livewire\Forms;

use App\Models\Author;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait OpenLibraryLookup
{
    public bool $isLookingUpIsbn = false;
    public ?string $isbnLookupError = null;
    public ?string $isbnLookupSuccess = null;
    public ?string $retrievedCoverUrl = null;

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

    public function lookupIsbn(?string $inputIsbn = null): void
    {
        $rawIsbn = $inputIsbn ?? $this->isbn;
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

}
