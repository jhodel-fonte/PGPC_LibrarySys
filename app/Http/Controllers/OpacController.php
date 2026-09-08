<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\Category;
use App\Models\OpacCatalogView;
use App\Models\ReservationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\DataNormalizationService;


class OpacController extends Controller
{
    /**
     * Display the OPAC catalog search & filter view.
     */
    public function index(Request $request)
    {
        $data = $this->executeCatalogQuery($request);

        // AJAX / Alpine.js dynamic response (no full page reload)
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'results'               => DataNormalizationService::normalizeItems($data['items']),
                'totalResults'          => $data['totalResults'],
                'pagination'            => $data['paginationData'],
                'availabilities'        => $data['availabilities'],
                'resourceTypes'         => $data['resourceTypes'],
                'subjects'              => $data['subjects'],
                'search'                => $data['search'],
                'selectedType'          => $data['selectedType'],
                'selectedAvailabilities' => $data['selectedAvailabilities'],
                'selectedSubjects'      => $data['selectedSubjects'],
                'yearFrom'              => $data['yearFrom'],
                'yearTo'                => $data['yearTo'],
                'sortBy'                => $data['sortBy'],
                'perPage'               => $data['perPage'],
            ]);
        }

        return view('main.opac-index', [
            'search'                 => $data['search'],
            'selectedType'           => $data['selectedType'],
            'selectedAvailabilities' => $data['selectedAvailabilities'],
            'selectedSubjects'       => $data['selectedSubjects'],
            'yearFrom'               => $data['yearFrom'],
            'yearTo'                 => $data['yearTo'],
            'sortBy'                 => $data['sortBy'],
            'perPage'                => $data['perPage'],
            'results'                => $data['items'],
            'pagination'             => $data['pagination'],
            'totalResults'           => $data['totalResults'],
            'availabilities'         => $data['availabilities'],
            'resourceTypes'          => $data['resourceTypes'],
            'subjects'               => $data['subjects'],
            'isLoggedIn'             => $data['isLoggedIn'],
            'currentUser'            => $data['currentUser'],
        ]);
    }

    /**
     * Display the Advanced Search page and process advanced criteria.
     */
    public function advancedSearch(Request $request)
    {
        // Bug fix #2: guard all inputs against arrays before using filled()
        $hasSearched = $request->hasAny([
            'title', 'author', 'subject', 'isbn', 'type', 'availability', 'year_from', 'year_to', 'location',
        ]) && (
            filled($this->inputString($request, 'title'))    ||
            filled($this->inputString($request, 'author'))   ||
            filled($this->inputString($request, 'subject'))  ||
            filled($this->inputString($request, 'isbn'))     ||
            ($this->inputString($request, 'type') !== 'all' && filled($this->inputString($request, 'type'))) ||
            ! empty(array_filter((array) $request->input('availability', []), fn($v) => filled($v) && $v !== 'all')) ||
            filled($request->input('year_from'))             ||
            filled($request->input('year_to'))               ||
            ($this->inputString($request, 'location') !== 'all' && filled($this->inputString($request, 'location')))
        );

        $data = $this->executeCatalogQuery($request);

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'results'      => DataNormalizationService::normalizeItems($data['items']),
                'totalResults' => $data['totalResults'],
                'pagination'   => $data['paginationData'],
                'sortBy'       => $data['sortBy'],
                'perPage'      => $data['perPage'],
                'hasSearched'  => $hasSearched,
            ]);
        }

        return view('main.advance-search', array_merge($data, [
            'hasSearched' => $hasSearched,
            'title'       => $data['advTitle'],
            'author'      => $data['advAuthor'],
            'subject'     => $data['advSubject'],
            'isbn'        => $data['advIsbn'],
            'location'    => $data['advLocation'],
            'matchType'   => $data['matchType'],
        ]));
    }

    /**
     * Handle book reservation request for authenticated users.
     *
     * Bug fix #7: $bookId from the OPAC result card is always a book_detail_id,
     * so look up BookDetail first, then find an available copy from it.
     * Bug fix #6: wrap the check + update in a DB transaction with a pessimistic
     * lock to prevent race conditions on concurrent reservation requests.
     */
    public function reserve(Request $request, $bookId)
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Please sign in with your account to reserve this resource.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('warning', 'Please sign in to reserve books.');
        }

        try {
            $reservation = DB::transaction(function () use ($bookId) {
                // Bug fix #7: OPAC passes book_detail_id — resolve an available copy via BookDetail
                $detail = BookDetail::find($bookId);
                $book   = $detail
                    ? $detail->books()->where('status', 'available')->lockForUpdate()->first()
                    : Book::where('id', $bookId)->where('status', 'available')->lockForUpdate()->first();

                if (! $book) {
                    throw new \RuntimeException('not_found');
                }

                // Bug fix #6: re-check inside the lock (status may have changed between requests)
                if ($book->status !== 'available') {
                    throw new \RuntimeException('not_available');
                }

                $pendingStatus = ReservationStatus::where('name', 'like', '%pending%')->first();

                $reservation = BookReservation::create([
                    'book_id'                => $book->id,
                    'reservation_status_id'  => $pendingStatus ? $pendingStatus->id : 1,
                    'reservation_date'       => now(),
                    'due_date'               => now()->addDays(3),
                    'comment'                => 'Online OPAC reservation by ' . (Auth::user()->username ?? 'student'),
                ]);

                $book->update(['status' => 'reserved']);

                return $reservation;
            });

            if (request()->expectsJson()) {
                return response()->json([
                    'success'        => true,
                    'message'        => 'Book reservation submitted successfully! Please pick up the item within 3 days.',
                    'reservation_id' => $reservation->id,
                ]);
            }

            return back()->with('success', 'Book reservation submitted! Please pick up your item at the circulation desk within 3 days.');

        } catch (\RuntimeException $e) {
            $message = $e->getMessage() === 'not_found'
                ? 'The requested resource copy was not found.'
                : 'This resource is currently not available for reservation.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);

        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation failed: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to submit reservation: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * PRIVATE HELPERS
     * ========================================================================= */

    /**
     * Safely read a request input as a trimmed string.
     * Returns '' when the value is an array (e.g. subject[]=1).
     *
     * Bug fix #1 / #2: prevents trim() TypeError when array params are passed.
     */
    private function inputString(Request $request, string $key): string
    {
        $value = $request->input($key);
        return is_string($value) ? trim($value) : '';
    }

    /**
     * Parse and sanitize all filter parameters from the request into a
     * structured array that the catalog query can safely consume.
     *
     * Separating this out keeps executeCatalogQuery() focused on DB work only.
     */
    private function parseFilters(Request $request): array
    {
        $search      = $this->inputString($request, 'search');
        $advTitle    = $this->inputString($request, 'title');
        $advAuthor   = $this->inputString($request, 'author');
        // Bug fix #1: subject as a text string (Advanced Search field)
        $advSubject  = $this->inputString($request, 'subject');
        $advIsbn     = $this->inputString($request, 'isbn');
        $advLocation = $this->inputString($request, 'location');
        if ($advLocation === 'all') {
            $advLocation = '';
        }

        $matchType    = $this->inputString($request, 'match') ?: 'all';
        $selectedType = $this->inputString($request, 'type') ?: 'all';

        $rawAvail             = $request->input('availability', []);
        $selectedAvailabilities = array_values(array_filter(
            is_array($rawAvail) ? $rawAvail : [$rawAvail],
            fn($val) => filled($val) && $val !== 'all'
        ));

        // Bug fix #1: subject as an array of category IDs (sidebar checkboxes)
        // Only treat them as integer IDs — reject non-numeric values so a plain
        // text "subject=Computer Science" from Advanced Search doesn't bleed here.
        $rawSubjects     = (array) $request->input('subject', []);
        $selectedSubjects = array_values(array_filter(
            $rawSubjects,
            fn($v) => is_numeric($v)   // only numeric IDs from sidebar checkboxes
        ));

        $yearFrom = is_numeric($request->input('year_from')) ? (int) $request->input('year_from') : null;
        $yearTo   = is_numeric($request->input('year_to'))   ? (int) $request->input('year_to')   : null;
        $sortBy   = $this->inputString($request, 'sort') ?: 'relevance';

        $perPage = (int) $request->input('per_page', 5);
        if (! in_array($perPage, [5, 10, 25, 50])) {
            $perPage = 5;
        }

        return compact(
            'search', 'advTitle', 'advAuthor', 'advSubject', 'advIsbn', 'advLocation',
            'matchType', 'selectedType', 'selectedAvailabilities', 'selectedSubjects',
            'yearFrom', 'yearTo', 'sortBy', 'perPage'
        );
    }

    /**
     * Run the full catalog query against the OPAC view.
     * Returns all data needed by both index() and advancedSearch().
     */
    protected function executeCatalogQuery(Request $request): array
    {
        $filters     = $this->parseFilters($request);
        $isLoggedIn  = Auth::check();
        $currentUser = Auth::user();

        // Bug fix #5: treat view as usable if the view is accessible, regardless
        // of whether it currently has rows (an empty catalog is still valid).
        $useView = false;
        try {
            DB::table('opac_catalog_view')->limit(1)->get();
            $useView = true;
        } catch (\Throwable $e) {
            $useView = false;
        }

        if ($useView) {
            [$items, $pagination, $totalResults] = $this->runViewQuery($request, $filters);
            $availabilities = $this->buildAvailabilityCounts();
            $resourceTypes  = $this->buildResourceTypes();
            $subjects       = $this->buildSubjects();
        } else {
            [$items, $pagination, $totalResults] = $this->emptyPaginatedResult($request, $filters['perPage']);
            $availabilities = $this->emptyAvailabilities();
            $resourceTypes  = [];
            $subjects       = [];
        }

        $paginationData = $this->buildPaginationData($pagination);

        return array_merge($filters, [
            'items'          => $items,
            'pagination'     => $pagination,
            'totalResults'   => $totalResults,
            'paginationData' => $paginationData,
            'availabilities' => $availabilities,
            'resourceTypes'  => $resourceTypes,
            'subjects'       => $subjects,
            'isLoggedIn'     => $isLoggedIn,
            'currentUser'    => $currentUser,
        ]);
    }

    /**
     * Query the opac_catalog_view with all active filters and return
     * [items collection, paginator, total count].
     */
    private function runViewQuery(Request $request, array $f): array
    {
        $query = OpacCatalogView::query()
            ->search($f['search'])
            ->advancedSearch([
                'title'    => $f['advTitle'],
                'author'   => $f['advAuthor'],
                'subject'  => $f['advSubject'],
                'isbn'     => $f['advIsbn'],
                'location' => $f['advLocation'],
            ], $f['matchType']);

        if ($f['selectedType'] !== 'all' && filled($f['selectedType'])) {
            $query->filterType($f['selectedType']);
        }

        if (! empty($f['selectedAvailabilities'])) {
            $query->filterAvailability($f['selectedAvailabilities']);
        }

        if (! empty($f['yearFrom']) || ! empty($f['yearTo'])) {
            $query->filterYear($f['yearFrom'], $f['yearTo']);
        }

        if (! empty($f['selectedSubjects'])) {
            $query->filterSubject($f['selectedSubjects']);
        }

        match ($f['sortBy']) {
            'newest', 'year_desc' => $query->orderBy('publication_year', 'desc'),
            'title_asc'           => $query->orderBy('book_title', 'asc'),
            default               => $query->orderBy('book_detail_id', 'desc'),
        };

        // Bug fix #4: use only safe scalar params for appends to avoid
        // double-encoding of array params like subject[0]=1
        $paginator = $query->paginate($f['perPage'])->appends(
            $request->except(['page'])  // let paginator manage the page param itself
        );

        $items = $paginator->getCollection()->map(fn($row) => $this->mapBookRow($row));

        return [$items, $paginator, $paginator->total()];
    }

    /**
     * Map a single OpacCatalogView row to the standard book array
     * consumed by the frontend.
     */
    private function mapBookRow(OpacCatalogView $row): array
    {
        return [
            'id'               => $row->book_detail_id,
            'title'            => $row->book_title,
            'author'           => $row->authors,
            'year'             => $row->publication_year ?? $row->copyright_year ?? 'N/A',
            'format'           => $row->resource_type ?? $row->format ?? 'Book',
            'pages'            => $row->pages ? "{$row->pages} p." : 'N/A',
            'call_no'          => $row->call_number ?? 'N/A',
            'location'         => $row->primary_location ?? 'Main Library',
            'status'           => $row->catalog_status,
            'status_label'     => $row->status_label,
            'status_color'     => $row->status_color,
            'dot_color'        => $row->dot_color,
            'accession_no'     => $row->primary_accession_no ?? 'N/A',
            'cover'            => $row->cover_url,
            'can_reserve'      => $row->can_reserve,
            'available_copies' => $row->available_copies,
            'total_copies'     => $row->total_copies,
            'available_book_id' => $row->available_book_id,
            'due_date'         => $row->earliest_due_date
                ? 'Due on ' . $row->earliest_due_date->format('M d, Y')
                : null,
        ];
    }

    /**
     * Build availability counts using a single aggregated query instead of
     * four separate COUNT queries.
     *
     * Bug fix #3: one query instead of four; counts reflect whole catalog (sidebar filter counts).
     */
    private function buildAvailabilityCounts(): array
    {
        try {
            $counts = DB::table('opac_catalog_view')
                ->selectRaw("catalog_status, count(*) as total")
                ->groupBy('catalog_status')
                ->pluck('total', 'catalog_status');
        } catch (\Throwable $e) {
            $counts = collect([]);
        }

        $available    = (int) ($counts['available']       ?? 0);
        $checkedOut   = (int) ($counts['checked_out']     ?? 0);
        $reserved     = (int) ($counts['reserved']        ?? 0);
        $refOnly      = (int) ($counts['reference_only']  ?? 0);

        return [
            ['id' => 'available',      'label' => 'Available',       'count' => $available,  'color' => 'bg-emerald-500'],
            ['id' => 'checked_out',    'label' => 'Checked Out',     'count' => $checkedOut, 'color' => 'bg-rose-500'],
            ['id' => 'reserved',       'label' => 'Reserved',        'count' => $reserved,   'color' => 'bg-amber-500'],
            ['id' => 'reference_only', 'label' => 'Reference Only',  'count' => $refOnly,    'color' => 'bg-blue-500'],
        ];
    }

    /**
     * Build resource type filter options from the catalog view.
     */
    private function buildResourceTypes(): array
    {
        try {
            return DB::table('opac_catalog_view')
                ->select('resource_type', DB::raw('count(*) as count'))
                ->whereNotNull('resource_type')
                ->groupBy('resource_type')
                ->get()
                ->map(fn($r) => [
                    'id'    => strtolower($r->resource_type),
                    'label' => ucfirst($r->resource_type),
                    'count' => (int) $r->count,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Build subject/category filter options from the categories table.
     */
    private function buildSubjects(): array
    {
        try {
            return Category::take(10)->get()->map(fn($cat) => [
                'id'    => (string) $cat->id,
                'label' => $cat->name,
                'count' => DB::table('book_data_category')->where('category_id', $cat->id)->count(),
            ])->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Return a zero-result paginated result when the catalog view is unavailable.
     */
    private function emptyPaginatedResult(Request $request, int $perPage): array
    {
        $items = collect([]);
        $pagination = new LengthAwarePaginator(
            $items, 0, $perPage, 1,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );
        return [$items, $pagination, 0];
    }

    /**
     * Return zero-count availability rows for the empty/error state.
     */
    private function emptyAvailabilities(): array
    {
        return [
            ['id' => 'available',      'label' => 'Available',      'count' => 0, 'color' => 'bg-emerald-500'],
            ['id' => 'checked_out',    'label' => 'Checked Out',    'count' => 0, 'color' => 'bg-rose-500'],
            ['id' => 'reserved',       'label' => 'Reserved',       'count' => 0, 'color' => 'bg-amber-500'],
            ['id' => 'reference_only', 'label' => 'Reference Only', 'count' => 0, 'color' => 'bg-blue-500'],
        ];
    }

    /**
     * Convert a LengthAwarePaginator to the flat array the frontend expects.
     */
    private function buildPaginationData(LengthAwarePaginator $pagination): array
    {
        return [
            'currentPage'  => $pagination->currentPage(),
            'lastPage'     => $pagination->lastPage(),
            'hasMorePages' => $pagination->hasMorePages(),
            'total'        => $pagination->total(),
            'perPage'      => $pagination->perPage(),
            'nextUrl'      => $pagination->nextPageUrl(),
            'prevUrl'      => $pagination->previousPageUrl(),
            'links'        => $pagination->linkCollection()->toArray(),
        ];
    }

}
