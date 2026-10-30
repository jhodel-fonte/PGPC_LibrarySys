<?php

namespace App\Services;

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
use Exception;
use File_MARC_Control_Field;
use File_MARC_Data_Field;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Scriptotek\Marc\Collection;
use Scriptotek\Marc\Record;
use Scriptotek\Marc\BibliographicRecord;

class ExtractMarcData
{
    /**
     * Cache key prefix for MARC batch records.
     */
    public const CACHE_PREFIX = 'marc_import_batch_';

    /**
     * Cache key prefix for uploaded MARC filenames.
     */
    public const FILENAME_CACHE_PREFIX = 'marc_file_name_';

    /**
     * Default cache TTL in minutes (30 minutes).
     */
    public const DEFAULT_CACHE_TTL_MINUTES = 30;

    /**
     * Cache extracted MARC records and summary for fast pagination and low Livewire state overhead.
     * Automatically purged after 30 minutes.
     *
     * @param string $key Unique import key
     * @param array $records Extracted bibliographic records
     * @param array $summary Batch file summary
     * @param int $ttlMinutes Time-to-live in minutes (defaults to 30 minutes)
     */
    public function cacheRecords(string $key, array $records, array $summary = [], int $ttlMinutes = self::DEFAULT_CACHE_TTL_MINUTES): void
    {
        $now = Carbon::now();
        $expiresAt = $now->copy()->addMinutes($ttlMinutes);

        Cache::put(self::CACHE_PREFIX . $key, [
            'records' => $records,
            'summary' => $summary,
            'cached_at' => $now->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'ttl_minutes' => $ttlMinutes,
        ], $expiresAt);
    }

