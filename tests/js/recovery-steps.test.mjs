import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const html = readFileSync('resources/views/livewire/pages/auth/forgot-password.blade.php', 'utf8');
const expression = html.match(/x-data="([\s\S]*?)" x-init=/)[1];

function recovery(wire) {
    const state = vm.runInNewContext(`(${expression})`, { $wire: wire });
    const inputs = Array.from({ length: 6 }, () => ({ focus() {} }));
    state.$refs = { accountForm: { reportValidity: () => true }, otpGroup: { querySelectorAll: () => inputs }, digitError: { hidden: true }, networkError: { hidden: true }, codeNetworkError: { hidden: true }, password: { focus() {} } };
    state.$nextTick = callback => callback();
    return state;
}

test('shows code step immediately while instructions are sending', async () => {
    let finish;
    const wire = { sent: false, sendPasswordResetLink: () => new Promise(resolve => { finish = () => { wire.sent = true; resolve(); }; }) };
    const state = recovery(wire);
    const request = state.send();
    assert.equal(state.step, 'code');
    assert.equal(state.sending, true);
    finish();
    await request;
    assert.equal(state.sending, false);
});

test('accepts pasted six-digit codes and verifies only complete entries', async () => {
    const calls = [];
    const wire = { verified: false, set: async (...args) => calls.push(args), verifyCode: async () => { wire.verified = true; } };
    const state = recovery(wire);
    state.enterDigits(0, '12 34-56');
    assert.equal(state.digits.join(''), '123456');
    await state.verify();
    assert.equal(calls[0][0], 'code');
    assert.equal(calls[0][1], '123456');
    assert.equal(state.step, 'password');
});
