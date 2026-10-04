<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Search categories using PostgreSQL ILIKE / Trigram indexed search.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('query', ''));

        if (mb_strlen($q) > 100) {
            return response()->json(['error' => 'Query is too long.'], 422);
        }
        
        $query = Category::query()->select(['id', 'name']);

        if ($q !== '') {
            $query->where('name', 'ILIKE', "%{$q}%");

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

        $categories = $query->limit(20)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                ];
            });

        return response()->json($categories);
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
    public function store(StoreCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
