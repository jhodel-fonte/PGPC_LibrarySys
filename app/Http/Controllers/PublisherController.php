<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use App\Http\Requests\StorePublisherRequest;
use App\Http\Requests\UpdatePublisherRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
{
    /**
     * Search publishers using PostgreSQL Trigram (GIN) indexed search.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('query', ''));
        
        // Select only required columns to minimize I/O overhead
        $query = Publisher::query()->select(['id', 'name']);

        if ($q !== '') {
            $query->where('name', 'ILIKE', "%{$q}%");

            // Relevance ordering: Prefix matches first with native bindings
            if (DB::getDriverName() === 'pgsql') {
                $prefixPattern = "{$q}%";
                $query->orderByRaw("
                    CASE 
                        WHEN name ILIKE ? THEN 1
                        ELSE 2
                    END,
                    name ASC
                ", [$prefixPattern]);
            } else {
                $query->orderBy('name');
            }
        } else {
            $query->orderBy('name');
        }

        $publishers = $query->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                ];
            });

        return response()->json($publishers);
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
    public function store(StorePublisherRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Publisher $publisher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Publisher $publisher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePublisherRequest $request, Publisher $publisher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publisher $publisher)
    {
        //
    }
}
