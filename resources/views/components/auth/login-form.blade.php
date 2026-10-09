@props(['order' => 'right'])

@php
    $orderClasses = $order === 'left'
        ? 'lg:order-1 lg:col-span-6 xl:col-span-5'
        : 'lg:order-2 lg:col-span-6 xl:col-span-5';
@endphp

<section
    {{ $attributes->class(["relative flex flex-col min-h-dvh lg:min-h-0 lg:h-dvh lg:overflow-y-auto bg-slate-100/80 px-4 py-6 sm:px-8 lg:px-8 xl:px-12 {$orderClasses} outline-none focus:outline-none select-none cursor-default"]) }}>

    <!-- Top Mobile Accent Gradient -->
    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#102b70] via-[#fcc719] to-[#102b70] lg:hidden pointer-events-none select-none"></div>
    <div class="absolute right-0 top-0 h-64 w-64 rounded-bl-full bg-blue-50/80 pointer-events-none select-none"></div>

    <div class="relative z-10 w-full max-w-[520px] m-auto py-6">

        
        <!-- Mobile Logo Header (Hidden on Desktop) -->
        <div class="mb-6 lg:hidden flex items-center justify-between select-none">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 outline-none focus:outline-none group" wire:navigate>
                <img src="{{ asset('logo.webp') }}" alt="PGPC logo"
                    class="h-9 w-9 object-contain"
                    onerror="this.src='{{ asset('images/logo.webp') }}'">
                <div class="text-left">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-[#102b70] leading-none">Padre Garcia Polytechnic College</span>
                    <span class="block text-[9px] font-semibold text-slate-500 uppercase tracking-widest mt-0.5 leading-none">Library System</span>
                </div>
            </a>
        </div>

        {{ $slot }}
    </div>
</section>
