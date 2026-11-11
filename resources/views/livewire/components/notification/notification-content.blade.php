<div x-data="notificationCenter($wire)" @notification-drawer-opened.window="open()" @notification-drawer-closed.window="opened = false" @export-queued.window="opened ? (loaded ? sync() : firstPage()) : refreshCount()" @online.window="opened ? sync() : refreshCount()" @visibilitychange.document="if (!document.hidden) opened ? sync() : refreshCount()" class="divide-y divide-slate-100">
    <div class="border-b border-slate-200 px-6 py-3" x-show="count > 0" x-cloak>
        <button type="button" @click="action(null, 'all')" :disabled="busy || loading" class="min-h-11 text-sm font-semibold text-[#102B70] underline disabled:opacity-50">Mark all as read</button>
    </div>
    <p x-show="error" x-text="error" role="alert" class="px-6 py-3 text-sm text-red-800"></p>
    <div x-show="error" class="px-6 pb-3"><button type="button" @click="loaded ? sync() : firstPage()" class="text-sm font-semibold text-[#102B70] underline">Try again</button></div>
    <p x-show="loading && !loaded" class="px-6 py-8 text-sm text-slate-600" role="status">Loading notifications…</p>
    <template x-for="item in items" :key="item.id">
        <article class="px-6 py-4" :class="item.unread ? 'bg-slate-50' : 'bg-white'">
            <div class="flex items-start gap-2"><span x-show="item.unread" class="text-xs font-semibold text-slate-600">Unread</span><h3 x-text="item.title" class="font-bold text-slate-900"></h3></div>
            <p x-show="item.status === 'queued' || item.status === 'running'" role="status" class="mt-2 text-xs font-semibold text-[#102B70]">Export in progress...</p>
            <p x-text="item.message" class="mt-1 break-words text-sm text-slate-700"></p>
            <p x-show="item.book_title" x-text="item.book_title" class="mt-2 break-words text-sm font-semibold text-[#102B70]"></p>
            <p x-show="item.due_date" class="text-xs text-slate-600">Due: <span x-text="item.due_date"></span></p>
            <p x-text="item.time" class="mt-2 text-xs text-slate-600"></p>
            <div class="mt-3 flex flex-wrap gap-3">
                <a x-show="item.download_url" :href="item.download_url" class="min-h-11 text-sm font-semibold text-[#102B70] underline">Download Excel</a>
                <button x-show="item.has_action" type="button" @click="$wire.markAsRead(item.id, true)" :disabled="busy || loading" x-text="item.action_text" class="min-h-11 text-sm font-semibold text-[#102B70] underline disabled:opacity-50"></button>
                <button x-show="item.unread" type="button" @click="action(item, 'read')" :disabled="busy || loading" class="min-h-11 rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white disabled:opacity-50">Mark read</button>
                <button type="button" @click="action(item, 'dismiss')" :disabled="busy || loading" class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 disabled:opacity-50">Dismiss</button>
            </div>
        </article>
    </template>
    <p x-show="loaded && !loading && !items.length && !error" x-cloak class="px-6 py-10 text-center text-sm text-slate-600">You are all caught up with your library notices.</p>
    <div x-show="cursor" x-cloak class="p-4 text-center"><button type="button" @click="more()" :disabled="busy || loading" class="min-h-11 text-sm font-semibold text-[#102B70] underline disabled:opacity-50">Load more</button></div>
</div>
