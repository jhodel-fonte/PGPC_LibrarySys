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

    <div class="border-t border-gray-100 py-1 bg-gray-50/50">
        <livewire:components.widgets.logout-button />
    </div>
</div>
