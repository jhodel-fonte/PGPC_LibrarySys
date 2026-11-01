import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync('resources/views/livewire/components/circulation/live-camera-script.blade.php', 'utf8').replace(/<\/?script>/g, '');

function camera(Scanner, autoStart = false) {
    const calls = [];
    const events = [];
    const window = { isSecureContext: true, Html5Qrcode: Scanner, dispatchEvent: event => events.push(event) };
    class CustomEvent { constructor(type, options) { this.type = type; this.detail = options.detail; } }
    vm.runInNewContext(source, { window, CustomEvent, console, setTimeout, clearTimeout, Date, Math, Promise });
    return { scanner: window.liveCameraScanner({ handleScan: (code, id) => { calls.push([code, id]); return Promise.resolve(); } }, autoStart), calls, events };
}

test('borrow and return scans send the code to the input without a camera lookup request', () => {
    const { scanner, calls, events } = camera(undefined, true);
    scanner.detectCode('ACC-123');
    assert.equal(calls.length, 0);
    assert.equal(events.length, 1);
    assert.equal(events[0].type, 'camera-code-read');
    assert.equal(events[0].detail.code, 'ACC-123');
    scanner.detectCode('ACC-123');
    assert.equal(events.length, 1);
});

test('waits for lookup, suppresses the visible code, and permits deliberate retry', async () => {
    const { scanner, calls } = camera();
    scanner.enabled = true;
    scanner.detectCode('A');
    scanner.detectCode('B');
    assert.equal(calls.length, 1);
    const id = calls[0][1];
    scanner.finishScan({ scanId: 'old', success: true });
    assert.equal(scanner.hasActiveScan, true);
    scanner.finishScan({ scanId: id, success: true });
    scanner.detectCode('A');
    assert.equal(calls.length, 1);
    scanner.detectCode('B');
    assert.equal(calls.length, 2);
    scanner.finishScan({ scanId: calls[1][1], success: false });
    scanner.retryScan();
    scanner.detectCode('B');
    assert.equal(calls.length, 3);
    scanner.stopScanner();
});

test('releases a camera that starts after the scanner is closed', async () => {
    let finishStart;
    let stopped = 0;
    class Scanner {
        static async getCameras() { return [{ id: 'camera', label: 'camera' }]; }
        constructor() { this.isScanning = false; }
        async start() { await new Promise(resolve => { finishStart = resolve; }); this.isScanning = true; }
        async stop() { stopped++; this.isScanning = false; }
        clear() {}
    }
    const { scanner } = camera(Scanner);
    const starting = scanner.startScanner();
    while (!finishStart) await new Promise(resolve => setTimeout(resolve, 0));
    scanner.stopScanner();
    finishStart();
    await starting;
    assert.equal(stopped, 1);
    assert.equal(scanner.scanner, null);
    assert.equal(scanner.isPaused, true);
});
