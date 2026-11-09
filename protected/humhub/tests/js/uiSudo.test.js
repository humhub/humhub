import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

// humhub.ui.sudo (docs/develop/concept-sudo-mode.md): a form marked `data-sudo-required` asks for the confirmation
// before it is sent, and is sent once confirmed; closing the dialog without confirming leaves it unsent.

const $ = globalThis.jQuery;

// The global modal: load() shows the dialog; the test plays the user by triggering `submitted` and `hidden`.
const dialog = {
    $: $('<div class="modal" id="globalModal"></div>').appendTo(document.body),
    load: vi.fn(() => Promise.resolve()),
    close: vi.fn(),
};
globalThis.humhubStubs.modal.global = dialog;
globalThis.humhubStubs.client.setSudoHandler = vi.fn();
globalThis.humhubStubs.client.redirect = vi.fn();
const loaderReset = vi.fn();
globalThis.humhub.module('ui.loader', function (module) {
    module.export({ reset: loaderReset });
});

await import('../../resources/js/humhub/humhub.ui.sudo.js');

const sudo = globalThis.humhub.modules['ui.sudo'];
const flush = () => new Promise((resolve) => setTimeout(resolve, 5));

const userConfirms = async () => {
    await flush();
    dialog.$.trigger('submitted', { success: true });
    dialog.$.trigger('hidden.bs.modal');
    await flush();
};

const userCancels = async () => {
    await flush();
    dialog.$.trigger('hidden.bs.modal');
    await flush();
};

let form;
let sent;

beforeEach(() => {
    form = $('<form action="/user/account/change-email" data-sudo-required="/user/sudo/confirm"><button type="submit" data-ui-loader>Save</button></form>')
        .appendTo(document.body)[0];
    sent = vi.fn();
    form.requestSubmit = () => sent();
    dialog.load.mockClear();
});

afterEach(() => {
    form.remove();
});

describe('humhub.ui.sudo', () => {
    it('registers itself as the client\'s sudo handler', () => {
        expect(globalThis.humhubStubs.client.setSudoHandler).toHaveBeenCalledWith(sudo.confirm);
    });

    it('asks before a marked form is sent and sends it once confirmed', async () => {
        const submit = new Event('submit', { bubbles: true, cancelable: true });
        form.dispatchEvent(submit);

        expect(submit.defaultPrevented).toBe(true);
        expect(dialog.load).toHaveBeenCalledWith('/user/sudo/confirm');
        expect(sent).not.toHaveBeenCalled();

        await userConfirms();

        expect(sent).toHaveBeenCalledTimes(1);
        expect(form.hasAttribute('data-sudo-required')).toBe(false);
    });

    it('leaves the form unsent when the dialog is closed without confirming', async () => {
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        await userCancels();

        expect(sent).not.toHaveBeenCalled();
        expect(form.getAttribute('data-sudo-required')).toBe('/user/sudo/confirm');
        expect(loaderReset).toHaveBeenCalledWith(form.querySelector('button'));
    });

    it('opens one dialog for parallel requests', async () => {
        const first = sudo.confirm('/user/sudo/confirm');
        const second = sudo.confirm('/user/sudo/confirm');
        expect(first).toBe(second);
        expect(dialog.load).toHaveBeenCalledTimes(1);

        await userConfirms();
        await expect(first).resolves.toBeUndefined();
    });
});
