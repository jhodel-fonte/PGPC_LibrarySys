<div wire:poll.30s class="divide-y divide-slate-100">
    @if($notifications->isNotEmpty())
        @foreach ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $isUnread = is_null($notification->read_at);
                $actionUrl = $data['action_url'] ?? null;
            @endphp
            <div 
                wire:key="notif-{{ $notification->id }}" 
                class="relative px-4 py-3 hover:bg-[#EFF6FF]/40 transition-colors group {{ $isUnread ? 'bg-[#F8FAFC]' : '' }}"
            >
                <div class="flex items-start gap-3">
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
                                class="text-[10px] font-semibold text-slate-500 hover:text-[#102B70]">Mark read</button>
                        @endif

                        <button
                            type="button"
                            wire:click="deleteNotification('{{ $notification->id }}')"
                            title="Dismiss notification"
                            class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 text-[10px] font-semibold text-slate-500 hover:text-rose-600 transition-opacity p-0.5 cursor-pointer"
                        >
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <!-- Empty State -->
        <div class="px-4 py-10 text-center">
            <p class="text-sm font-bold text-slate-700">No notifications</p>
            <p class="text-xs text-slate-400 mt-0.5">You're all caught up with your library notices!</p>
        </div>
    @endif
</div>
