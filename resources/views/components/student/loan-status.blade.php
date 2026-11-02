@props(['loan'])

@php
    $due = $loan->due_date?->copy()->setTimezone('Asia/Manila');
    $today = now('Asia/Manila')->startOfDay();
    $days = $due ? (int) $today->diffInDays($due->copy()->startOfDay(), false) : null;
@endphp

@if (! $due)
    <span class="inline-flex rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-700">Due date unavailable</span>
@elseif ($due->isPast())
    <span class="inline-flex rounded-md border border-red-200 bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">Overdue</span>
@elseif ($days === 0)
    <span class="inline-flex rounded-md border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">Due today</span>
@else
    <span class="inline-flex rounded-md border {{ $days <= 2 ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }} px-2.5 py-1 text-xs font-bold">Due in {{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span>
@endif
