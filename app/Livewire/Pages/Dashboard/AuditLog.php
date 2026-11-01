<?php

namespace App\Livewire\Pages\Dashboard;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', ['title' => 'Logs'])]
class AuditLog extends Component
{
    use WithPagination;

    public string $actor = '';
    public string $action = '';
    public string $subject = '';
    public string $from = '';
    public string $to = '';

    public function updated($property): void
    {
        if (in_array($property, ['actor', 'action', 'subject', 'from', 'to'], true)) $this->resetPage();
    }

    public function render()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $logs = DB::table('audit_logs as entries')
            ->whereExists(function ($query) {
                $query->selectRaw('1')->from('audit_logs as matching')
                    ->where(function ($query) {
                        $query->whereColumn('matching.id', 'entries.id')
                            ->orWhere(function ($query) {
                                $query->whereNotNull('entries.group_id')->whereColumn('matching.group_id', 'entries.group_id');
                            });
                    })
                    ->when($this->actor !== '', fn ($q) => $q->where('matching.actor_name', 'like', '%' . $this->actor . '%'))
                    ->when($this->action !== '', fn ($q) => $q->where(fn ($q) => $q->where('matching.action', 'like', '%' . $this->action . '%')->orWhere('matching.group_action', 'like', '%' . $this->action . '%')))
                    ->when($this->subject !== '', fn ($q) => $q->where(fn ($q) => $q->where('matching.subject_type', 'like', '%' . $this->subject . '%')->orWhere('matching.subject_id', $this->subject)->orWhere('matching.context->Book', 'like', '%' . $this->subject . '%')->orWhere('matching.context->Accession number', 'like', '%' . $this->subject . '%')))
                    ->when($this->from !== '', fn ($q) => $q->where('matching.created_at', '>=', \Carbon\Carbon::parse($this->from, 'Asia/Manila')->startOfDay()->utc()))
                    ->when($this->to !== '', fn ($q) => $q->where('matching.created_at', '<', \Carbon\Carbon::parse($this->to, 'Asia/Manila')->addDay()->startOfDay()->utc()));
            })
            ->selectRaw('entries.group_id, MIN(entries.id) as first_id, MAX(entries.id) as last_id')
            ->groupBy('entries.group_id')
            ->groupByRaw('CASE WHEN entries.group_id IS NULL THEN entries.id ELSE 0 END')
            ->orderByDesc('last_id')->paginate(25);

        $groups = $logs->getCollection();
        $entries = DB::table('audit_logs')
            ->where(function ($query) use ($groups) {
                $query->whereIn('group_id', $groups->pluck('group_id')->filter()->all())
                    ->orWhereIn('id', $groups->whereNull('group_id')->pluck('first_id')->all());
            })->orderBy('id')->get();
        $logs->setCollection($groups->map(function ($group) use ($entries) {
            $items = $group->group_id ? $entries->where('group_id', $group->group_id) : $entries->where('id', $group->first_id);
            $first = $items->first();
            $context = $items->map(fn ($item) => json_decode($item->context ?? 'null', true) ?: [])->reduce(fn ($carry, $item) => $carry + $item, []);
            $sections = [];
            foreach ($items as $item) {
                $label = \Illuminate\Support\Str::headline(str_replace('.', ' ', $item->action)) . ' - ' . $item->subject_type . ' #' . $item->subject_id;
                $itemContext = json_decode($item->context ?? 'null', true);
                if ($itemContext) $sections[$label . ' (details, log #' . $item->id . ')'] = $itemContext;
                $sections[$label . ' (before, log #' . $item->id . ')'] = json_decode($item->old_values ?? 'null', true);
                $sections[$label . ' (after, log #' . $item->id . ')'] = json_decode($item->new_values ?? 'null', true);
            }
            $time = \Carbon\Carbon::parse($first->created_at, 'UTC')->setTimezone('Asia/Manila')->format('M j, Y g:i:s A');
            $action = $first->group_action ?: \Illuminate\Support\Str::headline(str_replace('.', ' ', $first->action));
            $record = $context['Book'] ?? ($first->subject_type . ' #' . $first->subject_id);
            $fields = ['Date and time' => $time . ' (Philippine time)', 'Actor' => $first->actor_name, 'Actor ID' => $first->actor_id, 'Action' => $action] + $context;
            return (object) ['time' => $time, 'actor' => $first->actor_name, 'action' => $action, 'record' => $record, 'details' => ['title' => 'Audit entry', 'fields' => array_filter($fields, fn ($value) => $value !== null), 'sections' => $sections]];
        }));

        return view('livewire.pages.dashboard.audit-log', compact('logs'));
    }
}
