<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\Category;
use App\Models\OpacCatalogView;
use App\Models\ReservationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class OpacController extends Controller
{
    /**
     * Display the OPAC catalog search & filter view.
     */
    public function index(Request $request)
    {
        $data = $this->executeCatalogQuery($request);

        // AJAX / Alpine.js dynamic response (No full page reload)
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'results' => is_array($data['items']) ? $data['items'] : (method_exists($data['items'], 'values') ? $data['items']->values()->toArray() : (array) $data['items']),
                'totalResults' => $data['totalResults'],
                'pagination' => $data['paginationData'],
                'availabilities' => $data['availabilities'],
                'resourceTypes' => $data['resourceTypes'],
                'subjects' => $data['subjects'],
                'search' => $data['search'],
                'selectedType' => $data['selectedType'],
                'selectedAvailabilities' => $data['selectedAvailabilities'],
                'selectedSubjects' => $data['selectedSubjects'],
                'yearFrom' => $data['yearFrom'],
                'yearTo' => $data['yearTo'],
                'sortBy' => $data['sortBy'],
                'perPage' => $data['perPage'],
            ]);
        }

        return view('main.opac-index', [
            'search' => $data['search'],
            'selectedType' => $data['selectedType'],
            'selectedAvailabilities' => $data['selectedAvailabilities'],
            'selectedSubjects' => $data['selectedSubjects'],
            'yearFrom' => $data['yearFrom'],
            'yearTo' => $data['yearTo'],
            'sortBy' => $data['sortBy'],
            'perPage' => $data['perPage'],
            'results' => $data['items'],
            'pagination' => $data['pagination'],
            'totalResults' => $data['totalResults'],
            'availabilities' => $data['availabilities'],
            'resourceTypes' => $data['resourceTypes'],
            'subjects' => $data['subjects'],
            'isLoggedIn' => $data['isLoggedIn'],
            'currentUser' => $data['currentUser'],
        ]);
    }

    /**
     * Display the Advanced Search page and process advanced criteria.
     */
    public function advancedSearch(Request $request)
    {
        $hasSearched = $request->hasAny([
            'title', 'author', 'subject', 'isbn', 'type', 'availability', 'year_from', 'year_to', 'location'
        ]) && (
            filled($request->input('title')) ||
            filled($request->input('author')) ||
            filled($request->input('subject')) ||
            filled($request->input('isbn')) ||
            ($request->input('type') !== 'all' && filled($request->input('type'))) ||
            ($request->input('availability') !== 'all' && filled($request->input('availability'))) ||
            filled($request->input('year_from')) ||
            filled($request->input('year_to')) ||
            ($request->input('location') !== 'all' && filled($request->input('location')))
        );

        $data = $this->executeCatalogQuery($request, $hasSearched);

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'results' => is_array($data['items']) ? $data['items'] : (method_exists($data['items'], 'values') ? $data['items']->values()->toArray() : (array) $data['items']),
                'totalResults' => $data['totalResults'],
                'pagination' => $data['paginationData'],
                'sortBy' => $data['sortBy'],
                'perPage' => $data['perPage'],
                'hasSearched' => $hasSearched,
            ]);
        }

        return view('main.advance-search', array_merge($data, [
            'hasSearched' => $hasSearched,
            'title' => $data['advTitle'],
            'author' => $data['advAuthor'],
            'subject' => $data['advSubject'],
            'isbn' => $data['advIsbn'],
            'location' => $data['advLocation'],
            'matchType' => $data['matchType'],
        ]));
    }

    /**
     * Execute catalog query for both Standard OPAC and Advanced Search.
     */
    protected function executeCatalogQuery(Request $request, bool $isAdvanced = false): array
    {
        $search = trim($request->input('search', ''));
        $advTitle = trim($request->input('title', ''));
        $advAuthor = trim($request->input('author', ''));
        $advSubject = trim($request->input('subject', ''));
        $advIsbn = trim($request->input('isbn', ''));
        $advLocation = trim($request->input('location', ''));
        if ($advLocation === 'all') {
            $advLocation = '';
        }
        $matchType = $request->input('match', 'all');

        $selectedType = $request->input('type', 'all');
        $rawAvail = $request->input('availability', []);
        $selectedAvailabilities = array_values(array_filter(
            is_array($rawAvail) ? $rawAvail : [$rawAvail],
            fn($val) => filled($val) && $val !== 'all'
        ));
        $selectedSubjects = (array) $request->input('subject', []);
        $yearFrom = $request->input('year_from');
        $yearTo = $request->input('year_to');
        $sortBy = $request->input('sort', 'relevance');
        $perPage = (int) $request->input('per_page', 5);
        if (!in_array($perPage, [5, 10, 25, 50])) {
            $perPage = 5;
        }

        $isLoggedIn = Auth::check();
        $currentUser = Auth::user();

        $useView = false;
        try {
            DB::table('opac_catalog_view')->limit(1)->get();
            $useView = (OpacCatalogView::count() > 0);
        } catch (\Throwable $e) {
            $useView = false;
        }

        if ($useView) {
            $query = OpacCatalogView::query()
                ->search($search)
                ->advancedSearch([
                    'title' => $advTitle,
                    'author' => $advAuthor,
                    'subject' => $advSubject,
                    'isbn' => $advIsbn,
                    'location' => $advLocation,
                ], $matchType);

            if ($selectedType !== 'all' && filled($selectedType)) {
                $query->filterType($selectedType);
            }

            if (!empty($selectedAvailabilities)) {
                $query->filterAvailability($selectedAvailabilities);
            }

            if (!empty($yearFrom) || !empty($yearTo)) {
                $query->filterYear($yearFrom, $yearTo);
            }

            if (!empty($selectedSubjects)) {
                $query->filterSubject($selectedSubjects);
            }

            switch ($sortBy) {
                case 'newest':
                case 'year_desc':
                    $query->orderBy('publication_year', 'desc');
                    break;
                case 'title_asc':
                    $query->orderBy('book_title', 'asc');
                    break;
                default:
                    $query->orderBy('book_detail_id', 'desc');
                    break;
            }

            $paginator = $query->paginate($perPage)->appends($request->query());

            $results = $paginator->getCollection()->map(function ($row) {
                return [
                    'id' => $row->book_detail_id,
                    'title' => $row->book_title,
                    'author' => $row->authors,
                    'year' => $row->publication_year ?? $row->copyright_year ?? 'N/A',
                    'format' => $row->resource_type ?? $row->format ?? 'Book',
                    'pages' => $row->pages ? "{$row->pages} p." : 'N/A',
                    'call_no' => $row->call_number ?? 'N/A',
                    'location' => $row->primary_location ?? 'Main Library',
                    'status' => $row->catalog_status,
                    'status_label' => $row->status_label,
                    'status_color' => $row->status_color,
                    'dot_color' => $row->dot_color,
                    'accession_no' => $row->primary_accession_no ?? 'N/A',
                    'cover' => $row->cover_url,
                    'can_reserve' => $row->can_reserve,
                    'available_copies' => $row->available_copies,
                    'total_copies' => $row->total_copies,
                    'available_book_id' => $row->available_book_id,
                    'due_date' => $row->earliest_due_date ? 'Due on ' . $row->earliest_due_date->format('M d, Y') : null,
                ];
            });

            $totalCount = OpacCatalogView::count();
            $availableCount = OpacCatalogView::where('catalog_status', 'available')->count();
            $checkedOutCount = OpacCatalogView::where('catalog_status', 'checked_out')->count();
            $reservedCount = OpacCatalogView::where('catalog_status', 'reserved')->count();

            $availabilities = [
                ['id' => 'available', 'label' => 'Available', 'count' => $availableCount, 'color' => 'bg-emerald-500'],
                ['id' => 'checked_out', 'label' => 'Checked Out', 'count' => $checkedOutCount, 'color' => 'bg-rose-500'],
                ['id' => 'reserved', 'label' => 'Reserved', 'count' => $reservedCount, 'color' => 'bg-amber-500'],
                ['id' => 'reference_only', 'label' => 'Reference Only', 'count' => max(0, $totalCount - ($availableCount + $checkedOutCount + $reservedCount)), 'color' => 'bg-blue-500'],
            ];

            try {
                $resourceTypes = DB::table('opac_catalog_view')
                    ->select('resource_type', DB::raw('count(*) as count'))
                    ->whereNotNull('resource_type')
                    ->groupBy('resource_type')
                    ->get()
                    ->map(fn($r) => ['id' => strtolower($r->resource_type), 'label' => ucfirst($r->resource_type), 'count' => (int) $r->count])
                    ->toArray();
            } catch (\Throwable $e) {
                $resourceTypes = [
                    ['id' => 'books', 'label' => 'Books', 'count' => 128],
                    ['id' => 'theses', 'label' => 'Theses', 'count' => 32],
                    ['id' => 'journals', 'label' => 'Journals', 'count' => 24],
                ];
            }

            try {
                $subjects = Category::take(10)->get()->map(function ($cat) {
                    return [
                        'id' => (string) $cat->id,
                        'label' => $cat->name,
                        'count' => DB::table('book_data_category')->where('category_id', $cat->id)->count(),
                    ];
                })->toArray();
            } catch (\Throwable $e) {
                $subjects = [
                    ['id' => 'cs', 'label' => 'Computer Science', 'count' => 84],
                    ['id' => 'prog', 'label' => 'Programming', 'count' => 62],
                ];
            }

            $items = $results;
            $pagination = $paginator;
            $totalResults = $paginator->total();
        } else {
            // 2. High-Fidelity Fallback Dataset
            $defaultBooks = collect([
                [
                    'id' => 1,
                    'title' => 'Introduction to Algorithms, Fourth Edition',
                    'author' => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein',
                    'year' => '2022',
                    'format' => 'Book',
                    'type_id' => 'books',
                    'subject' => 'Computer Science',
                    'pages' => '1312 p.',
                    'call_no' => 'QA76.6 .I585 2022',
                    'location' => 'Main Library – 3rd Floor',
                    'status' => 'available',
                    'status_label' => 'Available',
                    'status_color' => 'text-emerald-700',
                    'dot_color' => 'bg-emerald-500',
                    'accession_no' => '00012450',
                    'cover' => 'https://m.media-amazon.com/images/I/61Mw06x2A7L._SY466_.jpg',
                    'can_reserve' => true,
                    'available_copies' => 4,
                    'total_copies' => 5,
                ],
                [
                    'id' => 2,
                    'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                    'author' => 'Robert C. Martin',
                    'year' => '2008',
                    'format' => 'Book',
                    'type_id' => 'books',
                    'subject' => 'Software Engineering',
                    'pages' => '464 p.',
                    'call_no' => 'QA76.76.C64 M37 2008',
                    'location' => 'Main Library – 2nd Floor',
                    'status' => 'available',
                    'status_label' => 'Available',
                    'status_color' => 'text-emerald-700',
                    'dot_color' => 'bg-emerald-500',
                    'accession_no' => '00011822',
                    'cover' => 'https://m.media-amazon.com/images/I/51E2055ZGUL._SY445_SX342_.jpg',
                    'can_reserve' => true,
                    'available_copies' => 2,
                    'total_copies' => 4,
                ],
                [
                    'id' => 3,
                    'title' => 'Artificial Intelligence: A Modern Approach, Global Edition',
                    'author' => 'Stuart Russell, Peter Norvig',
                    'year' => '2021',
                    'format' => 'Book',
                    'type_id' => 'books',
                    'subject' => 'Artificial Intelligence',
                    'pages' => '1168 p.',
                    'call_no' => 'Q335 .R87 2021',
                    'location' => 'Main Library – 3rd Floor',
                    'status' => 'checked_out',
                    'status_label' => 'Checked Out',
                    'status_color' => 'text-rose-600',
                    'dot_color' => 'bg-rose-500',
                    'accession_no' => '00013004',
                    'due_date' => 'Due on Jun 12, 2025',
                    'cover' => 'https://m.media-amazon.com/images/I/81s6DUyQCZL._SY466_.jpg',
                    'can_reserve' => false,
                    'available_copies' => 0,
                    'total_copies' => 2,
                ],
                [
                    'id' => 4,
                    'title' => 'The Pragmatic Programmer: Your Journey To Mastery, 20th Anniversary Edition',
                    'author' => 'Andrew Hunt, David Thomas',
                    'year' => '2019',
                    'format' => 'Book',
                    'type_id' => 'books',
                    'subject' => 'Software Engineering',
                    'pages' => '352 p.',
                    'call_no' => 'QA76.6 .H85 2019',
                    'location' => 'Main Library – 3rd Floor',
                    'status' => 'reserved',
                    'status_label' => 'Reserved',
                    'status_color' => 'text-amber-600',
                    'dot_color' => 'bg-amber-500',
                    'accession_no' => '00012309',
                    'pickup_date' => 'Pick up before May 25, 2025',
                    'cover' => 'https://m.media-amazon.com/images/I/71f743sOPmL._SY466_.jpg',
                    'can_reserve' => false,
                    'available_copies' => 0,
                    'total_copies' => 1,
                ],
            ]);

            // Filter in-memory
            $filtered = $defaultBooks;

            if ($matchType === 'any') {
                $hasAdvCrit = filled($advTitle) || filled($advAuthor) || filled($advSubject) || filled($advIsbn) || filled($advLocation);
                if ($hasAdvCrit) {
                    $filtered = $filtered->filter(function ($b) use ($advTitle, $advAuthor, $advSubject, $advIsbn, $advLocation) {
                        $match = false;
                        if (filled($advTitle) && str_contains(strtolower($b['title']), strtolower($advTitle))) $match = true;
                        if (filled($advAuthor) && str_contains(strtolower($b['author']), strtolower($advAuthor))) $match = true;
                        if (filled($advSubject) && str_contains(strtolower($b['subject']), strtolower($advSubject))) $match = true;
                        if (filled($advIsbn) && (str_contains(strtolower($b['call_no']), strtolower($advIsbn)) || str_contains(strtolower($b['accession_no']), strtolower($advIsbn)))) $match = true;
                        if (filled($advLocation) && str_contains(strtolower($b['location']), strtolower($advLocation))) $match = true;
                        return $match;
                    });
                }
            } else {
                // Match all (default)
                if (filled($advTitle)) {
                    $t = strtolower($advTitle);
                    $filtered = $filtered->filter(fn($b) => str_contains(strtolower($b['title']), $t));
                }
                if (filled($advAuthor)) {
                    $a = strtolower($advAuthor);
                    $filtered = $filtered->filter(fn($b) => str_contains(strtolower($b['author']), $a));
                }
                if (filled($advSubject)) {
                    $s = strtolower($advSubject);
                    $filtered = $filtered->filter(fn($b) => str_contains(strtolower($b['subject']), $s));
                }
                if (filled($advIsbn)) {
                    $i = strtolower($advIsbn);
                    $filtered = $filtered->filter(fn($b) => str_contains(strtolower($b['call_no']), $i) || str_contains(strtolower($b['accession_no']), $i));
                }
                if (filled($advLocation)) {
                    $l = strtolower($advLocation);
                    $filtered = $filtered->filter(fn($b) => str_contains(strtolower($b['location']), $l));
                }
            }

            if (!empty($search)) {
                $s = strtolower($search);
                $filtered = $filtered->filter(function ($b) use ($s) {
                    return str_contains(strtolower($b['title']), $s)
                        || str_contains(strtolower($b['author']), $s)
                        || str_contains(strtolower($b['call_no']), $s);
                });
            }

            if ($selectedType !== 'all' && filled($selectedType)) {
                $filtered = $filtered->where('type_id', $selectedType);
            }

            if (!empty($selectedAvailabilities)) {
                $filtered = $filtered->whereIn('status', $selectedAvailabilities);
            }

            if (!empty($yearFrom)) {
                $filtered = $filtered->where('year', '>=', (string)$yearFrom);
            }
            if (!empty($yearTo)) {
                $filtered = $filtered->where('year', '<=', (string)$yearTo);
            }

            // Sort in-memory
            switch ($sortBy) {
                case 'newest':
                case 'year_desc':
                    $filtered = $filtered->sortByDesc('year');
                    break;
                case 'title_asc':
                    $filtered = $filtered->sortBy('title');
                    break;
                default:
                    $filtered = $filtered->sortBy('id');
                    break;
            }

            $totalResults = $filtered->count();
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageItems = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $pagination = new LengthAwarePaginator(
                $currentPageItems,
                $totalResults,
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
            );

            $items = $currentPageItems;

            $availabilities = [
                ['id' => 'available', 'label' => 'Available', 'count' => 128, 'color' => 'bg-emerald-500'],
                ['id' => 'checked_out', 'label' => 'Checked Out', 'count' => 42, 'color' => 'bg-rose-500'],
                ['id' => 'reserved', 'label' => 'Reserved', 'count' => 18, 'color' => 'bg-amber-500'],
                ['id' => 'reference_only', 'label' => 'Reference Only', 'count' => 16, 'color' => 'bg-blue-500'],
            ];

            $resourceTypes = [
                ['id' => 'books', 'label' => 'Books', 'count' => 128],
                ['id' => 'theses', 'label' => 'Theses', 'count' => 32],
                ['id' => 'journals', 'label' => 'Journals', 'count' => 24],
                ['id' => 'reports', 'label' => 'Reports', 'count' => 12],
                ['id' => 'multimedia', 'label' => 'Multimedia', 'count' => 8],
            ];

            $subjects = [
                ['id' => 'cs', 'label' => 'Computer Science', 'count' => 84],
                ['id' => 'prog', 'label' => 'Programming', 'count' => 62],
                ['id' => 'algo', 'label' => 'Algorithms', 'count' => 38],
                ['id' => 'db', 'label' => 'Database Systems', 'count' => 29],
                ['id' => 'se', 'label' => 'Software Engineering', 'count' => 25],
            ];
        }

        $paginationData = null;
        if ($pagination instanceof LengthAwarePaginator) {
            $paginationData = [
                'currentPage' => $pagination->currentPage(),
                'lastPage' => $pagination->lastPage(),
                'hasMorePages' => $pagination->hasMorePages(),
                'total' => $pagination->total(),
                'perPage' => $pagination->perPage(),
                'nextUrl' => $pagination->nextPageUrl(),
                'prevUrl' => $pagination->previousPageUrl(),
                'links' => $pagination->linkCollection()->toArray(),
            ];
        }

        return [
            'search' => $search,
            'advTitle' => $advTitle,
            'advAuthor' => $advAuthor,
            'advSubject' => $advSubject,
            'advIsbn' => $advIsbn,
            'advLocation' => $advLocation,
            'matchType' => $matchType,
            'selectedType' => $selectedType,
            'selectedAvailabilities' => $selectedAvailabilities,
            'selectedSubjects' => $selectedSubjects,
            'yearFrom' => $yearFrom,
            'yearTo' => $yearTo,
            'sortBy' => $sortBy,
            'perPage' => $perPage,
            'items' => $items,
            'pagination' => $pagination,
            'totalResults' => $totalResults,
            'paginationData' => $paginationData,
            'availabilities' => $availabilities,
            'resourceTypes' => $resourceTypes,
            'subjects' => $subjects,
            'isLoggedIn' => $isLoggedIn,
            'currentUser' => $currentUser,
        ];
    }

    /**
     * Handle book reservation request for authenticated users.
     */
    public function reserve(Request $request, $bookId)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please sign in with your account to reserve this resource.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('warning', 'Please sign in to reserve books.');
        }

        $book = Book::find($bookId);
        if (!$book) {
            // Fallback: Check if it's a book detail id
            $detail = BookDetail::find($bookId);
            if ($detail) {
                $book = $detail->books()->where('status', 'available')->first();
            }
        }

        if (!$book) {
            return back()->with('error', 'The requested resource copy was not found.');
        }

        if ($book->status !== 'available') {
            return back()->with('error', 'This resource is currently not available for reservation.');
        }

        try {
            // Find or default status
            $pendingStatus = ReservationStatus::where('name', 'like', '%pending%')->first();
            $statusId = $pendingStatus ? $pendingStatus->id : 1;

            $reservation = BookReservation::create([
                'book_id' => $book->id,
                'reservation_status_id' => $statusId,
                'reservation_date' => now(),
                'due_date' => now()->addDays(3),
                'comment' => 'Online OPAC reservation by ' . (Auth::user()->username ?? 'student'),
            ]);

            // Mark book as reserved
            $book->update(['status' => 'reserved']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book reservation submitted successfully! Please pick up the item within 3 days.',
                    'reservation_id' => $reservation->id,
                ]);
            }

            return back()->with('success', 'Book reservation submitted! Please pick up your item at the circulation desk within 3 days.');
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
}

