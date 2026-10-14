@props([
    'checkInRoute' => null,
    'checkOutRoute' => null,
    'staffName' => null,
])
@php
    $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
    $resolvedCheckInRoute = $checkInRoute ?? route($routePrefix . 'circulation-desk.return');
    $resolvedCheckOutRoute = $checkOutRoute ?? route($routePrefix . 'circulation-desk.borrow');

    $authUser = auth()->user();
    $canCheckIn = $authUser && (
        $authUser->hasRole('Admin') ||
        $authUser->hasPermission('manage_circulation') ||
        $authUser->hasPermission('return_book')
    );
    $canCheckOut = $authUser && (
        $authUser->hasRole('Admin') ||
        $authUser->hasPermission('manage_circulation') ||
        $authUser->hasPermission('borrow_book')
    );
@endphp
<div class="bg-[#17357A] rounded-[16px] p-5 sm:p-7 lg:p-8 relative overflow-hidden flex flex-col justify-between min-h-[260px] h-full">
    <!-- Background Decoration -->
    <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(105deg, transparent 0%, transparent 60%, rgba(255,255,255,.025) 60%);"></div>

    <div class="relative z-10 flex flex-col h-full">
        <div class="mb-6 flex-1">
            <p class="text-blue-200 text-[10px] sm:text-xs font-semibold uppercase tracking-[0.08em] mb-3 sm:mb-4">
                {{ strtoupper(now()->format('l, F j, Y')) }}
            </p>
            
            <h1 class="text-3xl sm:text-[34px] xl:text-[38px] font-bold text-white leading-tight mb-2">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, 
                <span class="text-[#FFC61A]">{{ $staffName ?? 'User' }}</span>
            </h1>
            
            <p class="text-blue-100 text-[13px] sm:text-[14px] leading-relaxed max-w-[620px] mt-2 sm:mt-3">
                @if($canCheckIn || $canCheckOut)
                    Here's an overview of the library today. Process returning items or check-out new books immediately below.
                @else
                    Here's an overview of the library today. Review circulation activity, collection metrics, and announcements.
                @endif
            </p>
        </div>
        
        <!-- Quick Action Buttons -->
        @if($canCheckIn || $canCheckOut)
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-[14px] mt-auto">
                @if($canCheckIn)
                    <a href="{{ $resolvedCheckInRoute }}" wire:navigate class="w-full sm:w-auto h-11 px-5 bg-[#05BF8F] hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Quick Check-In (Return)
                    </a>
                @endif
                @if($canCheckOut)
                    <a href="{{ $resolvedCheckOutRoute }}" wire:navigate class="w-full sm:w-auto h-11 px-5 bg-[#FFC61A] hover:bg-[#e6b217] text-[#102B70] text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Quick Check-Out (Borrow)
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>