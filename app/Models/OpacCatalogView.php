<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpacCatalogView extends Model
{
    /**
     * The table associated with the model (database view).
     */
    protected $table = 'opac_catalog_view';

    /**
     * The primary key associated with the view.
     */
    protected $primaryKey = 'book_detail_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable (View is read-only).
     */
    protected $guarded = ['*'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'book_detail_id' => 'integer',
        'book_data_id' => 'integer',
        'book_type_id' => 'integer',
        'publisher_id' => 'integer',
        'language_id' => 'integer',
        'publication_year' => 'integer',
        'copyright_year' => 'integer',
        'pages' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'borrowed_copies' => 'integer',
        'reserved_copies' => 'integer',
        'available_book_id' => 'integer',
        'earliest_due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'can_reserve',
        'status_label',
        'status_color',
        'dot_color',
        'cover_url',
    ];

    /* =========================================================================
     * ACCESSORS
     * ========================================================================= */

    /**
     * Check if this book edition currently has available copies to reserve.
     */
    public function getCanReserveAttribute(): bool
    {
        return (int) $this->available_copies > 0;
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->catalog_status) {
            'available' => 'Available',
            'checked_out' => 'Checked Out',
            'reserved' => 'Reserved',
            'reference_only' => 'Reference Only',
            default => 'Available',
        };
    }

    /**
     * Text color class for availability badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->catalog_status) {
            'available' => 'text-emerald-700',
            'checked_out' => 'text-rose-600',
            'reserved' => 'text-amber-600',
            'reference_only' => 'text-blue-600',
            default => 'text-emerald-700',
        };
    }

    /**
     * Status dot background color class.
     */
    public function getDotColorAttribute(): string
    {
        return match ($this->catalog_status) {
            'available' => 'bg-emerald-500',
            'checked_out' => 'bg-rose-500',
            'reserved' => 'bg-amber-500',
            'reference_only' => 'bg-blue-500',
            default => 'bg-emerald-500',
        };
    }

    /**
     * Formatted cover image URL.
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (empty($this->cover_image)) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        return asset($this->cover_image);
    }

    /* =========================================================================
     * SCOPES
     * ========================================================================= */

    /**
     * Scope: Search query across title, authors, ISBN, Call Number, description.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($term, $likeOp) {
            $q->where('book_title', $likeOp, "%{$term}%")
              ->orWhere('subtitle', $likeOp, "%{$term}%")
              ->orWhere('authors', $likeOp, "%{$term}%")
              ->orWhere('isbn', $likeOp, "%{$term}%")
              ->orWhere('call_number', $likeOp, "%{$term}%")
              ->orWhere('description', $likeOp, "%{$term}%");
        });
    }

    /**
     * Scope: Advanced search with field-specific matching (title, author, subject, isbn, location).
     */
    public function scopeAdvancedSearch(Builder $query, array $criteria, string $match = 'all'): Builder
    {
        $activeCriteria = array_filter($criteria, fn($val) => filled($val) && $val !== 'all');
        if (empty($activeCriteria)) {
            return $query;
        }

        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';
        $isOr = ($match === 'any');

        return $query->where(function (Builder $q) use ($activeCriteria, $isOr, $likeOp) {
            $first = true;
            foreach ($activeCriteria as $field => $val) {
                $closure = function (Builder $sub) use ($field, $val, $likeOp) {
                    switch ($field) {
                        case 'title':
                            $sub->where('book_title', $likeOp, "%{$val}%")
                                ->orWhere('subtitle', $likeOp, "%{$val}%");
                            break;
                        case 'author':
                            $sub->where('authors', $likeOp, "%{$val}%");
                            break;
                        case 'subject':
                            $sub->where('categories', $likeOp, "%{$val}%")
                                ->orWhere('description', $likeOp, "%{$val}%");
                            break;
                        case 'isbn':
                            $sub->where('isbn', $likeOp, "%{$val}%")
                                ->orWhere('issn', $likeOp, "%{$val}%");
                            break;
                        case 'location':
                            $sub->where('primary_location', $likeOp, "%{$val}%");
                            break;
                    }
                };

                if ($first) {
                    $q->where($closure);
                    $first = false;
                } elseif ($isOr) {
                    $q->orWhere($closure);
                } else {
                    $q->where($closure);
                }
            }
        });
    }

    /**
     * Scope: Filter by resource type (e.g. 'books', 'theses', 'journals').
     */
    public function scopeFilterType(Builder $query, ?string $type): Builder
    {
        if (empty($type) || $type === 'all') {
            return $query;
        }

        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($type, $likeOp) {
            $q->where('resource_type', $likeOp, "%{$type}%")
              ->orWhere('format', $likeOp, "%{$type}%");
        });
    }

    /**
     * Scope: Filter by availability status list.
     */
    public function scopeFilterAvailability(Builder $query, array $availabilities): Builder
    {
        $valid = array_values(array_filter($availabilities, fn($val) => filled($val) && $val !== 'all'));
        if (empty($valid)) {
            return $query;
        }

        return $query->whereIn('catalog_status', $valid);
    }

    /**
     * Scope: Filter by publication year range.
     */
    public function scopeFilterYear(Builder $query, $from = null, $to = null): Builder
    {
        if (!empty($from)) {
            $query->where('publication_year', '>=', (int) $from);
        }

        if (!empty($to)) {
            $query->where('publication_year', '<=', (int) $to);
        }

        return $query;
    }

    /**
     * Scope: Filter by subject / category IDs or names.
     */
    public function scopeFilterSubject(Builder $query, array $subjects): Builder
    {
        if (empty($subjects)) {
            return $query;
        }

        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($subjects, $isPgsql, $likeOp) {
            foreach ($subjects as $subj) {
                if ($isPgsql) {
                    $q->orWhereRaw("? = ANY(string_to_array(category_ids, ','))", [(string)$subj])
                      ->orWhere('categories', $likeOp, "%{$subj}%");
                } else {
                    $q->orWhereRaw("FIND_IN_SET(?, category_ids)", [(string)$subj])
                      ->orWhere('categories', $likeOp, "%{$subj}%");
                }
            }
        });
    }

    /**
     * Scope: Find related books (same categories, same author, or same classification).
     */
    public function scopeRelated(Builder $query, int $excludeBookDetailId, ?string $categoryIds = null, ?string $author = null, ?string $classification = null): Builder
    {
        $query->where('book_detail_id', '!=', $excludeBookDetailId);

        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($categoryIds, $author, $classification, $isPgsql, $likeOp) {
            $conditionsApplied = false;

            if (!empty($categoryIds)) {
                $ids = explode(',', $categoryIds);
                foreach ($ids as $id) {
                    if (trim($id)) {
                        if ($isPgsql) {
                            $q->orWhereRaw("? = ANY(string_to_array(category_ids, ','))", [trim($id)]);
                        } else {
                            $q->orWhereRaw("FIND_IN_SET(?, category_ids)", [trim($id)]);
                        }
                        $conditionsApplied = true;
                    }
                }
            }

            if (!empty($author) && $author !== 'Unknown Author') {
                $q->orWhere('authors', $likeOp, "%{$author}%");
                $conditionsApplied = true;
            }

            if (!empty($classification)) {
                $q->orWhere('classification', '=', $classification);
                $conditionsApplied = true;
            }

            if (!$conditionsApplied) {
                $q->whereRaw('1=1');
            }
        });
    }

    /* =========================================================================
     * RELATIONSHIPS
     * ========================================================================= */

    public function bookDetail(): BelongsTo
    {
        return $this->belongsTo(BookDetail::class, 'book_detail_id');
    }

    public function bookData(): BelongsTo
    {
        return $this->belongsTo(BookData::class, 'book_data_id');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(Book::class, 'book_detail_id');
    }
}