    /**
     * Retrieve cached MARC batch records.
     *
     * @param string $key
     * @return array|null Returns ['records' => array, 'summary' => array]|null
     */
    public function getCachedRecords(string $key): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $key);
    }

    /**
     * Update the cached records (e.g. when excluding records from the batch).
     *
     * @param string $key
     * @param array $records
     * @param array|null $summary
     * @param int $ttlMinutes Defaults to 30 minutes
     */
    public function updateCachedRecords(string $key, array $records, ?array $summary = null, int $ttlMinutes = self::DEFAULT_CACHE_TTL_MINUTES): void
    {
        $cached = $this->getCachedRecords($key) ?? [];
        $cached['records'] = $records;
        if ($summary !== null) {
            $cached['summary'] = $summary;
        }
        $cached['updated_at'] = Carbon::now()->toIso8601String();

        $expiresAt = isset($cached['expires_at'])
            ? Carbon::parse($cached['expires_at'])
            : Carbon::now()->addMinutes($ttlMinutes);

        $ttl = Carbon::now()->lessThan($expiresAt) ? $expiresAt : Carbon::now()->addMinutes($ttlMinutes);

        Cache::put(self::CACHE_PREFIX . $key, $cached, $ttl);
    }

    /**
     * Clear cached records for the given import key.
     *
     * @param string $key
     */
    public function clearCachedRecords(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Cache the uploaded MARC filename with a 30-minute TTL to detect duplicate uploads.
     *
     * @param string $fileName
     * @param string $importKey
     * @param array $extra Metadata (size, total_records, etc.)
     * @param int $ttlMinutes Defaults to 30 minutes
     */
    public function cacheFilename(string $fileName, string $importKey, array $extra = [], int $ttlMinutes = self::DEFAULT_CACHE_TTL_MINUTES): void
    {
        $cacheKey = self::normalizeFileNameKey($fileName);
        $now = Carbon::now();
        $expiresAt = $now->copy()->addMinutes($ttlMinutes);

        Cache::put(self::FILENAME_CACHE_PREFIX . $cacheKey, [
            'filename' => basename($fileName),
            'import_key' => $importKey,
            'size' => $extra['size'] ?? '',
            'total_records' => $extra['total_records'] ?? 0,
            'uploaded_by' => auth()->user()?->name ?? 'Librarian',
            'uploaded_by_id' => auth()->id(),
            'uploaded_at' => $now->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'ttl_minutes' => $ttlMinutes,
        ], $expiresAt);
    }

    /**
     * Check if a file with this name was uploaded within the 30-minute window.
     *
     * @param string $fileName
     * @return array|null Returns metadata array or null if expired/not found
     */
    public function getCachedFileByName(string $fileName): ?array
    {
        $cacheKey = self::normalizeFileNameKey($fileName);
        $data = Cache::get(self::FILENAME_CACHE_PREFIX . $cacheKey);

        if (!$data || !is_array($data)) {
            return null;
        }

        // Verify if batch records are still available in memory cache
        $data['records_available'] = !empty($data['import_key']) && Cache::has(self::CACHE_PREFIX . $data['import_key']);

        return $data;
    }

    /**
     * Clear the filename cache entry for a given filename.
     *
     * @param string $fileName
     */
    public function clearFilenameCache(string $fileName): void
    {
        $cacheKey = self::normalizeFileNameKey($fileName);
        Cache::forget(self::FILENAME_CACHE_PREFIX . $cacheKey);
    }

    /**
     * Normalize filename into a consistent cache key.
     *
     * @param string $fileName
     * @return string
     */
    public static function normalizeFileNameKey(string $fileName): string
    {
        return md5(strtolower(trim(basename($fileName))));
    }
    /**
     * Common MARC 21 Tag Dictionary for cataloging UI inspection.
     */
    protected static array $tagDescriptions = [
        'LDR' => 'Record Leader',
        '001' => 'Control Number / Identifier',
        '003' => 'Control Number Identifier',
        '005' => 'Date and Time of Latest Transaction',
        '008' => 'Fixed-Length Data Elements',
        '020' => 'International Standard Book Number (ISBN)',
        '022' => 'International Standard Serial Number (ISSN)',
        '040' => 'Cataloging Source',
        '041' => 'Language Code',
        '043' => 'Geographic Area Code',
        '050' => 'Library of Congress Classification',
        '082' => 'Dewey Decimal Classification',
        '090' => 'Locally Assigned Call Number',
        '092' => 'Locally Assigned Dewey Number',
        '100' => 'Main Entry -- Personal Name (Primary Author)',
        '110' => 'Main Entry -- Corporate Name',
        '111' => 'Main Entry -- Meeting Name',
        '240' => 'Uniform Title',
        '245' => 'Title Statement',
        '246' => 'Varying Form of Title',
        '250' => 'Edition Statement',
        '260' => 'Publication, Distribution, etc. (Imprint)',
        '264' => 'Production, Publication, Distribution, Manufacture, or Copyright Notice',
        '300' => 'Physical Description (Extent & Dimensions)',
        '440' => 'Series Statement / Added Entry--Title',
        '490' => 'Series Statement',
        '500' => 'General Note',
        '504' => 'Bibliography, etc. Note',
        '505' => 'Formatted Contents Note (Table of Contents)',
        '520' => 'Summary, Scope, etc. Note',
        '600' => 'Subject Added Entry -- Personal Name',
        '650' => 'Subject Added Entry -- Topical Term',
        '651' => 'Subject Added Entry -- Geographic Name',
        '700' => 'Added Entry -- Personal Name (Co-Author / Contributor)',
        '710' => 'Added Entry -- Corporate Name',
        '856' => 'Electronic Location and Access (URL)',
    ];

    /**
     * Map of ISO 639-2 MARC language codes to standard language names.
     */
    protected static array $languageMap = [
        'eng' => 'English',
        'tgl' => 'Filipino',
        'fil' => 'Filipino',
        'tag' => 'Tagalog',
        'spa' => 'Spanish',
        'fre' => 'French',
        'fra' => 'French',
        'ger' => 'German',
        'deu' => 'German',
        'ita' => 'Italian',
        'por' => 'Portuguese',
        'rus' => 'Russian',
        'chi' => 'Chinese',
        'zho' => 'Chinese',
        'jpn' => 'Japanese',
        'kor' => 'Korean',
        'ara' => 'Arabic',
        'hin' => 'Hindi',
        'lat' => 'Latin',
        'und' => 'Undetermined',
    ];

    /**
     * Extract bibliographic records from a MARC file on disk.
     *
     * @param string $filePath Absolute or relative path to the .mrc / .marc / .xml file
     * @param string|null $originalName Original filename for UI summary
     * @param string|null $cacheKey Optional cache key to automatically cache extracted records
     * @return array ['success' => bool, 'summary' => array, 'records' => array, 'error' => ?string]
     */
    public function extractFromFile(string $filePath, ?string $originalName = null, ?string $cacheKey = null, int $ttlMinutes = self::DEFAULT_CACHE_TTL_MINUTES): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'summary' => [],
                'records' => [],
                'error' => "File not found at path: {$filePath}",
            ];
        }

        try {
            // Attempt to load collection using scriptotek/marc
            $collection = Collection::fromFile($filePath);
            $result = $this->processCollection($collection, $filePath, $originalName);
            if ($result['success'] && !empty($cacheKey)) {
                $this->cacheRecords($cacheKey, $result['records'], $result['summary'], $ttlMinutes);
                if (!empty($originalName)) {
                    $this->cacheFilename($originalName, $cacheKey, [
                        'size' => $result['summary']['size'] ?? '',
                        'total_records' => $result['summary']['total_parsed'] ?? count($result['records']),
                    ], $ttlMinutes);
                }
            }
            return $result;
        } catch (\Throwable $e) {
            Log::warning("ExtractMarcData::extractFromFile fallback attempt: " . $e->getMessage());

            // Fallback attempt: read file string directly
            try {
                $rawContent = file_get_contents($filePath);
                if (!empty($rawContent)) {
                    $collection = Collection::fromString($rawContent);
                    $result = $this->processCollection($collection, $filePath, $originalName);
                    if ($result['success'] && !empty($cacheKey)) {
                        $this->cacheRecords($cacheKey, $result['records'], $result['summary'], $ttlMinutes);
                        if (!empty($originalName)) {
                            $this->cacheFilename($originalName, $cacheKey, [
                                'size' => $result['summary']['size'] ?? '',
                                'total_records' => $result['summary']['total_parsed'] ?? count($result['records']),
                            ], $ttlMinutes);
                        }
                    }
                    return $result;
                }
            } catch (\Throwable $fallbackEx) {
                Log::error("ExtractMarcData::extractFromFile failed completely: " . $fallbackEx->getMessage());
            }

            return [
                'success' => false,
                'summary' => [],
                'records' => [],
                'error' => "Failed to parse MARC exchange record: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Extract bibliographic records from a raw MARC string.
     *
     * @param string $rawMarc
     * @param string $fileName
     * @return array
     */
    public function extractFromString(string $rawMarc, string $fileName = 'batch_records.mrc'): array
    {
        try {
            $collection = Collection::fromString($rawMarc);
            return $this->processCollection($collection, null, $fileName);
        } catch (\Throwable $e) {
            Log::error("ExtractMarcData::extractFromString error: " . $e->getMessage());
            return [
                'success' => false,
                'summary' => [],
                'records' => [],
                'error' => "Failed to parse MARC string: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Process a Scriptotek Collection instance into normalized UI array data.
     */
    protected function processCollection(Collection $collection, ?string $filePath = null, ?string $originalName = null): array
    {
        $records = [];
        $index = 1;
        $fileSize = $filePath && file_exists($filePath) ? filesize($filePath) : 0;

        try {
            foreach ($collection as $record) {
                try {
                    if ($record instanceof Record) {
                        $records[] = $this->parseRecord($record, $index);
                        $index++;
                    }
                } catch (\Throwable $recErr) {
                    Log::warning("ExtractMarcData::processCollection: Error parsing record at index {$index}: " . $recErr->getMessage());
                }
            }
        } catch (\Throwable $streamErr) {
            Log::warning("ExtractMarcData::processCollection: Collection iteration stopped at index {$index}: " . $streamErr->getMessage());
        }

        $readyCount = count(array_filter($records, fn($r) => ($r['status'] ?? '') === 'ready'));
        $warningCount = count(array_filter($records, fn($r) => ($r['status'] ?? '') === 'warning'));

        $summary = [
            'name' => $originalName ?: ($filePath ? basename($filePath) : 'MARC_Records_Batch.mrc'),
            'size' => $this->formatBytes($fileSize),
            'format' => 'MARC 21 Bibliographic (ISO 2709 Binary)',
            'encoding' => 'UTF-8 Unicode',
            'uploaded_at' => Carbon::now()->format('F j, Y \a\t g:i A'),
            'total_parsed' => count($records),
            'ready_count' => $readyCount,
            'warnings_count' => $warningCount,
        ];

        return [
            'success' => count($records) > 0,
            'summary' => $summary,
            'records' => $records,
            'error' => count($records) === 0 ? 'No valid bibliographic MARC records could be parsed from the provided input.' : null,
        ];
    }

    /**
     * Helper to human-format byte sizes.
     */
    protected function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int)floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), $precision) . ' ' . $units[$power];
    }

    /**
     * Parse a single MARC Record into a structured bibliographic array.
     */
    public function parseRecord(Record $record, int $index = 1): array
    {
        $wrappedRecord = $record->getRecord();
        $leader = $wrappedRecord ? $wrappedRecord->getLeader() : '';
        $controlNumber = $this->extractControlNumber($record, $index);

        // 1. Title statement (245)
        $titleInfo = $this->extractTitle($record);

        // 2. Authors (100, 700, 110, 245$c)
        $authorInfo = $this->extractAuthors($record, $titleInfo['statement_of_responsibility'] ?? null);

        // 3. ISBN & ISSN (020, 022)
        $isbn = $this->extractIsbn($record);
        $issn = $this->extractIssn($record);
        // Leader/07 identifies continuing resources; 866 is a holdings statement.
        $catalogKind = strlen($leader) > 7 && in_array(strtolower($leader[7]), ['s', 'i'], true) ? 'journal' : 'book';
        $holdingsField = $record->getField('866');
        $issueLabel = $holdingsField?->getSubfield('a')?->getData();
        $linkField = $record->getField('856');
        $resourceUrl = $linkField?->getSubfield('u')?->getData();
        $resourceUrl = is_string($resourceUrl) && filter_var($resourceUrl, FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($resourceUrl, PHP_URL_SCHEME)), ['http', 'https'], true) ? $resourceUrl : null;

        // 4. Dewey & Call Numbers (082, 050, 090)
        $dewey = $this->extractDewey($record);
        $callNumber = $this->extractCallNumber($record, $dewey, $authorInfo['primary_author'] ?? '', $titleInfo['title'] ?? '');

        // 5. Publisher & Imprint (260, 264)
        $imprint = $this->extractImprint($record);

        // 6. Edition (250)
        $edition = $this->extractEdition($record);

        // 7. Physical Description (300)
        $pages = $this->extractPages($record);

        // 8. Subjects (650, 651, 600)
        $subjects = $this->extractSubjects($record);

        // 9. Language (008, 041)
        $language = $this->extractLanguage($record);

        // 10. Series & Notes (440/490, 500, 504, 520)
        $seriesTitle = $this->extractSeries($record);
        $summaryNote = $this->extractSummary($record);
        $generalNote = $this->extractNote($record);

        // 11. Raw MARC Fields list for cataloger inspection modal
        $marcFields = $this->extractRawMarcFields($record);

        // 12. Determine readiness / warnings
        $warnings = [];
        if (empty($titleInfo['title'])) {
            $warnings[] = 'Missing book title';
        }
        if (empty($authorInfo['authors']) && empty($authorInfo['primary_author'])) {
            $warnings[] = 'No author detected';
        }
        if (empty($callNumber)) {
            $warnings[] = 'No Call Number or Dewey classification found';
        }
        if (empty($isbn)) {
            $warnings[] = 'No valid ISBN-10 or ISBN-13 specified';
        }

        $status = empty($warnings) ? 'ready' : 'warning';
        $warningMessage = !empty($warnings) ? implode('; ', $warnings) : null;

        return [
            'id' => (string)$index,
            'control_number' => $controlNumber,
            'leader' => $leader,
            'title' => $titleInfo['title'] ?: 'Untitled Record',
            'subtitle' => $titleInfo['subtitle'] ?: null,
            'authors' => $authorInfo['authors'] ?: 'Unknown Author',
            'primary_author' => $authorInfo['primary_author'] ?: ($authorInfo['authors'] ?: 'Unknown Author'),
            'author_list' => $authorInfo['author_list'] ?: [],
            'isbn' => $isbn,
            'issn' => $issn,
            'catalog_kind' => $catalogKind,
            'issue_label' => $issueLabel ? trim($issueLabel) : null,
            'url' => $resourceUrl,
            'call_number' => $callNumber,
            'dewey' => $dewey,
            'publisher' => $imprint['publisher'] ?: 'Independent / Unknown',
            'place' => $imprint['place'] ?: null,
            'year' => $imprint['year'] ?: (string)date('Y'),
            'edition' => $edition,
            'pages' => $pages,
            'subjects' => $subjects,
            'language' => $language,
            'series_title' => $seriesTitle,
            'description' => $summaryNote,
            'note' => $generalNote,
            'status' => $status,
            'warning_message' => $warningMessage,
            'marc_fields' => $marcFields,
            'raw_marc' => method_exists($record, 'toRaw') ? $record->toRaw() : null,
        ];
    }

    /**
     * Batch import multiple bibliographic records into PostgreSQL database.
     *
     * @param array $records Array of parsed record data
     * @param array $options Options (default_collection, default_copies, accession_prefix, start_accession_number, duplicate_policy)
     * @return array Result counts and details
     */
    public function importRecords(array $records, array $options = []): array
    {
        $defaultCollection = $options['default_collection'] ?? 'General Circulation';
        $defaultCopies = max(1, (int)($options['default_copies'] ?? 1));
        $accessionPrefix = $options['accession_prefix'] ?? 'PGPC-2026-';
        $startAccessionNumber = (int)($options['start_accession_number'] ?? 1001);
        $duplicatePolicy = $options['duplicate_policy'] ?? 'skip'; // 'skip', 'update', 'always_create'

        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errors = [];
        $createdBookDetailIds = [];

        $currentAccessionNum = $startAccessionNumber;

        foreach ($records as $record) {
            try {
                DB::beginTransaction();

                $rawTitle = trim($record['title'] ?? 'Untitled');
                $title = mb_substr($rawTitle, 0, 255);
                $rawIsbn = !empty($record['isbn']) ? trim($record['isbn']) : null;
                $isbn = $rawIsbn ? mb_substr($rawIsbn, 0, 255) : null;

                $kind = in_array($record['catalog_kind'] ?? null, ['journal', 'magazine'], true) ? $record['catalog_kind'] : 'book';
                $issueLabel = !empty($record['issue_label']) ? mb_substr(trim($record['issue_label']), 0, 255) : null;
                $sharedTitle = $kind !== 'book' ? BookData::where('book_title', $title)->whereHas('bookDetails', fn ($q) => $q->where('catalog_kind', $kind))->first() : null;
                // Compare serial issues by title and holdings label, not by ISSN alone.
                $existingBookDetail = $sharedTitle && $issueLabel ? $sharedTitle->bookDetails()->where('catalog_kind', $kind)
                    ->whereHas('periodicalIssue', fn ($q) => $q->where('issue_label', $issueLabel))->first() : null;
                if ($kind === 'book' && !empty($isbn)) {
                    $existingBookDetail = BookDetail::where('isbn', $isbn)->where('catalog_kind', 'book')->first();
                }

                if ($kind === 'book' && !$existingBookDetail && !empty($title)) {
                    $existingBookData = BookData::where('book_title', $title)->first();
                    if ($existingBookData) {
                        $existingBookDetail = $existingBookData->bookDetails()->where('catalog_kind', 'book')->first();
                    }
                }

                if ($existingBookDetail) {
                    if ($duplicatePolicy === 'skip') {
                        $skippedCount++;
                        DB::commit();
                        continue;
                    } elseif ($duplicatePolicy === 'update') {
                        // Update existing detail with refreshed catalog metadata
                        $this->updateExistingBookDetail($existingBookDetail, $record, $options);

                        // Add new physical copy/copies to existing title
                        $condition = BookCondition::whereRaw('LOWER(status) = ?', ['good'])->first()
                            ?? BookCondition::first()
                            ?? BookCondition::firstOrCreate(['status' => 'Good']);

                        for ($c = 1; $c <= $defaultCopies; $c++) {
                            $accessionNumber = $this->generateUniqueAccessionNumber($accessionPrefix, $currentAccessionNum);
                            $barcode = 'BC-' . preg_replace('/[^a-zA-Z0-9]/', '', $accessionNumber);

                            $book = Book::create([
                                'book_detail_id' => $existingBookDetail->id,
                                'book_condition_id' => $condition->id,
                                'accession_number' => $accessionNumber,
                                'code' => $barcode,
                                'location' => mb_substr($defaultCollection, 0, 255),
                                'status' => 'available',
                                'date_acquired' => Carbon::now()->toDateString(),
                            ]);

                            if ($existingBookDetail->bookData && $existingBookDetail->bookData->language_id) {
                                $book->languages()->sync([$existingBookDetail->bookData->language_id]);
                            }
                        }

                        $importedCount++;
                        $createdBookDetailIds[] = $existingBookDetail->id;
                        DB::commit();
                        continue;
                    }
                    // 'always_create' falls through to create below
                }

                // 1. Language
                $rawLang = trim($record['language'] ?? 'English');
                $langName = mb_substr($rawLang ?: 'English', 0, 255);
                $language = Language::firstOrCreate(['lang' => $langName]);

                // 2. BookData
                $pubYear = $this->parseYearInteger($record['year'] ?? null);
                $rawSubtitle = !empty($record['subtitle']) ? trim($record['subtitle']) : null;
                $subtitle = $rawSubtitle ? mb_substr($rawSubtitle, 0, 255) : null;
                $rawSeries = !empty($record['series_title']) ? trim($record['series_title']) : null;
                $seriesTitle = $rawSeries ? mb_substr($rawSeries, 0, 255) : null;

                // Prepare and sanitize MARC record string (valid UTF-8, no null-byte chars)
                $marcContent = !empty($record['raw_marc']) ? (string)$record['raw_marc'] : json_encode($record['marc_fields'] ?? [], JSON_UNESCAPED_UNICODE);
                if (!mb_check_encoding($marcContent, 'UTF-8')) {
                    $marcContent = mb_convert_encoding($marcContent, 'UTF-8', 'ISO-8859-1');
                }
                $marcContent = str_replace("\0", '', $marcContent);

                try {
                    $bookData = $sharedTitle ?: BookData::create([
                        'book_title' => $title,
                        'subtitle' => $subtitle,
                        'description' => !empty($record['description']) ? trim($record['description']) : null,
                        'series_title' => $seriesTitle,
                        'note' => !empty($record['note']) ? trim($record['note']) : null,
                        'language_id' => $language->id,
                        'copyright_year' => $pubYear,
                        'marc_record' => $marcContent,
                    ]);
                } catch (\Throwable $bdEx) {
                    // Fallback if DB table column still has a varchar(255) restriction
                    if (str_contains($bdEx->getMessage(), '255') || str_contains(strtolower($bdEx->getMessage()), 'truncated')) {
                        $bookData = BookData::create([
                            'book_title' => $title,
                            'subtitle' => $subtitle,
                            'description' => !empty($record['description']) ? trim($record['description']) : null,
                            'series_title' => $seriesTitle,
                            'note' => !empty($record['note']) ? trim($record['note']) : null,
                            'language_id' => $language->id,
                            'copyright_year' => $pubYear,
                            'marc_record' => mb_substr($marcContent, 0, 250),
                        ]);
                    } else {
                        throw $bdEx;
                    }
                }

                // 3. Authors
                $authorIds = $this->resolveAuthorIds($record);
                if (!$sharedTitle && !empty($authorIds)) {
                    $bookData->authors()->sync(array_unique($authorIds));
                }

                // 4. Categories from subjects / dewey
                $categoryIds = $this->resolveCategoryIds($record);
                if (!$sharedTitle && !empty($categoryIds)) {
                    $bookData->categories()->sync(array_unique($categoryIds));
                }

                // 5. Publisher
                $rawPublisher = trim($record['publisher'] ?? 'Independent / Unknown');
                $publisherName = mb_substr($rawPublisher ?: 'Independent / Unknown', 0, 255);
                $publisher = Publisher::firstOrCreate(['name' => $publisherName]);

                // 6. BookType
                $defaultBookType = $kind !== 'book' ? BookType::firstOrCreate(['type' => ucfirst($kind)]) : (BookType::first() ?? BookType::firstOrCreate(['type' => 'Book']));

                // Parse pages integer
                $pagesInt = null;
                if (!empty($record['pages'])) {
                    if (preg_match('/(\d+)\s*(p|pages|page)/i', $record['pages'], $pMatch)) {
                        $pagesInt = (int)$pMatch[1];
                    } else {
                        $cleanedPages = (int)preg_replace('/[^0-9]/', '', $record['pages']);
                        $pagesInt = $cleanedPages > 0 ? $cleanedPages : null;
                    }
                }

                // 7. BookDetail
                $callNum = !empty($record['call_number']) ? trim($record['call_number']) : ('000 GEN ' . date('Y'));
                $classification = !empty($record['dewey']) ? trim($record['dewey']) : null;
                $edition = !empty($record['edition']) ? trim($record['edition']) : null;
                $issn = !empty($record['issn']) ? trim($record['issn']) : null;

                $bookDetail = BookDetail::create([
                    'book_data_id' => $bookData->id,
                    'isbn' => $isbn,
                    'catalog_kind' => $kind,
                    'url' => $record['url'] ?? null,
                    'issn' => $issn ? mb_substr($issn, 0, 255) : null,
                    'publication_year' => $pubYear,
                    'copyright_year' => $pubYear,
                    'edition' => $edition ? mb_substr($edition, 0, 255) : null,
                    'pages' => $pagesInt,
                    'format' => $kind === 'book' ? 'Paperback' : ucfirst($kind),
                    'book_type_id' => $defaultBookType->id,
                    'call_number' => mb_substr($callNum, 0, 255),
                    'classification' => $classification ? mb_substr($classification, 0, 255) : null,
                    'publisher_id' => $publisher->id,
                    'cover_image' => null,
                    'total_copies' => $defaultCopies,
                    'available_copies' => $defaultCopies,
                    'borrowed_copies' => 0,
                    'damaged_lost_copies' => 0,
                ]);

                if ($kind !== 'book') {
                    $bookDetail->periodicalIssue()->create(['issue_label' => $issueLabel]);
                }

                // 8. Book Condition
                $condition = BookCondition::whereRaw('LOWER(status) = ?', ['good'])->first()
                    ?? BookCondition::first()
                    ?? BookCondition::firstOrCreate(['status' => 'Good']);

                // 9. Physical Copies
                for ($c = 1; $c <= $defaultCopies; $c++) {
                    // Safe auto-increment accession number (incremented inside helper)
                    $accessionNumber = $this->generateUniqueAccessionNumber($accessionPrefix, $currentAccessionNum);
                    $barcode = 'BC-' . preg_replace('/[^a-zA-Z0-9]/', '', $accessionNumber);

                    $book = Book::create([
                        'book_detail_id' => $bookDetail->id,
                        'book_condition_id' => $condition->id,
                        'accession_number' => $accessionNumber,
                        'code' => $barcode,
                        'location' => mb_substr($defaultCollection, 0, 255),
                        'status' => 'available',
                        'date_acquired' => Carbon::now()->toDateString(),
                    ]);

                    $book->languages()->sync([$language->id]);
                }

                DB::commit();
                $importedCount++;
                $createdBookDetailIds[] = $bookDetail->id;
            } catch (\Throwable $e) {
                DB::rollBack();
                $failedCount++;
                $errors[] = "Error on '{$record['title']}': " . $e->getMessage();
                Log::error("ExtractMarcData::importRecords error on record: " . $e->getMessage(), [
                    'record' => $record['title'] ?? 'unknown',
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return [
            'success' => $importedCount > 0 || $skippedCount > 0,
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
            'created_ids' => $createdBookDetailIds,
        ];
    }

    /**
     * Update an existing BookDetail record.
     */
    protected function updateExistingBookDetail(BookDetail $bookDetail, array $record, array $options): void
    {
        $pubYear = $this->parseYearInteger($record['year'] ?? null);
        $updates = [];

        if (!empty($record['call_number'])) {
            $updates['call_number'] = mb_substr(trim($record['call_number']), 0, 255);
        }
        if (!empty($record['dewey'])) {
            $updates['classification'] = mb_substr(trim($record['dewey']), 0, 255);
        }
        if ($pubYear > 0) {
            $updates['publication_year'] = $pubYear;
            $updates['copyright_year'] = $pubYear;
        }
        if (!empty($record['edition'])) {
            $updates['edition'] = mb_substr(trim($record['edition']), 0, 255);
        }

        if (!empty($updates)) {
            $bookDetail->update($updates);
        }

        if ($bookDetail->bookData) {
            $dataUpdates = [];
            if (!empty($record['subtitle'])) {
                $dataUpdates['subtitle'] = mb_substr(trim($record['subtitle']), 0, 255);
            }
            if (!empty($record['description'])) {
                $dataUpdates['description'] = trim($record['description']);
            }
            if (!empty($record['series_title'])) {
                $dataUpdates['series_title'] = mb_substr(trim($record['series_title']), 0, 255);
            }
            if (!empty($dataUpdates)) {
                $bookDetail->bookData->update($dataUpdates);
            }
        }
    }

    /**
     * Ensure the database column book_datas.marc_record is TYPE TEXT.
     */
    protected function ensureMarcRecordColumnType(): void
    {
        static $ensured = false;
        if ($ensured) {
            return;
        }

        try {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE book_datas ALTER COLUMN marc_record TYPE TEXT');
            } elseif ($driver === 'mysql') {
                DB::statement('ALTER TABLE book_datas MODIFY marc_record LONGTEXT NULL');
            }
            $ensured = true;
        } catch (\Throwable $e) {
            // Log notice and continue - fallback in create BookData will handle 255-char limit if DDL not permitted
            Log::info('ExtractMarcData::ensureMarcRecordColumnType note: ' . $e->getMessage());
        }
    }

    /**
     * Parse valid 4-digit publication / copyright year.
     */
    protected function parseYearInteger(mixed $rawYear): int
    {
        if (!empty($rawYear)) {
            $str = (string)$rawYear;
            if (preg_match('/\b(1[5-9]\d{2}|20\d{2}|2100)\b/', $str, $yMatch)) {
                return (int)$yMatch[1];
            }
            $cleaned = (int)preg_replace('/[^0-9]/', '', $str);
            if ($cleaned >= 1000 && $cleaned <= 2100) {
                return $cleaned;
            }
        }
        return (int)date('Y');
    }

    /**
     * Generate next available accession number without collision.
     */
    protected function generateUniqueAccessionNumber(string $prefix, int &$startNum): string
    {
        do {
            $accessionNumber = $prefix . str_pad((string)$startNum, 4, '0', STR_PAD_LEFT);
            $barcode = 'BC-' . preg_replace('/[^a-zA-Z0-9]/', '', $accessionNumber);
            $startNum++;
            $exists = Book::where('accession_number', $accessionNumber)->exists()
                   || Book::where('code', $barcode)->exists();
        } while ($exists);

        return $accessionNumber;
    }

    /**
     * Resolve or create Author IDs for database linking.
     */
    protected function resolveAuthorIds(array $record): array
    {
        $authorIds = [];
        $authorList = $record['author_list'] ?? [];

        if (empty($authorList) && !empty($record['authors'])) {
            // Split comma-separated authors
            $rawAuthors = explode(';', $record['authors']);
            if (count($rawAuthors) === 1) {
                $rawAuthors = preg_split('/\s*,\s*(?=[A-Z][a-z]+)/', $record['authors']);
            }
            $authorList = $rawAuthors;
        }

        foreach ($authorList as $authorRaw) {
            $authorName = trim((string)$authorRaw);
            if (empty($authorName) || $authorName === 'Unknown Author') {
                continue;
            }

            // Clean dates and roles (e.g. "Cormen, Thomas H., 1956- author.")
            $authorName = preg_replace('/\b\d{4}-\d{0,4}\b/', '', $authorName);
            $authorName = preg_replace('/,\s*(author|editor|illustrator|translator|jr|sr)\b/i', '', $authorName);
            $authorName = trim($authorName, " ,.;[]");

            if (empty($authorName)) {
                continue;
            }

            if (str_contains($authorName, ',')) {
                $parts = explode(',', $authorName, 2);
                $lastName = trim($parts[0]);
                $firstName = trim($parts[1] ?? '');
            } else {
                $parts = preg_split('/\s+/', $authorName);
                if (count($parts) > 1) {
                    $lastName = array_pop($parts);
                    $firstName = implode(' ', $parts);
                } else {
                    $firstName = $authorName;
                    $lastName = $authorName;
                }
            }

            $safeFirst = mb_substr($firstName ?: $lastName, 0, 255);
            $safeLast = mb_substr($lastName ?: $firstName, 0, 255);

            $author = Author::where('first_name', $safeFirst)
                ->where('last_name', $safeLast)
                ->first();

            if (!$author) {
                $author = Author::create([
                    'first_name' => $safeFirst,
                    'last_name' => $safeLast,
                ]);
            }

            $authorIds[] = $author->id;
        }

        return array_unique($authorIds);
    }

    /**
     * Resolve or create Category IDs from MARC subjects / dewey.
     */
    protected function resolveCategoryIds(array $record): array
    {
        $categoryIds = [];
        $subjects = $record['subjects'] ?? [];

        foreach ($subjects as $subject) {
            $catName = trim(rtrim($subject, '.'));
            if (empty($catName) || strlen($catName) < 2) {
                continue;
            }

            $safeCatName = mb_substr($catName, 0, 255);
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $safeCatName), 0, 4));

            $category = Category::firstOrCreate([
                'name' => $safeCatName,
            ], [
                'code' => $code,
            ]);

            $categoryIds[] = $category->id;
        }

        // If no categories from subjects, use Dewey major division
        if (empty($categoryIds) && !empty($record['dewey'])) {
            $deweyPrefix = substr(trim($record['dewey']), 0, 3);
            $generalName = $this->getDeweyDivisionName($deweyPrefix);
            if ($generalName) {
                $cat = Category::firstOrCreate([
                    'name' => mb_substr($generalName, 0, 255),
                ], [
                    'code' => $deweyPrefix,
                ]);
                $categoryIds[] = $cat->id;
            }
        }

        return array_unique($categoryIds);
    }

    /**
     * Get 100-division standard Dewey name.
     */
    protected function getDeweyDivisionName(string $class): ?string
    {
        $num = (int)$class;
        return match (true) {
            $num >= 0 && $num < 100 => 'Computer Science, Information & Generalities',
            $num >= 100 && $num < 200 => 'Philosophy & Psychology',
            $num >= 200 && $num < 300 => 'Religion & Theology',
            $num >= 300 && $num < 400 => 'Social Sciences',
            $num >= 400 && $num < 500 => 'Language & Linguistics',
            $num >= 500 && $num < 600 => 'Pure Science & Mathematics',
            $num >= 600 && $num < 700 => 'Technology & Applied Sciences',
            $num >= 700 && $num < 800 => 'Arts & Recreation',
            $num >= 800 && $num < 900 => 'Literature & Rhetoric',
            $num >= 900 && $num <= 999 => 'History & Geography',
            default => null,
        };
    }

    /**
     * Extract control number from 001 or fallback.
     */
    protected function extractControlNumber(Record $record, int $index): string
    {
        $f001 = $record->getField('001');
        if ($f001) {
            $val = trim($f001->getData());
            if (!empty($val)) {
                return $val;
            }
        }

        return 'PGPC-MRC-' . str_pad((string)(1040 + $index), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Extract title statement (245 $a, $b, $c).
     */
    protected function extractTitle(Record $record): array
    {
        $f245 = $record->getField('245');
        if (!$f245) {
            return [
                'title' => '',
                'subtitle' => null,
                'statement_of_responsibility' => null,
            ];
        }

        $a = $f245->getSubfield('a');
        $b = $f245->getSubfield('b');
        $c = $f245->getSubfield('c');

        $title = $a ? trim($a->getData()) : '';
        $title = rtrim($title, " /:;,");

        $subtitle = $b ? trim($b->getData()) : null;
        if ($subtitle) {
            $subtitle = rtrim($subtitle, " /:;,");
        }

        $responsibility = $c ? trim($c->getData()) : null;
        if ($responsibility) {
            $responsibility = rtrim($responsibility, " .;");
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'statement_of_responsibility' => $responsibility,
        ];
    }

    /**
     * Extract authors (100, 700, 110, 245$c).
     */
    protected function extractAuthors(Record $record, ?string $responsibility = null): array
    {
        $primary = null;
        $authorList = [];

        // Main Entry Personal Name (100)
        $f100 = $record->getField('100');
        if ($f100) {
            $subA = $f100->getSubfield('a');
            if ($subA) {
                $primary = trim(rtrim($subA->getData(), " ,;"));
                $authorList[] = $primary;
            }
        }

        // Main Entry Corporate (110) if no personal author
        if (!$primary) {
            $f110 = $record->getField('110');
            if ($f110) {
                $subA = $f110->getSubfield('a');
                if ($subA) {
                    $primary = trim(rtrim($subA->getData(), " ,;"));
                    $authorList[] = $primary;
                }
            }
        }

        // Added Entry Personal Names (700)
        $f700s = $record->getFields('700');
        foreach ($f700s as $f700) {
            $subA = $f700->getSubfield('a');
            if ($subA) {
                $secondary = trim(rtrim($subA->getData(), " ,;"));
                if (!in_array($secondary, $authorList)) {
                    $authorList[] = $secondary;
                }
            }
        }

        // Fallback to statement of responsibility if still empty
        if (empty($authorList) && !empty($responsibility)) {
            $cleaned = preg_replace('/^(by|written by|edited by)\s+/i', '', $responsibility);
            $primary = $cleaned;
            $authorList[] = $cleaned;
        }

        return [
            'primary_author' => $primary ?: (!empty($authorList) ? $authorList[0] : null),
            'authors' => implode(', ', $authorList),
            'author_list' => $authorList,
        ];
    }

    /**
     * Extract ISBN (020).
     */
    protected function extractIsbn(Record $record): ?string
    {
        $f020s = $record->getFields('020');
        foreach ($f020s as $f020) {
            $subA = $f020->getSubfield('a');
            if ($subA) {
                $val = trim($subA->getData());
                // Extract only numbers and hyphens or X
                if (preg_match('/[0-9Xx\-]{10,17}/', $val, $matches)) {
                    return $matches[0];
                }
            }
        }

        return null;
    }

    /**
     * Extract ISSN (022).
     */
    protected function extractIssn(Record $record): ?string
    {
        $f022 = $record->getField('022');
        if ($f022) {
            $subA = $f022->getSubfield('a');
            if ($subA) {
                $val = trim($subA->getData());
                if (preg_match('/\d{4}-\d{3}[\dX]/i', $val, $matches)) {
                    return $matches[0];
                }
            }
        }
        return null;
    }

    /**
     * Extract Dewey Classification (082).
     */
    protected function extractDewey(Record $record): ?string
    {
        $f082 = $record->getField('082');
        if ($f082) {
            $subA = $f082->getSubfield('a');
            if ($subA) {
                return trim($subA->getData());
            }
        }
        return null;
    }

    /**
     * Extract Call Number (050, 090, or generate from Dewey + Author + Year).
     */
    protected function extractCallNumber(Record $record, ?string $dewey, string $author, string $title): string
    {
        // 1. Try local call number (090 / 099)
        $f090 = $record->getField('090') ?? $record->getField('099');
        if ($f090) {
            $parts = [];
            foreach ($f090->getSubfields() as $sf) {
                $parts[] = trim($sf->getData());
            }
            if (!empty($parts)) {
                return implode(' ', $parts);
            }
        }

        // 2. Try LC Call number (050)
        $f050 = $record->getField('050');
        if ($f050) {
            $parts = [];
            foreach ($f050->getSubfields() as $sf) {
                $parts[] = trim($sf->getData());
            }
            if (!empty($parts)) {
                return implode(' ', $parts);
            }
        }

        // 3. Fallback: synthesize standard library Call Number
        $deweyNum = $dewey ?: '000';
        $authorCut = 'GEN';

        if (!empty($author)) {
            $cleanAuthor = preg_replace('/[^a-zA-Z]/', '', $author);
            if (strlen($cleanAuthor) >= 3) {
                $authorCut = strtoupper(substr($cleanAuthor, 0, 3));
            }
        } elseif (!empty($title)) {
            $cleanTitle = preg_replace('/^(the|a|an)\s+/i', '', $title);
            $cleanTitle = preg_replace('/[^a-zA-Z]/', '', $cleanTitle);
            if (strlen($cleanTitle) >= 3) {
                $authorCut = strtoupper(substr($cleanTitle, 0, 3));
            }
        }

        $year = $this->extractYearFromRecord($record) ?: date('Y');

        return "{$deweyNum} {$authorCut} {$year}";
    }

    /**
     * Extract Imprint: Publisher, Place, Year (264 / 260).
     */
    protected function extractImprint(Record $record): array
    {
        $f = $record->getField('264') ?? $record->getField('260');

        $publisher = null;
        $place = null;
        $year = null;

        if ($f) {
            $b = $f->getSubfield('b');
            if ($b) {
                $publisher = trim(rtrim($b->getData(), " ,;:[]"));
            }

            $a = $f->getSubfield('a');
            if ($a) {
                $place = trim(rtrim($a->getData(), " ,;:[]"));
            }

            $c = $f->getSubfield('c');
            if ($c) {
                $rawC = $c->getData();
                if (preg_match('/\b(19\d\d|20\d\d)\b/', $rawC, $matches)) {
                    $year = $matches[1];
                }
            }
        }

        if (!$year) {
            $year = $this->extractYearFromRecord($record);
        }

        return [
            'publisher' => $publisher,
            'place' => $place,
            'year' => $year,
        ];
    }

    /**
     * Extract 4-digit year from 008 position 7-10 or 260/264.
     */
    protected function extractYearFromRecord(Record $record): ?string
    {
        $f008 = $record->getField('008');
        if ($f008) {
            $data = $f008->getData();
            if (strlen($data) >= 11) {
                $y = substr($data, 7, 4);
                if (preg_match('/^(19|20)\d{2}$/', $y)) {
                    return $y;
                }
            }
        }
        return null;
    }

    /**
     * Extract Edition (250 $a).
     */
    protected function extractEdition(Record $record): ?string
    {
        $f250 = $record->getField('250');
        if ($f250) {
            $a = $f250->getSubfield('a');
            if ($a) {
                return trim(rtrim($a->getData(), " ."));
            }
        }
        return null;
    }

    /**
     * Extract Physical Description (300 $a).
     */
    protected function extractPages(Record $record): ?string
    {
        $f300 = $record->getField('300');
        if ($f300) {
            $parts = [];
            foreach ($f300->getSubfields() as $sf) {
                $parts[] = trim($sf->getData());
            }
            if (!empty($parts)) {
                return implode(' : ', $parts);
            }
        }
        return null;
    }

    /**
     * Extract Subjects (650, 651, 600).
     */
    protected function extractSubjects(Record $record): array
    {
        $subjects = [];

        foreach (['650', '651', '600'] as $tag) {
            foreach ($record->getFields($tag) as $f) {
                $a = $f->getSubfield('a');
                if ($a) {
                    $text = trim(rtrim($a->getData(), " ."));
                    if (!empty($text) && !in_array($text, $subjects)) {
                        $subjects[] = $text;
                    }
                }
            }
        }

        return $subjects;
    }

    /**
     * Extract Language from 008 or 041.
     */
    protected function extractLanguage(Record $record): string
    {
        // Try 041 $a
        $f041 = $record->getField('041');
        if ($f041) {
            $a = $f041->getSubfield('a');
            if ($a) {
                $code = strtolower(trim(substr($a->getData(), 0, 3)));
                if (isset(self::$languageMap[$code])) {
                    return self::$languageMap[$code];
                }
            }
        }

        // Try 008 pos 35-37
        $f008 = $record->getField('008');
        if ($f008) {
            $data = $f008->getData();
            if (strlen($data) >= 38) {
                $code = strtolower(substr($data, 35, 3));
                if (isset(self::$languageMap[$code])) {
                    return self::$languageMap[$code];
                }
            }
        }

        return 'English';
    }

    /**
     * Extract Series Statement (440 / 490).
     */
    protected function extractSeries(Record $record): ?string
    {
        $f = $record->getField('490') ?? $record->getField('440');
        if ($f) {
            $a = $f->getSubfield('a');
            if ($a) {
                return trim(rtrim($a->getData(), " .;"));
            }
        }
        return null;
    }

    /**
     * Extract Summary Note (520 $a).
     */
    protected function extractSummary(Record $record): ?string
    {
        $f520 = $record->getField('520');
        if ($f520) {
            $a = $f520->getSubfield('a');
            if ($a) {
                return trim($a->getData());
            }
        }
        return null;
    }

    /**
     * Extract General Note (500, 504).
     */
    protected function extractNote(Record $record): ?string
    {
        $f500 = $record->getField('500') ?? $record->getField('504');
        if ($f500) {
            $a = $f500->getSubfield('a');
            if ($a) {
                return trim($a->getData());
            }
        }
        return null;
    }

    /**
     * Extract raw MARC fields array for the visual inspection modal.
     */
    protected function extractRawMarcFields(Record $record): array
    {
        $fields = [];
        $wrapped = $record->getRecord();
        if (!$wrapped) {
            return $fields;
        }

        // Leader
        $leader = $wrapped->getLeader();
        if (!empty($leader)) {
            $fields[] = [
                'tag' => 'LDR',
                'ind1' => '',
                'ind2' => '',
                'data' => $leader,
                'desc' => self::$tagDescriptions['LDR'] ?? 'Record Leader',
            ];
        }

        foreach ($wrapped->getFields() as $field) {
            $tag = $field->getTag();
            $desc = self::$tagDescriptions[$tag] ?? 'Variable Field';

            $isControl = method_exists($field, 'isControlField')
                ? $field->isControlField()
                : (($field instanceof File_MARC_Control_Field) || !method_exists($field, 'getSubfields') || ($tag < '010' && is_numeric($tag)));

            if ($isControl) {
                $fields[] = [
                    'tag' => $tag,
                    'ind1' => '',
                    'ind2' => '',
                    'data' => method_exists($field, 'getData') ? $field->getData() : '',
                    'desc' => $desc,
                ];
            } else {
                $ind1 = method_exists($field, 'getIndicator') ? ($field->getIndicator(1) ?: '#') : '#';
                $ind2 = method_exists($field, 'getIndicator') ? ($field->getIndicator(2) ?: '#') : '#';

                $subfieldStr = '';
                if (method_exists($field, 'getSubfields')) {
                    foreach ($field->getSubfields() as $sf) {
                        $code = method_exists($sf, 'getCode') ? $sf->getCode() : '';
                        $data = method_exists($sf, 'getData') ? $sf->getData() : '';
                        $subfieldStr .= '$' . $code . ' ' . $data . ' ';
                    }
                }

                $fields[] = [
                    'tag' => $tag,
                    'ind1' => $ind1,
                    'ind2' => $ind2,
                    'data' => trim($subfieldStr),
                    'desc' => $desc,
                ];
            }
        }

        return $fields;
    }

    /**
     * Get high-quality default sample bibliographic records for demonstration/testing.
     */
    public function getSampleRecords(): array
    {
        return [
            [
                'id' => '1',
                'control_number' => 'PGPC-MRC-001042',
                'leader' => '01452nam a2200385 i 4500',
                'title' => 'Introduction to Algorithms',
                'subtitle' => 'Fourth Edition',
                'authors' => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein',
                'primary_author' => 'Cormen, Thomas H.',
                'author_list' => ['Cormen, Thomas H.', 'Leiserson, Charles E.', 'Rivest, Ronald L.', 'Stein, Clifford'],
                'isbn' => '978-0-262-04630-5',
                'issn' => null,
                'call_number' => '005.1 COR 2022',
                'dewey' => '005.1',
                'publisher' => 'The MIT Press',
                'place' => 'Cambridge, Massachusetts',
                'year' => '2022',
                'edition' => 'Fourth edition',
                'pages' => 'xix, 1292 pages',
                'subjects' => ['Computer algorithms', 'Data structures (Computer science)', 'Computational complexity'],
                'language' => 'English',
                'series_title' => null,
                'description' => 'Comprehensive textbook covering data structures, sorting, graph algorithms, and dynamic programming.',
                'note' => 'Includes bibliographical references and index.',
                'status' => 'ready',
                'warning_message' => null,
                'marc_fields' => [
                    ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01452nam a2200385 i 4500', 'desc' => 'Record Leader'],
                    ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn946571234', 'desc' => 'Control Number'],
                    ['tag' => '008', 'ind1' => '', 'ind2' => '', 'data' => '220115s2022    maub     b    001 0 eng  ', 'desc' => 'Fixed-Length Data Elements'],
                    ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9780262046305 $q hardcover', 'desc' => 'International Standard Book Number (ISBN)'],
                    ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 005.1 $2 23', 'desc' => 'Dewey Decimal Classification'],
                    ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Cormen, Thomas H., $e author.', 'desc' => 'Main Entry -- Personal Name'],
                    ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Introduction to algorithms / $c Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein.', 'desc' => 'Title Statement'],
                    ['tag' => '250', 'ind1' => '#', 'ind2' => '#', 'data' => '$a Fourth edition.', 'desc' => 'Edition Statement'],
                    ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Cambridge, Massachusetts : $b The MIT Press, $c [2022]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                    ['tag' => '300', 'ind1' => '#', 'ind2' => '#', 'data' => '$a xix, 1292 pages : $b illustrations ; $c 24 cm', 'desc' => 'Physical Description'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Computer algorithms.', 'desc' => 'Subject Added Entry -- Topical Term'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Data structures (Computer science)', 'desc' => 'Subject Added Entry -- Topical Term'],
                ],
            ],
            [
                'id' => '2',
                'control_number' => 'PGPC-MRC-001043',
                'leader' => '01210nam a2200325 a 4500',
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'subtitle' => 'A Handbook of Agile Software Craftsmanship',
                'authors' => 'Robert C. Martin',
                'primary_author' => 'Martin, Robert C.',
                'author_list' => ['Martin, Robert C.'],
                'isbn' => '978-0-13-235088-4',
                'issn' => null,
                'call_number' => '005.1 MAR 2008',
                'dewey' => '005.1',
                'publisher' => 'Prentice Hall',
                'place' => 'Upper Saddle River, NJ',
                'year' => '2008',
                'edition' => '1st edition',
                'pages' => 'xxix, 431 pages',
                'subjects' => ['Agile software development', 'Software refactoring', 'Computer programming'],
                'language' => 'English',
                'series_title' => 'Robert C. Martin series',
                'description' => 'A handbook of agile software craftsmanship with best practices for writing clean, maintainable code.',
                'note' => 'Includes index.',
                'status' => 'ready',
                'warning_message' => null,
                'marc_fields' => [
                    ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01210nam a2200325 a 4500', 'desc' => 'Record Leader'],
                    ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn221987114', 'desc' => 'Control Number'],
                    ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9780132350884 $q paperback', 'desc' => 'International Standard Book Number (ISBN)'],
                    ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 005.1 $2 22', 'desc' => 'Dewey Decimal Classification'],
                    ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Martin, Robert C., $e author.', 'desc' => 'Main Entry -- Personal Name'],
                    ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Clean code : $b a handbook of agile software craftsmanship / $c Robert C. Martin.', 'desc' => 'Title Statement'],
                    ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Upper Saddle River, NJ : $b Prentice Hall, $c [2008]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                    ['tag' => '300', 'ind1' => '#', 'ind2' => '#', 'data' => '$a xxix, 431 pages : $b illustrations ; $c 24 cm', 'desc' => 'Physical Description'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Agile software development.', 'desc' => 'Subject Added Entry -- Topical Term'],
                ],
            ],
            [
                'id' => '3',
                'control_number' => 'PGPC-MRC-001044',
                'leader' => '01124nam a2200289 a 4500',
                'title' => 'Artificial Intelligence: A Modern Approach',
                'subtitle' => 'Global Edition',
                'authors' => 'Stuart Russell, Peter Norvig',
                'primary_author' => 'Russell, Stuart',
                'author_list' => ['Russell, Stuart', 'Norvig, Peter'],
                'isbn' => '978-1-292-40113-3',
                'issn' => null,
                'call_number' => '006.3 RUS 2021',
                'dewey' => '006.3',
                'publisher' => 'Pearson',
                'place' => 'Harlow, United Kingdom',
                'year' => '2021',
                'edition' => 'Fourth edition',
                'pages' => '1166 pages',
                'subjects' => ['Artificial intelligence', 'Machine learning', 'Intelligent agents'],
                'language' => 'English',
                'series_title' => null,
                'description' => 'The leading textbook on artificial intelligence, intelligent agents, and neural networks.',
                'note' => 'Global edition.',
                'status' => 'ready',
                'warning_message' => null,
                'marc_fields' => [
                    ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '01124nam a2200289 a 4500', 'desc' => 'Record Leader'],
                    ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn1154874401', 'desc' => 'Control Number'],
                    ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9781292401133', 'desc' => 'International Standard Book Number (ISBN)'],
                    ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 006.3 $2 23', 'desc' => 'Dewey Decimal Classification'],
                    ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Russell, Stuart, $e author.', 'desc' => 'Main Entry -- Personal Name'],
                    ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Artificial intelligence : $b a modern approach / $c Stuart Russell, Peter Norvig.', 'desc' => 'Title Statement'],
                    ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Harlow, England : $b Pearson, $c [2021]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Artificial intelligence.', 'desc' => 'Subject Added Entry -- Topical Term'],
                ],
            ],
            [
                'id' => '4',
                'control_number' => 'PGPC-MRC-001045',
                'leader' => '00984nam a2200265 i 4500',
                'title' => 'Database System Concepts',
                'subtitle' => 'Seventh Edition',
                'authors' => 'Abraham Silberschatz, Henry F. Korth, S. Sudarshan',
                'primary_author' => 'Silberschatz, Abraham',
                'author_list' => ['Silberschatz, Abraham', 'Korth, Henry F.', 'Sudarshan, S.'],
                'isbn' => '978-0-07-802215-9',
                'issn' => null,
                'call_number' => '005.74 SIL 2020',
                'dewey' => '005.74',
                'publisher' => 'McGraw-Hill Education',
                'place' => 'New York, NY',
                'year' => '2020',
                'edition' => 'Seventh edition',
                'pages' => 'xxvi, 1349 pages',
                'subjects' => ['Database management', 'Relational databases', 'SQL'],
                'language' => 'English',
                'series_title' => null,
                'description' => 'Comprehensive introduction to database systems, relational algebra, SQL, and indexing.',
                'note' => 'Includes index.',
                'status' => 'ready',
                'warning_message' => null,
                'marc_fields' => [
                    ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '00984nam a2200265 i 4500', 'desc' => 'Record Leader'],
                    ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'ocn1084328821', 'desc' => 'Control Number'],
                    ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9780078022159', 'desc' => 'International Standard Book Number (ISBN)'],
                    ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 005.74 $2 23', 'desc' => 'Dewey Decimal Classification'],
                    ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Silberschatz, Abraham, $e author.', 'desc' => 'Main Entry -- Personal Name'],
                    ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Database system concepts / $c Abraham Silberschatz, Henry F. Korth, S. Sudarshan.', 'desc' => 'Title Statement'],
                    ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a New York, NY : $b McGraw-Hill, $c [2020]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Database management.', 'desc' => 'Subject Added Entry -- Topical Term'],
                ],
            ],
            [
                'id' => '5',
                'control_number' => 'PGPC-MRC-001046',
                'leader' => '00912nam a2200241 i 4500',
                'title' => 'Florante at Laura: Isang Makabagong Pagbasa',
                'subtitle' => 'Kritikal na Edisyon',
                'authors' => 'Francisco Balagtas Baltazar',
                'primary_author' => 'Baltazar, Francisco',
                'author_list' => ['Baltazar, Francisco', 'Virgilio S. Almario'],
                'isbn' => '978-971-508-412-9',
                'issn' => null,
                'call_number' => 'FIL 899.211 BAL 2018',
                'dewey' => '899.211',
                'publisher' => 'Adarna House',
                'place' => 'Quezon City, Philippines',
                'year' => '2018',
                'edition' => 'Bagong edisyon',
                'pages' => '180 pahina',
                'subjects' => ['Philippine poetry (Tagalog)', 'Awit at kurido', 'Panitikang Pilipino'],
                'language' => 'Filipino',
                'series_title' => null,
                'description' => 'Klasikong awit ni Francisco Balagtas na may gabay sa pag-aaral para sa mga estudyante ng kolehiyo.',
                'note' => 'Nakasulat sa wikang Filipino.',
                'status' => 'ready',
                'warning_message' => null,
                'marc_fields' => [
                    ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '00912nam a2200241 i 4500', 'desc' => 'Record Leader'],
                    ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'phl991204882', 'desc' => 'Control Number'],
                    ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9789715084129', 'desc' => 'International Standard Book Number (ISBN)'],
                    ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 899.211 $2 22', 'desc' => 'Dewey Decimal Classification'],
                    ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Baltazar, Francisco, $d 1788-1862.', 'desc' => 'Main Entry -- Personal Name'],
                    ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Florante at Laura : $b isang makabagong pagbasa / $c Francisco Baltazar.', 'desc' => 'Title Statement'],
                    ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Lungsod Quezon : $b Adarna House, $c [2018]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Tagalog poetry.', 'desc' => 'Subject Added Entry -- Topical Term'],
                ],
            ],
            [
                'id' => '6',
                'control_number' => 'PGPC-MRC-001047',
                'leader' => '00750nam a2200215 u 4500',
                'title' => 'Advanced Embedded Systems and IoT Protocols',
                'subtitle' => null,
                'authors' => 'Unknown Author',
                'primary_author' => 'Unknown Author',
                'author_list' => [],
                'isbn' => null,
                'issn' => null,
                'call_number' => '004.678 ADV 2023',
                'dewey' => '004.678',
                'publisher' => 'TechPress International',
                'place' => 'Singapore',
                'year' => '2023',
                'edition' => null,
                'pages' => '320 pages',
                'subjects' => ['Internet of things', 'Embedded computer systems'],
                'language' => 'English',
                'series_title' => null,
                'description' => 'Draft research monograph on IoT hardware and communication stack.',
                'note' => 'Pre-publication draft copy.',
                'status' => 'warning',
                'warning_message' => 'No standard ISBN found; No primary author specified',
                'marc_fields' => [
                    ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '00750nam a2200215 u 4500', 'desc' => 'Record Leader'],
                    ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'tmp00482910', 'desc' => 'Control Number'],
                    ['tag' => '082', 'ind1' => '0', 'ind2' => '4', 'data' => '$a 004.678 $2 23', 'desc' => 'Dewey Decimal Classification'],
                    ['tag' => '245', 'ind1' => '0', 'ind2' => '0', 'data' => '$a Advanced embedded systems and IoT protocols.', 'desc' => 'Title Statement'],
                    ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Singapore : $b TechPress International, $c [2023]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Internet of things.', 'desc' => 'Subject Added Entry -- Topical Term'],
                ],
            ],
            [
                'id' => '7',
                'control_number' => 'PGPC-MRC-001048',
                'leader' => '00820nam a2200225 u 4500',
                'title' => 'Contemporary Philippine Governance & Public Policy',
                'subtitle' => null,
                'authors' => 'Maria Socorro L. Reyes',
                'primary_author' => 'Reyes, Maria Socorro L.',
                'author_list' => ['Reyes, Maria Socorro L.'],
                'isbn' => '978-971-02-3345-1',
                'issn' => null,
                'call_number' => '320.9599 REY 2021',
                'dewey' => null,
                'publisher' => 'Anvil Publishing',
                'place' => 'Mandaluyong City, Philippines',
                'year' => '2021',
                'edition' => null,
                'pages' => '254 pages',
                'subjects' => ['Public administration -- Philippines', 'Political science'],
                'language' => 'English',
                'series_title' => null,
                'description' => 'A comprehensive review of governance reforms and policy implementation in the Philippines.',
                'note' => null,
                'status' => 'warning',
                'warning_message' => 'No Call Number or Dewey classification found',
                'marc_fields' => [
                    ['tag' => 'LDR', 'ind1' => '', 'ind2' => '', 'data' => '00820nam a2200225 u 4500', 'desc' => 'Record Leader'],
                    ['tag' => '001', 'ind1' => '', 'ind2' => '', 'data' => 'phl00392184', 'desc' => 'Control Number'],
                    ['tag' => '020', 'ind1' => '#', 'ind2' => '#', 'data' => '$a 9789710233451', 'desc' => 'International Standard Book Number (ISBN)'],
                    ['tag' => '100', 'ind1' => '1', 'ind2' => '#', 'data' => '$a Reyes, Maria Socorro L.', 'desc' => 'Main Entry -- Personal Name'],
                    ['tag' => '245', 'ind1' => '1', 'ind2' => '0', 'data' => '$a Contemporary Philippine governance & public policy / $c Maria Socorro L. Reyes.', 'desc' => 'Title Statement'],
                    ['tag' => '264', 'ind1' => '#', 'ind2' => '1', 'data' => '$a Mandaluyong City : $b Anvil Publishing, $c [2021]', 'desc' => 'Production, Publication, Distribution (Publisher)'],
                    ['tag' => '650', 'ind1' => '#', 'ind2' => '0', 'data' => '$a Public administration -- Philippines.', 'desc' => 'Subject Added Entry -- Topical Term'],
                ],
            ],
        ];
    }
}
