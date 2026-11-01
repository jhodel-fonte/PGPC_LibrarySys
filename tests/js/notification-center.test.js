import test from 'node:test';
import assert from 'node:assert/strict';
import notificationCenter from '../../resources/js/notification-center.js';

globalThis.window = { dispatchEvent() {} };
globalThis.CustomEvent = class { constructor(name, options) { this.type = name; this.detail = options.detail; } };

test('dismiss is immediate and a lost response reconciles with server state', async () => {
    let reject;
    const wire = {
        deleteNotification: () => new Promise((_, fail) => { reject = fail; }),
        page: async () => ({ items: [], cursor: null }),
        current: async () => [],
        unreadCount: async () => 0,
    };
    const center = notificationCenter(wire);
    center.loaded = true;
    center.items = [{ id: 'one', unread: true }];
    center.count = 1;
    const operation = center.action(center.items[0], 'dismiss');
    assert.equal(center.items.length, 0);
    assert.equal(center.count, 0);
    reject(new Error('response lost'));
    await operation;
    assert.equal(center.items.length, 0);
    assert.equal(center.count, 0);
});

test('failed read is restored when the server still reports unread', async () => {
    const row = { id: 'one', unread: true };
    const wire = {
        markAsRead: async () => { throw new Error('offline'); },
        page: async () => ({ items: [row], cursor: null }),
        current: async () => [row],
        unreadCount: async () => 1,
    };
    const center = notificationCenter(wire);
    center.loaded = true; center.items = [{ ...row }]; center.count = 1;
    await center.action(center.items[0], 'read');
    assert.equal(center.items[0].unread, true);
    assert.equal(center.count, 1);
    assert.match(center.error, /not saved/);
});
