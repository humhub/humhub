import { beforeEach, describe, expect, it, vi } from 'vitest';

// humhub.client.pjax.js's init() only wires anything up when the module config
// says pjax is active — configure BEFORE the import below (the harness calls
// init() at registration time, see support/setup.mjs).
Object.assign(globalThis.humhub.config.module('client.pjax'), {
    active: true,
    options: { timeout: 100 },
});

// init() dependencies the harness doesn't provide: the jquery-pjax plugin
// binding and the NProgress loader. Both are irrelevant to the lifecycle
// translation under test.
globalThis.jQuery.fn.pjax = function () { return this; };
globalThis.NProgress = { configure: () => {}, start: () => {}, done: () => {} };

await import('../../resources/js/humhub/humhub.client.pjax.js');

const LIFECYCLE_UNLOAD_EVENT = 'humhub:modules:client:pjax:beforeSend';

/**
 * The module-lifecycle side of the acknowledgeForm state machine
 * (humhub.client.js): jquery-pjax fires `pjax:beforeSend` BEFORE the
 * unsaved-changes confirm outcome is known — a handler canceling the
 * navigation (confirm answered "cancel") aborts the request so that
 * `pjax:send`/`pjax:success` never fire and modules are never re-initialized.
 * The lifecycle unload event (humhub.core.js calls EVERY module's unload() on
 * it) must therefore be driven by `pjax:send` — which jquery-pjax only fires
 * once the request was actually dispatched — never by `pjax:beforeSend`
 * itself. Regression coverage for the canceled-confirm bug where all Vue
 * islands were unmounted (and legacy modules half-unloaded) on a navigation
 * that never happened.
 */
describe('humhub.client.pjax module lifecycle translation', () => {
    let trigger;

    beforeEach(() => {
        trigger = vi.spyOn(globalThis.humhubStubs.event, 'trigger');
    });

    const lifecycleCalls = (name) => trigger.mock.calls.filter((call) => call[0] === name);

    it('does not fire the module-unload lifecycle event on pjax:beforeSend (canceled navigation fires nothing)', () => {
        // A canceled acknowledgeForm confirm is exactly this sequence: beforeSend
        // fired, request aborted, send/success never happen.
        jQuery(document).trigger('pjax:beforeSend', [{ readyState: 0 }, {}]);

        expect(lifecycleCalls(LIFECYCLE_UNLOAD_EVENT)).toHaveLength(0);
    });

    it('fires the module-unload lifecycle event on pjax:send (request actually dispatched)', () => {
        const xhr = { readyState: 1 };
        const options = { url: '/some/target' };

        jQuery(document).trigger('pjax:beforeSend', [xhr, options]);
        jQuery(document).trigger('pjax:send', [xhr, options]);

        const calls = lifecycleCalls(LIFECYCLE_UNLOAD_EVENT);
        expect(calls).toHaveLength(1);
        expect(calls[0][1].xhr).toBe(xhr);
        expect(calls[0][1].options).toBe(options);
    });

    it('fires the success lifecycle event on pjax:success', () => {
        jQuery(document).trigger('pjax:success', ['<div></div>', 'success', { getResponseHeader: () => null }, {}]);

        expect(lifecycleCalls('humhub:modules:client:pjax:success')).toHaveLength(1);
    });
});
