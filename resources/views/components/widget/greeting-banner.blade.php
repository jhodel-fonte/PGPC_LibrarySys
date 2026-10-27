@props([
    'checkInRoute' => null,
    'checkOutRoute' => null,
    'staffName' => null,
])
@php
    $routePrefix = \App\Services\EmployeeRoleService::getRoutePrefix();
    $resolvedCheckInRoute = $checkInRoute ?? route($routePrefix . 'circulation-desk.return');
    $resolvedCheckOutRoute = $checkOutRoute ?? route($routePrefix . 'circulation-desk.borrow');

    $canCheckIn = \App\Services\EmployeeRoleService::canReturn();
    $canCheckOut = \App\Services\EmployeeRoleService::canBorrow();
@endphp
<div class="pb-4 mr-3 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5 h-full shadow-sm">
    <!-- Background Decoration -->
    <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(105deg, transparent 0%, transparent 60%, rgba(255,255,255,.025) 60%);"></div>

    <!-- Left: Greeting Text -->
    <div class="relative z-10 flex-1 min-w-0">
        <p class="text-[#17357A]  text-[10px] sm:text-xs font-semibold uppercase tracking-[0.08em] mb-2 sm:mb-2.5">
            {{ strtoupper(now()->format('l, F j, Y')) }}
        </p>
        
        <h1 class="text-2xl sm:text-3xl lg:text-[32px] font-bold text-[#17357A]  leading-tight mb-1.5">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, 
            <span class="text-[#17357A]">{{ $staffName ?? 'User' }}</span>
        </h1>
        
        <p class="text-[#17357A]  text-[13px] sm:text-[14px] leading-relaxed">
            Here's an overview of the library today.
        </p>
    </div>
    
    {{-- <!-- Right: Quick Action Buttons -->
    @if($canCheckIn || $canCheckOut)
        <div class="relative z-10 flex flex-col sm:flex-row lg:flex-col xl:flex-row gap-2.5 sm:gap-3 shrink-0">
            @if($canCheckIn)
                <a href="{{ $resolvedCheckInRoute }}" wire:navigate class="h-10 sm:h-11 px-4 sm:px-5 bg-[#05BF8F] hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-xs whitespace-nowrap">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Return Books
                </a>
            @endif
            @if($canCheckOut)
                <a href="{{ $resolvedCheckOutRoute }}" wire:navigate class="h-10 sm:h-11 px-4 sm:px-5 bg-[#FFC61A] hover:bg-[#e6b217] text-[#102B70] text-xs sm:text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-xs whitespace-nowrap">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Borrow Books
                </a>
            @endif
        </div>
    @endif --}}
</div>