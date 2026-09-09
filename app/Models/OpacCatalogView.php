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
        if (empty($term) || !filled($term)) {
            return $query;
        }

        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';
        $escapedTerm = \App\Services\SanitizeInput::escapeLike(trim((string)$term));

        if ($escapedTerm === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($escapedTerm, $likeOp) {
            $q->where('book_title', $likeOp, "%{$escapedTerm}%")
              ->orWhere('subtitle', $likeOp, "%{$escapedTerm}%")
              ->orWhere('authors', $likeOp, "%{$escapedTerm}%")
              ->orWhere('isbn', $likeOp, "%{$escapedTerm}%")
              ->orWhere('call_number', $likeOp, "%{$escapedTerm}%")
              ->orWhere('description', $likeOp, "%{$escapedTerm}%");
        });
    }

    /**
     * Scope: Advanced search with field-specific matching.
     * Supports: title, author, subject, isbn, type, availability, year_from, year_to, location.
     * Mode: 'all' (AND: every active condition must match) or 'any' (OR: at least one active condition matches).
     */
    public function scopeAdvancedSearch(Builder $query, array $criteria, string $match = 'all'): Builder
    {
        $activeClauses = [];
        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';

        // 1. Title
        if (!empty($criteria['title']) && filled($criteria['title'])) {
            $val = \App\Services\SanitizeInput::escapeLike(trim((string)$criteria['title']));
            if ($val !== '') {
                $activeClauses[] = function (Builder $sub) use ($val, $likeOp) {
                    $sub->where('book_title', $likeOp, "%{$val}%")
                        ->orWhere('subtitle', $likeOp, "%{$val}%");
                };
            }
        }

        // 2. Author
        if (!empty($criteria['author']) && filled($criteria['author'])) {
            $val = \App\Services\SanitizeInput::escapeLike(trim((string)$criteria['author']));
            if ($val !== '') {
                $activeClauses[] = function (Builder $sub) use ($val, $likeOp) {
                    $sub->where('authors', $likeOp, "%{$val}%");
                };
            }
        }

        // 3. Subject / Keyword
        if (!empty($criteria['subject']) && filled($criteria['subject'])) {
            $val = \App\Services\SanitizeInput::escapeLike(trim((string)$criteria['subject']));
            if ($val !== '') {
                $activeClauses[] = function (Builder $sub) use ($val, $likeOp) {
                    $sub->where('categories', $likeOp, "%{$val}%")
                        ->orWhere('description', $likeOp, "%{$val}%");
                };
            }
        }

        // 4. ISBN / ISSN
        if (!empty($criteria['isbn']) && filled($criteria['isbn'])) {
            $val = \App\Services\SanitizeInput::escapeLike(trim((string)$criteria['isbn']));
            if ($val !== '') {
                $activeClauses[] = function (Builder $sub) use ($val, $likeOp) {
                    $sub->where('isbn', $likeOp, "%{$val}%")
                        ->orWhere('issn', $likeOp, "%{$val}%");
                };
            }
        }

        // 5. Resource Type
        if (!empty($criteria['type']) && filled($criteria['type']) && $criteria['type'] !== 'all') {
            $val = \App\Services\SanitizeInput::escapeLike(trim((string)$criteria['type']));
            if ($val !== '') {
                $activeClauses[] = function (Builder $sub) use ($val, $likeOp) {
                    $sub->where('resource_type', $likeOp, "%{$val}%")
                        ->orWhere('format', $likeOp, "%{$val}%");
                };
            }
        }

        // 6. Availability Status
        if (!empty($criteria['availability']) && $criteria['availability'] !== 'all') {
            $rawAvail = is_array($criteria['availability']) ? $criteria['availability'] : [$criteria['availability']];
            $validAvails = array_values(array_filter($rawAvail, fn($v) => filled($v) && $v !== 'all'));
            if (!empty($validAvails)) {
                $activeClauses[] = function (Builder $sub) use ($validAvails) {
                    $sub->whereIn('catalog_status', $validAvails);
                };
            }
        }

        // 7. Publication Year bounds
        $hasFrom = !empty($criteria['year_from']) && is_numeric($criteria['year_from']);
        $hasTo   = !empty($criteria['year_to']) && is_numeric($criteria['year_to']);
        if ($hasFrom && $hasTo) {
            $yFrom = (int) $criteria['year_from'];
            $yTo   = (int) $criteria['year_to'];
            $activeClauses[] = function (Builder $sub) use ($yFrom, $yTo) {
                $sub->whereBetween('publication_year', [min($yFrom, $yTo), max($yFrom, $yTo)]);
            };
        } elseif ($hasFrom) {
            $yFrom = (int) $criteria['year_from'];
            $activeClauses[] = function (Builder $sub) use ($yFrom) {
                $sub->where('publication_year', '>=', $yFrom);
            };
        } elseif ($hasTo) {
            $yTo = (int) $criteria['year_to'];
            $activeClauses[] = function (Builder $sub) use ($yTo) {
                $sub->where('publication_year', '<=', $yTo);
            };
        }

        // 8. Location
        if (!empty($criteria['location']) && filled($criteria['location']) && $criteria['location'] !== 'all') {
            $val = \App\Services\SanitizeInput::escapeLike(trim((string)$criteria['location']));
            if ($val !== '') {
                $activeClauses[] = function (Builder $sub) use ($val, $likeOp) {
                    $sub->where('primary_location', $likeOp, "%{$val}%");
                };
            }
        }

        if (empty($activeClauses)) {
            return $query;
        }

        $isOr = (strtolower($match) === 'any');

        return $query->where(function (Builder $q) use ($activeClauses, $isOr) {
            if ($isOr) {
                $first = true;
                foreach ($activeClauses as $clause) {
                    if ($first) {
                        $q->where($clause);
                        $first = false;
                    } else {
                        $q->orWhere($clause);
                    }
                }
            } else {
                foreach ($activeClauses as $clause) {
                    $q->where($clause);
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
        $escaped = \App\Services\SanitizeInput::escapeLike(trim((string)$type));

        return $query->where(function (Builder $q) use ($escaped, $likeOp) {
            $q->where('resource_type', $likeOp, "%{$escaped}%")
              ->orWhere('format', $likeOp, "%{$escaped}%");
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
        if (!empty($from) && is_numeric($from)) {
            $query->where('publication_year', '>=', (int) $from);
        }

        if (!empty($to) && is_numeric($to)) {
            $query->where('publication_year', '<=', (int) $to);
        }

        return $query;
    }

    /**
     * Scope: Filter by subject / category IDs or names.
     */
    public function scopeFilterSubject(Builder $query, array $subjects): Builder
    {
        $valid = array_values(array_filter($subjects, fn($s) => is_numeric($s) || (is_string($s) && filled($s))));
        if (empty($valid)) {
            return $query;
        }

        $isPgsql = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $likeOp = $isPgsql ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($valid, $isPgsql, $likeOp) {
            foreach ($valid as $subj) {
                if (is_numeric($subj)) {
                    $idStr = (string)(int)$subj;
                    if ($isPgsql) {
                        $q->orWhereRaw("? = ANY(string_to_array(category_ids, ','))", [$idStr]);
                    } else {
                        $q->orWhereRaw("FIND_IN_SET(?, category_ids)", [$idStr]);
                    }
                } else {
                    $escaped = \App\Services\SanitizeInput::escapeLike(trim((string)$subj));
                    $q->orWhere('categories', $likeOp, "%{$escaped}%");
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

