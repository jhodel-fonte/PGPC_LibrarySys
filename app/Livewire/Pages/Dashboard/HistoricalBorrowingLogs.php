<?php

namespace App\Livewire\Pages\Dashboard;

use App\Services\EmployeeRoleService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', ['title' => 'Historical Borrowing Log', 'activepageRoute' => 'admin.circulation-desk.index'])]
class HistoricalBorrowingLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        abort_unless(EmployeeRoleService::canManageCirculation(), 403);
        $logs = DB::table('historical_borrowing_logs')
            ->when(trim($this->search) !== '', fn ($query) => $query->where('accession_number', 'like', '%'.trim($this->search).'%'))
            ->orderByDesc('borrowed_date')->orderByDesc('id')->paginate(25);

        return view('livewire.pages.dashboard.historical-borrowing-logs', compact('logs'));
    }
}
