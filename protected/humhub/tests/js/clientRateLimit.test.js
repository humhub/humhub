import { afterAll, afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

// How the shared client (humhub.client.js) tells the user about an exceeded rate limit:
// a `429` (see docs/develop/concept-rate-limiting.md, "Response") shows one status message
// per burst - the server's message, else a generic one - and humhub.log does not add a
// generic error for the same response. Nothing is retried.

// Core modules the harness does not provide. `action` and `config` are only touched by the
// modules' init(); `ui.status` is the seam under test.
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
// The client registers its `acknowledgeForm` addition without a selector, which the
// harness' additions stub cannot apply - and which is irrelevant here.
globalThis.humhubStubs.additions.register = () => {};

await import('../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub/humhub.log.js');
await import('../../resources/js/humhub/humhub.client.js');

const client = globalThis.humhub.modules.client;
const log = globalThis.humhub.modules.log;
const status = globalThis.humhub.modules['ui.status'];
const event = globalThis.humhubStubs.event;

// humhub.core.js gives every module text(); humhub.log.js resolves its messages through it.
const LOG_TEXTS = { 'error.default': 'An unexpected error occurred.' };
log.text = (key) => LOG_TEXTS[key];

const xhrFor = (statusCode, { json, headers = {} } = {}) => {
    const lowerHeaders = Object.fromEntries(Object.entries(headers).map(([k, v]) => [k.toLowerCase(), v]));
    if (json) {
        lowerHeaders['content-type'] = 'application/json; charset=UTF-8';
    }
    return {
        status: statusCode,
        responseJSON: json,
        responseText: json ? JSON.stringify(json) : '',
        getResponseHeader: (name) => lowerHeaders[name.toLowerCase()] ?? null,
    };
};

let ajax;
let warn;
let now = Date.UTC(2026, 0, 1);

// Each $.ajax call fails with the xhr the test queued.
const failWith = (...xhrs) => {
    const queue = [...xhrs];
    ajax.mockImplementation((cfg) => {
        const xhr = queue.shift();
        cfg.error(xhr, 'error', 'Error');
    });
};

const request = (url = '/api/v2/like/1') => client.get(url).then(
    () => { throw new Error('expected a rejection'); },
    (response) => response,
);

const rateLimited = (seconds = 42) => xhrFor(429, {
    json: { error: 'rate_limited', message: `Too many requests, try again in ${seconds} seconds.`, retryAfter: seconds },
    headers: { 'Retry-After': String(seconds) },
});

beforeEach(() => {
    // Every test starts after the previous test's notice window has passed.
    now += 3600 * 1000;
    vi.useFakeTimers({ toFake: ['Date'] });
    vi.setSystemTime(now);
    ajax = vi.spyOn(globalThis.jQuery, 'ajax');
    warn = vi.spyOn(status, 'warn').mockImplementation(() => {});
});

afterEach(() => {
    vi.restoreAllMocks();
});

afterAll(() => {
    vi.useRealTimers();
});

describe('humhub.client on 429 Too Many Requests', () => {
    it('shows the server\'s message, visible for the Retry-After seconds', async () => {
        failWith(rateLimited(12));

        const response = await request();

        expect(warn).toHaveBeenCalledTimes(1);
        expect(warn).toHaveBeenCalledWith('Too many requests, try again in 12 seconds.', undefined, 12000);
        expect(response.status).toBe(429);
        expect(response.statusShown).toBe('Too many requests, try again in 12 seconds.');
    });

    it('shows the message once for parallel refusals within the window', async () => {
        failWith(rateLimited(), rateLimited(), rateLimited());

        const responses = await Promise.all([request('/api/v2/a'), request('/api/v2/b'), request('/api/v2/c')]);

        expect(warn).toHaveBeenCalledTimes(1);
        // Every response is marked, so no caller adds a generic error of its own.
        responses.forEach((response) => expect(response.statusShown).toBeTruthy());
    });

    it('shows it again once the previous message has closed', async () => {
        failWith(rateLimited(10), rateLimited(10));

        await request();
        vi.setSystemTime(now + 9000);
        await request();
        expect(warn).toHaveBeenCalledTimes(1);

        vi.setSystemTime(now + 10001);
        failWith(rateLimited(10));
        await request();
        expect(warn).toHaveBeenCalledTimes(2);
    });

    it('keeps the message between 5 and 30 seconds on screen', async () => {
        failWith(rateLimited(1));
        await request();
        expect(warn).toHaveBeenLastCalledWith(expect.any(String), undefined, 5000);

        vi.setSystemTime(now + 60000);
        failWith(rateLimited(3600));
        await request();
        expect(warn).toHaveBeenLastCalledWith(expect.any(String), undefined, 30000);
    });

    it('falls back to a generic message with the Retry-After header of a response without a JSON body', async () => {
        failWith(xhrFor(429, { headers: { 'Retry-After': '7', 'Content-Type': 'text/html' } }));

        await request();

        expect(warn).toHaveBeenCalledWith('Too many requests. Please wait 7 seconds and try again.', undefined, 7000);
    });

    it('falls back to a generic message without any retry information', async () => {
        failWith(xhrFor(429, { headers: { 'Content-Type': 'text/plain' } }));

        await request();

        expect(warn).toHaveBeenCalledWith('Too many requests. Please wait a moment and try again.', undefined, 5000);
    });

    it('does not retry the request', async () => {
        failWith(rateLimited());

        await request();

        expect(ajax).toHaveBeenCalledTimes(1);
    });

    it('leaves other errors alone', async () => {
        failWith(xhrFor(500, { json: { message: 'Internal error' } }));

        const response = await request();

        expect(warn).not.toHaveBeenCalled();
        expect(response.statusShown).toBeUndefined();
    });
});

describe('humhub.log with a response the client already reported', () => {
    let setStatus;

    beforeEach(() => {
        setStatus = vi.fn();
        event.on('humhub:modules:log:setStatus', setStatus);
        vi.spyOn(console, 'error').mockImplementation(() => {});
    });

    it('does not show a second, generic error for a 429', async () => {
        failWith(rateLimited());
        const response = await request();

        log.error(response, true);

        expect(setStatus).not.toHaveBeenCalled();
        expect(console.error).toHaveBeenCalledWith(expect.stringContaining('Too many requests, try again in 42 seconds.'), response);
        event.off('humhub:modules:log:setStatus', setStatus);
    });

    it('still shows other failed responses', async () => {
        failWith(xhrFor(500, { headers: { 'Content-Type': 'text/html' } }));
        const response = await request();

        log.error(response, true);

        expect(setStatus).toHaveBeenCalledTimes(1);
        expect(setStatus.mock.calls[0][1]).toBe('An unexpected error occurred.');
        event.off('humhub:modules:log:setStatus', setStatus);
    });
});
