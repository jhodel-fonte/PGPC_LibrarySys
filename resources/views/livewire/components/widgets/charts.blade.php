<div class="bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-5 flex flex-col justify-between">
    {{-- Card Header --}}
    <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full {{ $type === 'donut' ? 'bg-[#FCC719]' : ($type === 'bar' ? 'bg-[#3B82F6]' : 'bg-[#102B70]') }}"></span>
            <h3 class="text-sm font-bold text-[#0F172A] tracking-tight">{{ $title }}</h3>
        </div>

        @if($type === 'line')
            <div class="relative">
                <select 
                    wire:model.live="filter" 
                    class="text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 pr-7 appearance-none cursor-pointer focus:outline-none focus:border-[#102B70] focus:ring-1 focus:ring-[#102B70]"
                >
                    <option value="7_days">Last 7 Days</option>
                    <option value="30_days">Last 30 Days</option>
                    <option value="this_year">This Year</option>
                </select>
                <span class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </div>
        @endif
    </div>

    {{-- Chart Body --}}
    <div class="my-auto py-2" wire:loading.class="opacity-50 transition-opacity">
        @if($type === 'donut')
            <x-widget.chart 
                :series="$donutData['series']" 
                :labels="$donutData['labels']" 
                :colors="['#102B70', '#3B82F6', '#FCC719', '#10B981', '#8B5CF6']"
                donut 
                legend 
                :height="$height" 
                :centerLabel="['value' => $donutData['total'], 'label' => 'Total']" 
            />
        @elseif($type === 'bar')
            <x-widget.chart 
                :series="$barData['series']" 
                :labels="$barData['labels']" 
                :colors="['#102B70', '#3B82F6']"
                bar 
                grid 
                legend 
                :height="$height" 
            />
        @else
            <x-widget.chart 
                :series="$lineData['series']" 
                :labels="$lineData['labels']" 
                :colors="['#102B70', '#3B82F6']"
                line 
                grid 
                legend 
                markers 
                :height="$height" 
            />
        @endif
    </div>
</div>
