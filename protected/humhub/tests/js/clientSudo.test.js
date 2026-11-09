import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

// How the shared client (humhub.client.js) handles a request that asks for the sudo mode
// (docs/develop/concept-sudo-mode.md): a `401` with `X-Sudo-Required` goes to the sudo handler, and once it resolves
// the same request is sent again. Without a confirmation the caller gets the original refusal.

globalThis.humhub.module('action', function (module) {
    module.export({ registerHandler: () => {} });
});
globalThis.humhub.module('config', function (module) {
    module.export({ module: (id) => globalThis.humhub.config.module(typeof id === 'string' ? id : id.id) });
});
globalThis.humhub.module('ui.status', function (module) {
    module.export({ warn: () => {} });
});
globalThis.humhubStubs.view.getViewContext = () => null;
globalThis.humhubStubs.additions.register = () => {};

await import('../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub/humhub.log.js');
await import('../../resources/js/humhub/humhub.client.js');

const client = globalThis.humhub.modules.client;

const xhrFor = (statusCode, { json, headers = {} } = {}) => {
    const lowerHeaders = Object.fromEntries(Object.entries(headers).map(([k, v]) => [k.toLowerCase(), v]));
    lowerHeaders['content-type'] = 'application/json; charset=UTF-8';
    return {
        status: statusCode,
        responseJSON: json,
        responseText: JSON.stringify(json || {}),
        getResponseHeader: (name) => lowerHeaders[name.toLowerCase()] ?? null,
    };
};

const sudoRequired = () => xhrFor(401, {
    json: { sudo: true, url: '/user/sudo/confirm' },
    headers: { 'X-Sudo-Required': '/user/sudo/confirm&returnUrl=%2F' },
});

let ajax;

// Each $.ajax call answers with the next queued outcome: an xhr fails, an object succeeds.
const answer = (...outcomes) => {
    const queue = [...outcomes];
    ajax.mockImplementation((cfg) => {
        const outcome = queue.shift();
        if (outcome.status) {
            cfg.error(outcome, 'error', 'Error');
        } else {
            cfg.success(outcome, 'success', xhrFor(200, { json: outcome }));
        }
    });
};

beforeEach(() => {
    ajax = vi.spyOn(globalThis.jQuery, 'ajax');
});

afterEach(() => {
    vi.restoreAllMocks();
    client.setSudoHandler(null);
});

describe('humhub.client on 401 with X-Sudo-Required', () => {
    it('asks the sudo handler and sends the same request again once confirmed', async () => {
        const handler = vi.fn(() => Promise.resolve());
        client.setSudoHandler(handler);
        answer(sudoRequired(), { saved: true });

        const response = await client.post('/user/account/change-email', { data: { 'AccountChangeEmail[newEmail]': 'a@b.c' } });

        expect(handler).toHaveBeenCalledWith('/user/sudo/confirm&returnUrl=%2F');
        expect(ajax).toHaveBeenCalledTimes(2);
        expect(ajax.mock.calls[1][0].url).toBe('/user/account/change-email');
        expect(ajax.mock.calls[1][0].data).toEqual({ 'AccountChangeEmail[newEmail]': 'a@b.c' });
        expect(response.data).toEqual({ saved: true });
    });

    it('rejects with the refusal when the dialog is closed without confirming', async () => {
        client.setSudoHandler(() => Promise.reject());
        const errorHandler = vi.fn();
        answer(sudoRequired());

        const rejected = await client.post('/user/account/change-email', { error: errorHandler }).catch((r) => r);

        expect(ajax).toHaveBeenCalledTimes(1);
        expect(rejected.status).toBe(401);
        expect(errorHandler).toHaveBeenCalledTimes(1);
    });

    it('leaves other 401s and requests without a handler alone', async () => {
        const handler = vi.fn(() => Promise.resolve());
        client.setSudoHandler(handler);
        answer(xhrFor(401, { json: { gate: 'twofa' } }));
        expect((await client.post('/x').catch((r) => r)).status).toBe(401);
        expect(handler).not.toHaveBeenCalled();

        client.setSudoHandler(null);
        answer(sudoRequired());
        expect((await client.post('/x').catch((r) => r)).status).toBe(401);
        expect(ajax).toHaveBeenCalledTimes(2);
    });
});
