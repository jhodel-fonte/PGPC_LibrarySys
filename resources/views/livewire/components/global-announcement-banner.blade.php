<div>
    @foreach($announcements as $announcement)
        @if(!in_array($announcement['id'], $dismissed))
            @php
                $style = $announcement['display_style'] ?? 'Information';
                
                $styleClasses = match($style) {
                    'Information' => 'bg-blue-50 border-blue-200 text-blue-800',
                    'Notice' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                    'Warning' => 'bg-amber-50 border-amber-200 text-amber-800',
                    'Critical' => 'bg-red-50 border-red-200 text-red-800',
                    default => 'bg-slate-50 border-slate-200 text-slate-800'
                };
                
                $iconClass = match($style) {
                    'Information' => 'text-blue-500',
                    'Notice' => 'text-emerald-500',
                    'Warning' => 'text-amber-500',
                    'Critical' => 'text-red-500',
                    default => 'text-slate-500'
                };
            @endphp
            
            <div class="relative w-full border-b {{ $styleClasses }} px-4 py-3 shadow-sm sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0">
                            @if($style === 'Information' || $style === 'Notice')
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $iconClass }}"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            @elseif($style === 'Warning' || $style === 'Critical')
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $iconClass }}"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            @endif
                        </div>
                        <p class="text-sm leading-6">
                            <strong class="font-semibold">{{ $announcement['title'] }}</strong>
                            <span class="mx-1">&middot;</span>
                            <span>{{ $announcement['message'] }}</span>
                        </p>
                    </div>
                    
                    <button type="button" wire:click="dismiss({{ $announcement['id'] }})" class="-m-1.5 flex-none p-1.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-opacity hover:opacity-75">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    @endforeach
</div>
