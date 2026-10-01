<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    /**
     * Search authors using PostgreSQL Trigram (GIN) indexed search.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('query', ''));
        
        // Select only required columns to minimize I/O overhead
        $query = Author::query()->select(['id', 'first_name', 'last_name']);

        if ($q !== '') {
            $tokens = array_filter(preg_split('/\s+/', $q));

            $query->where(function ($qb) use ($q, $tokens) {
                // 1. Direct match on indexed full name ((first_name || ' ' || last_name) gin_trgm_ops)
                $qb->whereRaw("(first_name || ' ' || last_name) ILIKE ?", ["%{$q}%"]);

                // 2. Tokenized match hitting idx_authors_first_name_trgm and idx_authors_last_name_trgm
                if (count($tokens) > 1) {
                    $qb->orWhere(function ($sub) use ($tokens) {
                        foreach ($tokens as $token) {
                            $sub->where(function ($tokenSub) use ($token) {
                                $tokenSub->where('first_name', 'ILIKE', "%{$token}%")
                                         ->orWhere('last_name', 'ILIKE', "%{$token}%");
                            });
                        }
                    });
                }
            });

            // Relevance ordering: Prefix matches first, followed by alphabetical order
            if (DB::getDriverName() === 'pgsql') {
                $prefixPattern = "{$q}%";
                $query->orderByRaw("
                    CASE 
                        WHEN first_name ILIKE ? THEN 1
                        WHEN last_name ILIKE ? THEN 2
                        WHEN (first_name || ' ' || last_name) ILIKE ? THEN 3
                        ELSE 4
                    END,
                    first_name ASC
                ", [$prefixPattern, $prefixPattern, $prefixPattern]);
            } else {
                $query->orderBy('first_name');
            }
        } else {
            $query->orderBy('first_name');
        }

        $authors = $query->limit(10)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => trim($a->first_name . ' ' . $a->last_name),
                    'first_name' => $a->first_name,
                    'last_name' => $a->last_name,
                ];
            });

        return response()->json($authors);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuthorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Author $author)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuthorRequest $request, Author $author)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        //
    }
}
