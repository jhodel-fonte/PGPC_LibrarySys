<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookType;
use App\Models\CatalogImport;
use App\Models\CatalogImportRow;
use App\Models\Category;
use App\Models\Language;
use App\Models\MaterialType;
use App\Models\Publisher;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class CatalogSpreadsheetImportService
{
    /**
     * Map of common header names and aliases to normalized catalog fields.
     */
    public const HEADER_ALIASES = [
        // Title
        'title' => 'title',
        'book_title' => 'title',
        'main_title' => 'title',
        'title_statement' => 'title',
        'book_name' => 'title',
        'titles' => 'title',
        'book' => 'title',

        // Author
        'author' => 'author',
        'authors' => 'author',
        'creator' => 'author',
        'creators' => 'author',
        'author_name' => 'author',
        'primary_author' => 'author',
        'writer' => 'author',
        'writers' => 'author',
        'main_author' => 'author',
        'author_creator' => 'author',

        // Accession Number / Barcode
        'accession_no' => 'accession_no',
        'accession_number' => 'accession_no',
        'accession' => 'accession_no',
        'accession_num' => 'accession_no',
        'barcode' => 'accession_no',
        'acc_no' => 'accession_no',
        'acc_num' => 'accession_no',
        'item_barcode' => 'accession_no',
        'accessionno' => 'accession_no',
        'accno' => 'accession_no',
        'accnum' => 'accession_no',
        'accession_' => 'accession_no',
        'item_no' => 'accession_no',

        // Material Type / Format
        'material_type' => 'material_type',
        'material' => 'material_type',
        'item_type' => 'material_type',
        'format' => 'material_type',
        'resource_type' => 'material_type',
        'type' => 'material_type',
        'media_type' => 'material_type',

        // ISBN / ISSN / LCCN
        'isbn' => 'isbn',
        'isbn_13' => 'isbn',
        'isbn13' => 'isbn',
        'isbn_10' => 'isbn',
        'isbn10' => 'isbn',
        'isbn_no' => 'isbn',
        'isbn_number' => 'isbn',
        'issn' => 'issn',
        'issn_no' => 'issn',
        'lccn' => 'lccn',

        // Call Number / Classification
        'call_number' => 'call_number',
        'call_no' => 'call_number',
        'callnumber' => 'call_number',
        'callno' => 'call_number',
        'classification' => 'call_number',
        'classification_no' => 'call_number',
        'class_number' => 'call_number',
        'class_no' => 'call_number',
        'dewey' => 'call_number',
        'dewey_decimal' => 'call_number',
        'ddc' => 'call_number',
        'lcc' => 'call_number',
        'shelf_call_number' => 'call_number',
        'call' => 'call_number',

        // Publisher
        'publisher' => 'publisher',
        'publisher_name' => 'publisher',
        'press' => 'publisher',
        'publishing_company' => 'publisher',
        'imprint' => 'publisher',
        'publication' => 'publisher',

        // Publisher Address / Place of Publication
        'publisher_address' => 'publisher_address',
        'place_of_publication' => 'publisher_address',
        'publication_place' => 'publisher_address',
        'pub_place' => 'publisher_address',
        'city' => 'publisher_address',
        'address' => 'publisher_address',
        'place' => 'publisher_address',
        'city_of_publication' => 'publisher_address',

        // Publication Year
        'publication_year' => 'publication_year',
        'pub_year' => 'publication_year',
        'year' => 'publication_year',
        'copyright_year' => 'publication_year',
        'date' => 'publication_year',
        'date_of_publication' => 'publication_year',
        'pub_date' => 'publication_year',
        'year_published' => 'publication_year',
        'published_year' => 'publication_year',

        // Categories / Genres
        'categories' => 'categories',
        'category' => 'categories',
        'genre' => 'categories',
        'genres' => 'categories',
        'collection_type' => 'categories',

        // Subjects / Topics
        'subject' => 'subject',
        'subjects' => 'subject',
        'topic' => 'subject',
        'topics' => 'subject',
        'subject_heading' => 'subject',
        'subject_headings' => 'subject',

        // Shelf / Collection Location
        'sublocation' => 'sublocation',
        'location' => 'sublocation',
        'shelf_location' => 'sublocation',
        'shelf' => 'sublocation',
        'collection' => 'sublocation',
        'section' => 'sublocation',
        'library_section' => 'sublocation',
        'shelf_section' => 'sublocation',

        // Additional catalog fields
        'edition' => 'edition',
        'ed' => 'edition',
        'pages' => 'pages',
        'page_count' => 'pages',
        'no_of_pages' => 'pages',
        'extent' => 'pages',
        'language' => 'language',
        'lang' => 'language',
        'subtitle' => 'subtitle',
        'sub_title' => 'subtitle',
        'description' => 'description',
        'summary' => 'description',
        'series_title' => 'series_title',
        'series' => 'series_title',
        'note' => 'note',
        'notes' => 'note',
    ];

    /**
     * Inspect and validate uploaded spreadsheet rows without committing to database.
     */
    public function review(string $path, string $originalName, ?int $userId, array $options = []): CatalogImport
    {
        @set_time_limit(300);

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Fast row extraction depending on file format
        if (in_array($extension, ['csv', 'txt'], true)) {
            $rawRows = $this->readCsvRows($path);
        } else {
            $reader = IOFactory::createReaderForFile($path);
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            if (method_exists($reader, 'setReadEmptyCells')) {
                $reader->setReadEmptyCells(false);
            }
            $spreadsheet = $reader->load($path);
            $rawRows = $spreadsheet->getActiveSheet()->toArray('', false, false, false);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);
        }

        if (empty($rawRows)) {
            throw new \RuntimeException('The uploaded file contains no data.');
        }

        // Intelligently locate the header row within the first 10 rows
        $bestHeaderRowIndex = 0;
        $bestHeaderCount = 0;
        $maxScan = min(10, count($rawRows));

        for ($i = 0; $i < $maxScan; $i++) {
            $candidate = $rawRows[$i];
            if (!is_array($candidate)) {
                continue;
            }

            $matchedCount = 0;
            $hasTitleHeader = false;

            foreach ($candidate as $colVal) {
                $norm = $this->normalizeHeader((string) $colVal);
                if ($norm !== '' && (isset(self::HEADER_ALIASES[$norm]) || in_array($norm, array_values(self::HEADER_ALIASES), true))) {
                    $matchedCount++;
                    if ($norm === 'title' || (self::HEADER_ALIASES[$norm] ?? '') === 'title') {
                        $hasTitleHeader = true;
                    }
                }
            }

            $score = $matchedCount + ($hasTitleHeader ? 10 : 0);
            if ($score > $bestHeaderCount) {
                $bestHeaderCount = $score;
                $bestHeaderRowIndex = $i;
            }
        }

        // Slice rows starting from detected header row
        $rawRows = array_slice($rawRows, $bestHeaderRowIndex);
        $rawHeaders = array_shift($rawRows) ?? [];

        $headers = [];
        $hasValidHeader = false;

        foreach ($rawHeaders as $index => $rawHeader) {
            $normalized = $this->normalizeHeader((string) $rawHeader);
            $headers[$index] = $normalized;
            if ($normalized !== '') {
                $hasValidHeader = true;
            }
        }

        if (!$hasValidHeader) {
            throw new \RuntimeException('No recognizable header row was detected. Please ensure your file includes column names like Title, Author, Accession No, etc.');
        }

        // Verify required header: 'title'
        $hasTitle = in_array('title', $headers, true);
        $missing = [];
        if (!$hasTitle) {
            $missing[] = 'title';
        }

        // Pre-fetch existing accession numbers for fast DB duplicate checks
        $candidateAccessions = [];
        $accessionColIndex = array_search('accession_no', $headers, true);

        if ($accessionColIndex !== false) {
            foreach ($rawRows as $r) {
                if (is_array($r)) {
                    $acc = trim((string) ($r[$accessionColIndex] ?? ''));
                    if ($acc !== '') {
                        $candidateAccessions[] = $acc;
                    }
                }
            }
        }

        $existingDbAccessions = [];
        if (!empty($candidateAccessions)) {
            $existingDbAccessions = Book::whereIn('accession_number', array_unique($candidateAccessions))
                ->pluck('accession_number')
                ->mapWithKeys(fn ($acc) => [strtolower($acc) => true])
                ->all();
        }

        return DB::transaction(function () use (
            $headers,
            $missing,
            $originalName,
            $path,
            $rawRows,
            $userId,
            $options,
            $existingDbAccessions
        ): CatalogImport {
            $import = CatalogImport::create([
                'uploaded_by' => $userId,
                'source_file_name' => $originalName,
                'source_file_path' => $path,
                'source_type' => strtolower(pathinfo($originalName, PATHINFO_EXTENSION)),
                'status' => 'reviewing',
                'total_rows' => 0,
                'summary' => [
                    'headers' => array_values(array_filter($headers)),
                    'missing_headers' => $missing,
                    'options' => $options,
                ],
            ]);

            $pendingRows = [];
            $timestamp = now();
            $totalRows = 0;
            $validCount = 0;
            $invalidCount = 0;
            $seenAccessions = [];

            foreach ($rawRows as $index => $row) {
                if (!is_array($row)) {
                    continue;
                }

                // Skip completely blank rows
                if (!array_filter($row, fn ($val) => trim((string) $val) !== '')) {
                    continue;
                }

                $data = [];
                foreach ($headers as $column => $header) {
                    if ($header !== '') {
                        $data[$header] = trim((string) ($row[$column] ?? ''));
                    }
                }

                // Apply defaults from options if missing on row
                if (empty($data['material_type']) && !empty($options['default_material_type'])) {
                    $data['material_type'] = $options['default_material_type'];
                }
                if (empty($data['sublocation']) && !empty($options['default_collection'])) {
                    $data['sublocation'] = $options['default_collection'];
                }

                $errors = $missing
                    ? ['Missing required column: ' . implode(', ', $missing) . '.']
                    : $this->validateRow($data, $options, $seenAccessions, $existingDbAccessions, $index + 2);

                $status = empty($errors) ? 'pending' : 'invalid';
                if ($status === 'pending') {
                    $validCount++;
                } else {
                    $invalidCount++;
                }

                $pendingRows[] = [
                    'catalog_import_id' => $import->id,
                    'row_number' => $index + 2,
                    'raw_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    'status' => $status,
                    'errors' => !empty($errors) ? json_encode($errors, JSON_UNESCAPED_UNICODE) : null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                $totalRows++;

                if (count($pendingRows) >= 500) {
                    CatalogImportRow::insert($pendingRows);
                    $pendingRows = [];
                }
            }

            if (!empty($pendingRows)) {
                CatalogImportRow::insert($pendingRows);
            }

            $import->update([
                'total_rows' => $totalRows,
                'summary' => array_merge($import->summary ?? [], [
                    'valid_count' => $validCount,
                    'invalid_count' => $invalidCount,
                ]),
            ]);

            return $import->fresh();
        });
    }

    /**
     * Fast, lightweight native CSV reader with auto-delimiter detection and UTF-8 conversion.
     */
    private function readCsvRows(string $path): array
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException('Failed to read uploaded file.');
        }

        // Strip UTF-8 BOM
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // Convert non-UTF8 encoding if necessary
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        // Detect delimiter from first 4KB
        $sample = substr($content, 0, 4096);
        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($sample, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delim;
            }
        }

        $rows = [];
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        while (($row = fgetcsv($stream, 0, $bestDelimiter, '"', '\\')) !== false) {
            $rows[] = $row;
        }

        fclose($stream);
        unset($content);

        return $rows;
    }

    /**
     * Commit all valid (status = pending) rows into the catalog.
     */
    public function commit(CatalogImport $import): CatalogImport
    {
        @set_time_limit(0);

        $import->update(['status' => 'importing', 'imported_rows' => 0, 'failed_rows' => 0]);

        $options = $import->summary['options'] ?? [];
        $prefix = $options['accession_prefix'] ?? ('PGPC-' . date('Y') . '-');
        $startNum = (int) ($options['start_accession_number'] ?? 1001);
        $assignedInBatch = [];
        $context = $this->createCatalogContext($options);

        $processedCount = 0;
        $importedCount = 0;
        $failedCount = 0;

        foreach ($import->rows()->where('status', 'pending')->cursor() as $row) {
            try {
                DB::transaction(function () use ($row, $options, $prefix, &$startNum, &$assignedInBatch, &$context): void {
                    $data = is_array($row->raw_data) ? $row->raw_data : json_decode($row->raw_data, true);

                    // Auto-generate accession number if blank
                    if (empty($data['accession_no'])) {
                        $data['accession_no'] = $this->generateUniqueAccessionNumber($prefix, $startNum, $assignedInBatch);
                    }
                    $assignedInBatch[strtolower($data['accession_no'])] = true;

                    [$bookData, $bookDetail, $book] = $this->catalog($data, $options, $context);

                    $row->update([
                        'status' => 'imported',
                        'raw_data' => $data,
                        'book_data_id' => $bookData->id,
                        'book_detail_id' => $bookDetail->id,
                        'book_id' => $book->id,
                        'errors' => null,
                    ]);
                });
                $importedCount++;
            } catch (Throwable $exception) {
                $row->update([
                    'status' => 'failed',
                    'errors' => [$exception->getMessage()],
                ]);
                $failedCount++;
            }

            $processedCount++;
            if ($processedCount % 25 === 0) {
                $import->update([
                    'imported_rows' => $importedCount,
                    'failed_rows' => $failedCount,
                ]);
            }
        }

        $import->update([
            'status' => 'completed',
            'imported_rows' => $importedCount,
            'failed_rows' => $failedCount,
        ]);

        return $import->fresh('rows');
    }

    /**
     * Create an in-memory lookup cache to drastically accelerate batch imports.
     */
    private function createCatalogContext(array $options = []): array
    {
        $bookType = BookType::whereRaw('LOWER(type) = ?', ['book'])->first()
            ?? BookType::first()
            ?? BookType::create(['type' => 'Book']);

        $condition = BookCondition::whereRaw('LOWER(status) = ?', ['good'])->first()
            ?? BookCondition::first()
            ?? BookCondition::create(['status' => 'Good']);

        return [
            'book_type' => $bookType,
            'condition' => $condition,
            'languages' => [],
            'publishers' => [],
            'material_types' => [],
            'categories' => [],
            'subjects' => [],
            'authors' => [],
        ];
    }

    /**
     * Catalog a single validated row into BookData, BookDetail, and physical Book copy.
     */
    private function catalog(array $data, array $options = [], array &$context = []): array
    {
        $accessionNumber = trim($data['accession_no']);

        if (Book::where('accession_number', $accessionNumber)->exists()) {
            throw new \RuntimeException("Accession number already exists: {$accessionNumber}");
        }

        // 1. Language
        $langName = !empty($data['language']) ? trim($data['language']) : 'English';
        $cleanLang = mb_substr($langName, 0, 100);
        $langKey = mb_strtolower($cleanLang);
        if (!isset($context['languages'][$langKey])) {
            $context['languages'][$langKey] = Language::firstOrCreate(['lang' => $cleanLang]);
        }
        $language = $context['languages'][$langKey];

        // 2. Publisher
        $pubName = !empty($data['publisher']) ? trim($data['publisher']) : 'Unknown Publisher';
        $pubAddress = !empty($data['publisher_address']) ? trim($data['publisher_address']) : null;
        $cleanPub = mb_substr($pubName, 0, 255);
        $pubKey = mb_strtolower($cleanPub);
        if (!isset($context['publishers'][$pubKey])) {
            $publisher = Publisher::firstOrCreate(
                ['name' => $cleanPub],
                ['address' => $pubAddress ? mb_substr($pubAddress, 0, 255) : null]
            );
            if ($pubAddress && empty($publisher->address)) {
                $publisher->update(['address' => mb_substr($pubAddress, 0, 255)]);
            }
            $context['publishers'][$pubKey] = $publisher;
        }
        $publisher = $context['publishers'][$pubKey];

        // 3. BookData
        $title = mb_substr(trim($data['title']), 0, 255);
        $subtitle = !empty($data['subtitle']) ? mb_substr(trim($data['subtitle']), 0, 255) : null;
        $desc = !empty($data['description']) ? trim($data['description']) : null;
        $series = !empty($data['series_title']) ? mb_substr(trim($data['series_title']), 0, 255) : null;
        $note = !empty($data['note']) ? trim($data['note']) : null;
        $pubYear = $this->year($data['publication_year'] ?? null);

        $bookData = BookData::firstOrCreate(
            ['book_title' => $title],
            [
                'language_id' => $language->id,
                'subtitle' => $subtitle,
                'description' => $desc,
                'series_title' => $series,
                'note' => $note,
                'copyright_year' => $pubYear,
            ]
        );

        if (!empty($data['author'])) {
            $authorIds = $this->authorIds($data['author'], $context);
            if (!empty($authorIds)) {
                $bookData->authors()->syncWithoutDetaching($authorIds);
            }
        }
        if (!empty($data['categories'])) {
            $catIds = $this->namedIds(Category::class, $data['categories'], $context);
            if (!empty($catIds)) {
                $bookData->categories()->syncWithoutDetaching($catIds);
            }
        }
        if (!empty($data['subject'])) {
            $subIds = $this->namedIds(Subject::class, $data['subject'], $context);
            if (!empty($subIds)) {
                $bookData->subjects()->syncWithoutDetaching($subIds);
            }
        }

        // 4. Material Type
        $matName = !empty($data['material_type']) ? trim($data['material_type']) : ($options['default_material_type'] ?? 'Book');
        $matKey = mb_strtolower($matName);
        if (!isset($context['material_types'][$matKey])) {
            $code = Str::upper(Str::slug($matName, '_')) ?: 'BOOK';
            $materialType = MaterialType::where('code', $code)->orWhere('name', $matName)->first();
            if (!$materialType) {
                $materialType = MaterialType::create([
                    'name' => mb_substr($matName, 0, 100),
                    'code' => mb_substr($code, 0, 50),
                    'is_borrowable' => true,
                ]);
            }
            $context['material_types'][$matKey] = $materialType;
        }
        $materialType = $context['material_types'][$matKey];

        // 5. BookType (reused from context)
        $bookType = $context['book_type'] ?? (BookType::whereRaw('LOWER(type) = ?', ['book'])->first() ?? BookType::first());

        // 6. BookDetail
        $cleanPages = null;
        if (!empty($data['pages'])) {
            $digits = preg_replace('/[^0-9]/', '', (string) $data['pages']);
            $cleanPages = $digits !== '' ? (int) $digits : null;
        }

        $callNum = !empty($data['call_number']) ? trim($data['call_number']) : 'Unclassified';

        $bookDetail = BookDetail::firstOrCreate([
            'book_data_id' => $bookData->id,
            'isbn' => !empty($data['isbn']) ? mb_substr(trim($data['isbn']), 0, 50) : null,
            'call_number' => mb_substr($callNum, 0, 255),
        ], [
            'issn' => !empty($data['issn']) ? mb_substr(trim($data['issn']), 0, 50) : null,
            'lccn' => !empty($data['lccn']) ? mb_substr(trim($data['lccn']), 0, 50) : null,
            'publication_year' => $pubYear,
            'copyright_year' => $pubYear,
            'edition' => !empty($data['edition']) ? mb_substr(trim($data['edition']), 0, 100) : null,
            'pages' => $cleanPages,
            'format' => mb_substr($matName, 0, 50),
            'classification' => mb_substr($callNum, 0, 255),
            'book_type_id' => $bookType->id,
            'material_type_id' => $materialType->id,
            'publisher_id' => $publisher->id,
        ]);

        // 7. Book (Physical copy)
        $condition = $context['condition'] ?? (BookCondition::whereRaw('LOWER(status) = ?', ['good'])->first() ?? BookCondition::first());

        $barcode = 'BC-' . preg_replace('/[^a-zA-Z0-9]/', '', $accessionNumber);
        $location = !empty($data['sublocation']) ? trim($data['sublocation']) : ($options['default_collection'] ?? 'General Circulation');

        $book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => $condition->id,
            'accession_number' => $accessionNumber,
            'code' => $barcode,
            'location' => mb_substr($location, 0, 255),
            'status' => 'available',
            'date_acquired' => now()->toDateString(),
        ]);

        $book->languages()->syncWithoutDetaching([$language->id]);

        return [$bookData, $bookDetail, $book];
    }

    /**
     * Validate an individual row's parsed values.
     */
    private function validateRow(
        array $data,
        array $options,
        array &$seenAccessions,
        array $existingDbAccessions,
        int $rowNumber
    ): array {
        $errors = [];

        // Title is strictly required
        if (empty($data['title'])) {
            $errors[] = 'Title is required.';
        }

        // Accession number validation
        $acc = !empty($data['accession_no']) ? trim($data['accession_no']) : '';
        $autoGenerate = (bool) ($options['auto_generate_accession'] ?? true);

        if ($acc !== '') {
            $lowerAcc = strtolower($acc);

            // Duplicate in spreadsheet?
            if (isset($seenAccessions[$lowerAcc])) {
                $errors[] = "Duplicate accession number '{$acc}' (first seen on row {$seenAccessions[$lowerAcc]}).";
            } else {
                $seenAccessions[$lowerAcc] = $rowNumber;
            }

            // Already in database?
            if (isset($existingDbAccessions[$lowerAcc])) {
                $errors[] = "Accession number '{$acc}' already exists in library catalog.";
            }
        } elseif (!$autoGenerate) {
            $errors[] = 'Accession number is missing and auto-generation is disabled.';
        }

        return $errors;
    }

    /**
     * Normalize a header string using alias dictionary.
     */
    public function normalizeHeader(string $header): string
    {
        // 1. Remove UTF-8 BOM, zero-width spaces, quotes, and newlines
        $clean = trim(str_replace(["\xEF\xBB\xBF", "\u{FEFF}", '"', "'", "\r", "\n"], '', $header));
        if ($clean === '') {
            return '';
        }

        // 2. Strip common suffix tags like (s), [s], (optional), (required)
        $clean = preg_replace('/\s*[\(\[](s|optional|required)[\)\]]/i', '', $clean);

        // 3. Remove symbols like #, ., :, *, ?, !, (), [], {}
        $clean = str_replace(['#', '.', ':', '*', '?', '!', '(', ')', '[', ']', '{', '}'], '', $clean);

        // 4. Normalize separators (slashes, hyphens, spaces, underscores) to a single underscore
        $snake = strtolower(trim(preg_replace('/[\/\s\-_]+/', '_', $clean), '_'));

        // 5. Look up in aliases dictionary
        return self::HEADER_ALIASES[$snake]
            ?? self::HEADER_ALIASES[str_replace('_', '', $snake)]
            ?? $snake;
    }

    /**
     * Parse and resolve Author models from semicolon or comma separated string.
     */
    private function authorIds(string $authors, array &$context = []): array
    {
        // Split by semicolon first; if none, check if multiple authors are separated by commas
        $parts = str_contains($authors, ';') ? explode(';', $authors) : [$authors];

        return collect($parts)
            ->map(function (string $author) use (&$context) {
                $author = trim($author);
                if ($author === '') {
                    return null;
                }

                $key = mb_strtolower($author);
                if (isset($context['authors'][$key])) {
                    return $context['authors'][$key];
                }

                [$lastName, $firstName] = str_contains($author, ',')
                    ? array_map('trim', explode(',', $author, 2))
                    : [Str::afterLast($author, ' '), Str::beforeLast($author, ' ')];

                $resolvedFirst = $firstName ?: ($lastName ?: 'Unknown');
                $resolvedLast = $lastName ?: ($firstName ?: 'Author');

                $authorId = Author::firstOrCreate([
                    'first_name' => mb_substr($resolvedFirst, 0, 100),
                    'last_name' => mb_substr($resolvedLast, 0, 100),
                ])->id;

                if (isset($context['authors'])) {
                    $context['authors'][$key] = $authorId;
                }

                return $authorId;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Parse and resolve named model IDs (Category, Subject) from semicolon or comma delimited string.
     */
    private function namedIds(string $model, string $names, array &$context = []): array
    {
        $cacheGroup = $model === Category::class ? 'categories' : 'subjects';

        return collect(preg_split('/[;,]/', $names))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->map(function (string $name) use ($model, $cacheGroup, &$context) {
                $cleanName = mb_substr($name, 0, 100);
                $key = mb_strtolower($cleanName);

                if (isset($context[$cacheGroup][$key])) {
                    return $context[$cacheGroup][$key];
                }

                if ($model === Category::class) {
                    $slug = Str::upper(Str::slug($cleanName, '_')) ?: 'CAT';
                    $id = Category::firstOrCreate(
                        ['name' => $cleanName],
                        ['code' => mb_substr($slug, 0, 50)]
                    )->id;
                } else {
                    $id = $model::firstOrCreate(['name' => $cleanName])->id;
                }

                if (isset($context[$cacheGroup])) {
                    $context[$cacheGroup][$key] = $id;
                }

                return $id;
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Extract 4-digit publication year.
     */
    public function year(?string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (preg_match('/\b(1[89]\d{2}|20\d{2})\b/', (string) $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Generate a unique sequential accession number not present in the catalog or current batch.
     */
    private function generateUniqueAccessionNumber(string $prefix, int &$counter, array &$assignedInBatch = []): string
    {
        while (true) {
            $candidate = $prefix . Str::padLeft($counter, 4, '0');
            $counter++;
            $lower = strtolower($candidate);

            if (!isset($assignedInBatch[$lower]) && !Book::where('accession_number', $candidate)->exists()) {
                $assignedInBatch[$lower] = true;
                return $candidate;
            }
        }
    }
}
