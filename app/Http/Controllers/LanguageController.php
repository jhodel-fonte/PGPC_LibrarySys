<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    /**
     * Search languages using PostgreSQL ILIKE / Trigram indexed search.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('query', ''));
        
        $query = Language::query()->select(['id', 'lang']);

        if ($q !== '') {
            $query->where('lang', 'ILIKE', "%{$q}%");

            if (DB::getDriverName() === 'pgsql') {
                $prefixPattern = "{$q}%";
                $query->orderByRaw("
                    CASE 
                        WHEN lang ILIKE ? THEN 1
                        ELSE 2
                    END,
                    lang ASC
                ", [$prefixPattern]);
            } else {
                $query->orderBy('lang');
            }
        } else {
            $query->orderBy('lang');
        }

        $languages = $query->limit(20)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'name' => $l->lang,
                ];
            });

        return response()->json($languages);
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
    public function store(StoreLanguageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Language $language)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLanguageRequest $request, Language $language)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language)
    {
        //
    }
}
