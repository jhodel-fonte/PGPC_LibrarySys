export default function notificationCenter(wire) {
    return {
        items: [], cursor: null, loaded: false, loading: false, busy: false, opened: false, version: 0, error: '', count: 0, timer: null,
        init() {
            this.refreshCount();
            this.timer = setInterval(() => { if (!document.hidden && !this.busy) this.opened ? this.sync(false) : this.refreshCount(); }, 30000);
        },
        destroy() { clearInterval(this.timer); },
        setCount(count) {
            this.count = Math.max(0, count);
            window.dispatchEvent(new CustomEvent('notifications-updated', { detail: { unreadCount: this.count } }));
        },
        async refreshCount() {
            if (this.busy) return;
            const version = this.version;
            try { const count = await wire.unreadCount(); if (!this.busy && version === this.version) this.setCount(count); } catch (_) { /* Retry on the next poll. */ }
        },
        async open() {
            this.opened = true;
            if (!this.loaded) return this.firstPage();
            return this.sync();
        },
        async firstPage() {
            if (this.loading || this.busy) return;
            this.loading = true; this.error = '';
            try {
                const page = await wire.page();
                this.items = page.items; this.cursor = page.cursor; this.loaded = true;
                await this.refreshCount();
            } catch (_) { this.error = 'Could not load notifications. Please try again.'; }
            finally { this.loading = false; }
        },
        async more() {
            if (!this.cursor || this.loading || this.busy) return;
            this.loading = true; this.error = '';
            try {
                const page = await wire.page(this.cursor);
                const ids = new Set(this.items.map(item => item.id));
                this.items.push(...page.items.filter(item => !ids.has(item.id)));
                this.cursor = page.cursor;
            } catch (_) { this.error = 'Could not load more notifications. Please try again.'; }
            finally { this.loading = false; }
        },
        async sync(full = true) {
            if (this.loading || this.busy || !this.loaded) return;
            this.loading = true;
            try {
                const page = await wire.page();
                const existing = [];
                if (full) {
                    for (let i = 0; i < this.items.length; i += 200) {
                        existing.push(...await wire.current(this.items.slice(i, i + 200).map(item => item.id)));
                    }
                } else existing.push(...this.items);
                const current = new Map(existing.map(item => [item.id, item]));
                const first = new Set(page.items.map(item => item.id));
                if (this.items.length && page.items.length === 15 && !this.items.some(item => first.has(item.id))) {
                    this.items = page.items;
                    this.cursor = page.cursor;
                } else {
                    this.items = [...page.items, ...this.items.filter(item => !first.has(item.id) && current.has(item.id)).map(item => current.get(item.id))];
                }
                this.error = '';
                await this.refreshCount();
            } catch (_) { this.error = 'Unable to refresh notifications. Check your connection and try again.'; }
            finally { this.loading = false; }
        },
        async action(item, kind) {
            if (this.busy || this.loading) return;
            this.version++; this.busy = true; this.error = '';
            const oldItems = this.items.map(row => ({ ...row }));
            const oldCount = this.count;
            if (kind === 'all') { this.items.forEach(row => row.unread = false); this.setCount(0); }
            else {
                if (item.unread) this.setCount(this.count - 1);
                if (kind === 'dismiss') this.items = this.items.filter(row => row.id !== item.id);
                else item.unread = false;
            }
            try {
                if (kind === 'all') await wire.markAllAsRead();
                else if (kind === 'dismiss') await wire.deleteNotification(item.id);
                else await wire.markAsRead(item.id);
                this.busy = false;
                await this.sync();
            } catch (_) {
                this.items = oldItems; this.setCount(oldCount); this.busy = false;
                await this.sync();
                if (this.error) this.error = 'Update could not be confirmed. Reconnect and try again.';
                else if (kind === 'all' ? this.count > 0 : this.items.some(row => row.id === item.id && (kind === 'dismiss' || row.unread))) this.error = 'The update was not saved. Please try again.';
            }
        },
    };
}
