<div>
    <!-- Placeholder Content for Notifications (You can loop through real notifications here) -->
    @for ($i = 0; $i < 4; $i++)
    <a href="#" class="block px-4 py-3 hover:bg-[#EFF6FF]/50 transition-colors border-b border-gray-50 last:border-0 group">
        <div class="flex items-start gap-3">
            <div class="shrink-0 mt-0.5">
                <div class="h-8 w-8 rounded-full bg-[#EFF6FF] flex items-center justify-center text-[#3B82F6] group-hover:bg-[#3B82F6] group-hover:text-white transition-colors shadow-sm">
                    <!-- Example Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate group-hover:text-[#102B70] transition-colors">
                    @if($i == 0)
                        New Book Reserved
                    @elseif($i == 1)
                        Overdue Item Alert
                    @elseif($i == 2)
                        System Maintenance
                    @else
                        Library Policy Update
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                    @if($i == 0)
                        John Doe has reserved 'The Great Gatsby'. Please prepare the item for pickup.
                    @elseif($i == 1)
                        Jane Smith has 3 items that are overdue. Automated reminders have been sent.
                    @elseif($i == 2)
                        The system will undergo brief maintenance tonight at 12:00 AM.
                    @else
                        Please review the updated circulation rules in the settings module.
                    @endif
                </p>
                <p class="text-[10px] text-gray-400 mt-1 font-medium">
                    {{ $i == 0 ? '10 mins ago' : ($i == 1 ? '1 hour ago' : 'Yesterday') }}
                </p>
            </div>
            <!-- Unread Indicator (Optional dot) -->
            @if($i < 2)
            <div class="shrink-0 flex items-center justify-center pt-2">
                <div class="h-2 w-2 rounded-full bg-[#FCC719]"></div>
            </div>
            @endif
        </div>
    </a>
    @endfor
    
    <!-- Empty State (Hidden by default, shown if no notifications) -->
    <!--
    <div class="px-4 py-8 text-center flex flex-col items-center justify-center">
        <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mb-3 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-600">No new notifications</p>
        <p class="text-xs text-gray-400 mt-1">You're all caught up for today!</p>
    </div>
    -->
</div>