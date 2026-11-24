import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

// `ui.status` is legacy JS, but it is now the façade in front of the status island
// (StatusBar.vue) that 18 external modules and View::endBody() call into - so its
// argument handling is worth pinning: level mapping, `details` flattening and the
// entity decoding legacy callers depend on.

// Minimal `log` module: the real humhub.log.js needs the `config`/`module` core
// modules this harness does not provide, while ui.status only reads its TRACE_*
// constants and log.error().
globalThis.humhub.module('log', function (module) {
    module.export({
        TRACE_TRACE: 0,
        TRACE_DEBUG: 1,
        TRACE_INFO: 2,
        TRACE_SUCCESS: 3,
        TRACE_WARN: 4,
        TRACE_ERROR: 5,
        TRACE_FATAL: 6,
        error: () => {},
    });
});

// The real util module rather than a stub: it has no dependencies of its own, so
// there is no reason to risk drift between a stubbed `object.isString` and the one
// ui.status actually runs against.
await import('../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');
await import('../../resources/js/humhub/humhub.ui.status.js');

const status = globalThis.humhub.modules['ui.status'];
const vueModule = globalThis.humhub.modules.vue;
const log = globalThis.humhub.modules.log;
const { Response } = globalThis.humhubStubs.client;

let sent;

beforeEach(() => {
    sent = [];
    vi.spyOn(vueModule, 'status').mockImplementation((...args) => sent.push(args));
});

describe('ui.status façade', () => {
    it('forwards each level to the bridge with the caller\'s closeAfter', () => {
        status.success('Saved');
        status.info('Heads up', 3000);
        status.warn('Careful', undefined, 500);
        status.error('Broken');

        expect(sent).toEqual([
            ['success', 'Saved', undefined, undefined],
            ['info', 'Heads up', undefined, 3000],
            ['warn', 'Careful', undefined, 500],
            ['error', 'Broken', undefined, undefined],
        ]);
    });

    it('passes string details through untouched', () => {
        status.error('Broken', 'Request failed: 500');

        expect(sent[0][2]).toBe('Request failed: 500');
    });

    it('flattens an Error into its stack, without repeating the message line', () => {
        const error = new Error('boom');
        error.stack = 'Error: boom\n    at somewhere';

        status.error('Broken', error);

        expect(sent[0][2]).toBe('Error: boom\n    at somewhere');
    });

    it('prepends the message when an engine leaves it out of the stack', () => {
        const error = new Error('boom');
        error.stack = '    at somewhere';

        status.error('Broken', error);

        expect(sent[0][2]).toBe('Error: boom\n    at somewhere');
    });

    it('flattens a client.Response into its log representation', () => {
        status.error('Broken', new Response({ status: 422, url: '/api/v2/comment' }));

        expect(JSON.parse(sent[0][2])).toEqual({ status: 422, url: '/api/v2/comment' });
    });

    it('flattens a jQuery-style {error: Error} envelope into message and stack fields', () => {
        const inner = new Error('inner boom');
        inner.stack = 'Error: inner boom\n    at deeper';

        status.error('Broken', { status: 500, error: inner });

        const details = JSON.parse(sent[0][2]);
        expect(details.status).toBe(500);
        expect(details.error).toBe('inner boom');
        expect(details.stack).toBe('Error: inner boom\n    at deeper');
    });

    it('renders any other details value as pretty JSON', () => {
        status.warn('Careful', { errors: { message: ['required'] } });

        expect(sent[0][2]).toBe(JSON.stringify({ errors: { message: ['required'] } }, null, 4));
    });

    it('decodes the entities Html::encode() produces, so pre-escaped callers stay readable', () => {
        status.success('Tom &amp; Jerry&#039;s &quot;file&quot;');

        expect(sent[0][1]).toBe('Tom & Jerry\'s "file"');
    });

    it('decodes in a single pass, so a doubly encoded entity stays encoded once', () => {
        status.info('&amp;lt;b&amp;gt;');

        expect(sent[0][1]).toBe('&lt;b&gt;');
    });

    it('leaves a message without entities alone, ampersand included', () => {
        status.info('Tom & Jerry');

        expect(sent[0][1]).toBe('Tom & Jerry');
    });

    it('maps the log setStatus event levels onto the status levels', () => {
        const trigger = (msg, details, level) => globalThis.humhubStubs.event
            .trigger('humhub:modules:log:setStatus', [msg, details, level]);

        trigger('fatal', 'trace', log.TRACE_FATAL);
        trigger('err', 'trace', log.TRACE_ERROR);
        trigger('warn', 'trace', log.TRACE_WARN);
        trigger('done', undefined, log.TRACE_SUCCESS);
        trigger('note', undefined, log.TRACE_INFO);

        expect(sent.map((args) => args[0])).toEqual(['error', 'error', 'warn', 'success', 'info']);
        // A success message never carries a trace block, matching the legacy switch.
        expect(sent[3][2]).toBeUndefined();
        expect(sent[0][2]).toBe('trace');
    });
});

describe('ui.status announce()', () => {
    const region = () => document.getElementById('status-announcer');

    beforeEach(() => vi.useFakeTimers());
    afterEach(() => {
        vi.clearAllTimers();
        vi.useRealTimers();
    });

    it('has its live region on the page before the first announcement', () => {
        expect(region()).not.toBeNull();
        expect(region().getAttribute('role')).toBe('status');
        expect(region().getAttribute('aria-atomic')).toBe('true');
        expect(region().className).toBe('visually-hidden');
        expect(region().textContent).toBe('');
    });

    it('says the message without a toast', () => {
        status.announce('Your comment has been added.');
        vi.advanceTimersByTime(100);

        expect(region().textContent).toBe('Your comment has been added.');
        expect(sent).toEqual([]);
    });

    it('says the same message again', () => {
        status.announce('You like this.');
        vi.advanceTimersByTime(100);

        status.announce('You like this.');
        // Emptied first, so that setting the same text is a change
        expect(region().textContent).toBe('');
        vi.advanceTimersByTime(100);
        expect(region().textContent).toBe('You like this.');
    });

    it('clears the message after a while, so it is not found at the end of the page', () => {
        status.announce('The content is public now.');
        vi.advanceTimersByTime(100 + 10000);

        expect(region().textContent).toBe('');
    });

    it('puts the message in as text', () => {
        status.announce('<img src=x onerror=alert(1)> &amp; more');
        vi.advanceTimersByTime(100);

        expect(region().textContent).toBe('<img src=x onerror=alert(1)> & more');
        expect(region().children.length).toBe(0);
    });

    it('is reached from Vue through the bridge', () => {
        vueModule.announce('From an island');
        vi.advanceTimersByTime(100);

        expect(region().textContent).toBe('From an island');
    });
});
