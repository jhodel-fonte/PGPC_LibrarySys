<div wire:poll.30s class="divide-y divide-slate-100">
    @if($notifications->isNotEmpty())
        @foreach ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $isUnread = is_null($notification->read_at);
                $type = $data['type'] ?? 'info';
                $actionUrl = $data['action_url'] ?? null;
            @endphp
            <div 
                wire:key="notif-{{ $notification->id }}" 
                class="relative px-4 py-3 hover:bg-[#EFF6FF]/40 transition-colors group {{ $isUnread ? 'bg-[#F8FAFC]' : '' }}"
            >
                <div class="flex items-start gap-3">
                    <!-- Notification Type Icon -->
                    <div class="shrink-0 mt-0.5">
                        @if($type === 'success')
                            <div class="h-8 w-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                        @elseif($type === 'warning')
                            <div class="h-8 w-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                        @elseif($type === 'danger' || $type === 'error')
                            <div class="h-8 w-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                        @else
                            <div class="h-8 w-8 rounded-full bg-[#EFF6FF] text-[#102B70] flex items-center justify-center border border-[#DBEAFE] shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div 
                        class="flex-1 min-w-0 {{ $actionUrl ? 'cursor-pointer' : '' }}"
                        @if($actionUrl)
                            wire:click="markAsRead('{{ $notification->id }}', '{{ $actionUrl }}')"
                        @else
                            wire:click="markAsRead('{{ $notification->id }}')"
                        @endif
                    >
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-xs sm:text-sm font-bold text-slate-800 truncate group-hover:text-[#102B70] transition-colors">
                                {{ $data['title'] ?? 'Library Alert' }}
                            </p>
                        </div>

                        <p class="text-xs text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">
                            {{ $data['message'] ?? '' }}
                        </p>

                        <!-- Related Book Details if present -->
                        @if(!empty($data['book_title']))
                            <div class="mt-1.5 flex items-center gap-2 p-1.5 rounded-lg bg-white border border-slate-200/80 shadow-2xs">
                                @if(!empty($data['book_cover']))
                                    <img 
                                        src="{{ $data['book_cover'] }}" 
                                        alt="Book Cover" 
                                        class="h-8 w-6 rounded object-cover border border-slate-200 shrink-0"
                                        onerror="this.style.display='none'"
                                    >
                                @else
                                    <div class="h-6 w-6 rounded bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-bold text-[#102B70] truncate leading-tight">
                                        {{ $data['book_title'] }}
                                    </p>
                                    @if(!empty($data['due_date']))
                                        <p class="text-[10px] font-semibold text-amber-700 mt-0.5">
                                            Due: {{ $data['due_date'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-[10px] font-semibold text-slate-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            @if($actionUrl)
                                <span class="text-[10px] font-bold text-[#102B70] underline hover:text-[#0B225E]">
                                    {{ $data['action_text'] ?? 'View' }} &rarr;
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Unread Badge & Actions -->
                    <div class="shrink-0 flex items-center gap-1.5 pt-1">
                        @if($isUnread)
                            <button 
                                type="button" 
                                wire:click="markAsRead('{{ $notification->id }}')" 
                                title="Mark as read"
                                class="h-2 w-2 rounded-full bg-[#FCC719] ring-2 ring-[#FCC719]/30"
                            ></button>
                        @endif

                        <button
                            type="button"
                            wire:click="deleteNotification('{{ $notification->id }}')"
                            title="Dismiss notification"
                            class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-rose-500 transition-opacity p-0.5 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <!-- Empty State -->
        <div class="px-4 py-10 text-center flex flex-col items-center justify-center">
            <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-2.5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-sm font-bold text-slate-700">No New Notifications</p>
            <p class="text-xs text-slate-400 mt-0.5">You're all caught up with your library notices!</p>
        </div>
    @endif
</div>