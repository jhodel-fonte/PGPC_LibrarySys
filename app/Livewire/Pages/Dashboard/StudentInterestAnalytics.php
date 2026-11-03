<?php

namespace App\Livewire\Pages\Dashboard;

use App\Services\EmployeeRoleService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'AI Analytics', 'subpage' => 'Student interests'])]
class StudentInterestAnalytics extends Component
{
    public function mount(): void
    {
        abort_unless(EmployeeRoleService::canViewReports(), 403);
    }

    public function render()
    {
        $history = DB::table('user_histories as h')
            ->join('accounts as a', 'a.id', '=', 'h.user_id')
            ->join('students as s', 's.account_id', '=', 'a.id')
            ->whereNull('a.deleted_at')->whereNull('s.deleted_at')
            ->where('h.is_seeded', false)->whereIn('h.rating', [1, 2]);

        $students = (clone $history)->distinct()->count('h.user_id');
        $borrowers = (clone $history)->where('h.rating', 2)->distinct()->count('h.user_id');
        $viewOnly = (clone $history)->where('h.rating', 1)->count();
        $borrowed = (clone $history)->where('h.rating', 2)->count();
        $books = (clone $history)->whereNotNull('h.isbn')->where('h.isbn', '<>', '')
            ->distinct()->count(DB::raw("UPPER(REPLACE(REPLACE(TRIM(h.isbn), '-', ''), ' ', ''))"));

        $catalogHistory = (clone $history)
            ->join('book_details as d', DB::raw("UPPER(REPLACE(REPLACE(TRIM(h.isbn), '-', ''), ' ', ''))"), '=', DB::raw("UPPER(REPLACE(REPLACE(TRIM(d.isbn), '-', ''), ' ', ''))"))
            ->join('book_datas as b', 'b.id', '=', 'd.book_data_id')
            ->whereNull('d.deleted_at')->whereNull('b.deleted_at')
            ->whereIn('d.catalog_kind', ['book', 'magazine', 'journal']);

        $categories = (clone $catalogHistory)
            ->join('book_data_category as bc', 'bc.book_data_id', '=', 'b.id')
            ->join('categories as c', 'c.id', '=', 'bc.category_id')
            ->whereNull('c.deleted_at')
            ->select('c.name')->selectRaw('COUNT(DISTINCT h.user_id) as students')
            ->groupBy('c.name')->havingRaw('COUNT(DISTINCT h.user_id) >= 3')
            ->orderByDesc('students')->orderBy('c.name')->limit(8)->get();

        $titles = (clone $catalogHistory)
            ->select('b.book_title')->selectRaw('COUNT(DISTINCT h.user_id) as students')
            ->groupBy('b.book_title')->havingRaw('COUNT(DISTINCT h.user_id) >= 3')
            ->orderByDesc('students')->orderBy('b.book_title')->limit(8)->get();

        return view('livewire.pages.dashboard.student-interest-analytics', compact('students', 'borrowers', 'books', 'viewOnly', 'borrowed', 'categories', 'titles'));
    }
}
