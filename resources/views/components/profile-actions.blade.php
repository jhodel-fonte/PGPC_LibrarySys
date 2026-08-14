<div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
        {{-- Displaying authenticated user's details if available --}}
        <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()?->name ?? auth()->user()?->username ?? 'Student Name' }}</p>
        <p class="text-xs font-medium text-gray-500 capitalize">{{ auth()->user()?->role?->name ?? 'Student' }}</p>
    </div>
    
    <div class="py-1">
        <x-profile-action-button href="#">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </x-slot:icon>
            My Profile
        </x-profile-action-button>
        
        <x-profile-action-button href="#">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </x-slot:icon>
            Account Settings
        </x-profile-action-button>
    </div>

    <div class="border-t border-gray-100 py-1 bg-gray-50/50">
        <livewire:components.logout-button />
    </div>
</div>
