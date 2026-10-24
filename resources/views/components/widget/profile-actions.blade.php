@php
    $studentUser = auth()->user()?->student;
    $librarianUser = auth()->user()?->librarian;
    $person = $studentUser ?? $librarianUser;
    $displayName = $person ? ($person->first_name . ' ' . $person->last_name) : (auth()->user()?->name ?? auth()->user()?->username ?? 'User');
    $routePrefix = request()->is('student*') ? 'student.' : (request()->is('librarian*') ? 'librarian.' : 'admin.');
    $profileRouteName = $routePrefix . 'profile';
    $profileUrl = \Illuminate\Support\Facades\Route::has($profileRouteName)
        ? route($profileRouteName)
        : (\Illuminate\Support\Facades\Route::has('student.profile') ? route('student.profile') : '#');
@endphp
<div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
        {{-- Displaying authenticated user's details if available --}}
        <p class="text-sm font-bold text-gray-900 truncate">{{ $displayName }}</p>
        <p class="text-xs font-medium text-gray-500 capitalize">{{ auth()->user()?->role?->name ?? 'Student' }}</p>
    </div>
    
    <div class="py-1">
        <x-widget.profile-action-button href="{{ $profileUrl }}" wire:navigate>
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </x-slot:icon>
            My Profile
        </x-widget.profile-action-button>
    </div>

    <!-- Accessibility / Text Size Selector -->
    <div 
        x-data="{
            textZoom: localStorage.getItem('pgpc_text_zoom') || '100',
            applyZoom(val) {
                this.textZoom = val;
                localStorage.setItem('pgpc_text_zoom', val);
                document.documentElement.style.setProperty('--text-zoom', val);
            }
        }"
        x-init="applyZoom(textZoom)"
        @click.stop
        class="px-4 py-2.5 border-t border-gray-100 bg-slate-50/60"
    >
        <div class="flex items-center justify-between mb-1.5">
            <span class="flex items-center gap-1.5 text-xs font-bold text-[#102B70]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#102B70]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="4 7 4 4 20 4 20 7"></polyline>
                    <line x1="9" y1="20" x2="15" y2="20"></line>
                    <line x1="12" y1="4" x2="12" y2="20"></line>
                </svg>
                Text Size
            </span>
        </div>

        <div class="relative">
            <select 
                x-model="textZoom"
                @change="applyZoom($event.target.value)"
                @click.stop
                class="w-full text-xs font-semibold text-[#102B70] bg-white border border-[#CBD5E1] rounded-lg px-2.5 py-1.5 pr-7 appearance-none cursor-pointer hover:border-[#102B70] focus:outline-none focus:border-[#102B70] focus:ring-1 focus:ring-[#EFF6FF] transition-colors shadow-xs"
            >
                <option value="90">Compact (90%)</option>
                <option value="100">Default (100%)</option>
                <option value="110">Medium (110%)</option>
                <option value="120">Large (120%)</option>
                <option value="130">Extra Large (130%)</option>
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-[#102B70]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </span>
        </div>
    </div>

    <div class="border-t border-gray-100 py-1 bg-gray-50/50">
        <livewire:components.widgets.logout-button />
    </div>
</div>
