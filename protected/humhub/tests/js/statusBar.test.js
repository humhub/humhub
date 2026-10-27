import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import StatusBar from '../../vue/StatusBar.vue';

// The component talks to the real bridge (it registers itself as the status
// handler on mount), so the runtime module has to be loaded here as well.
await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// Mirrors the constants in StatusBar.vue - the duration of the CSS transition in
// Bootstrap's `.fade` the toast enters and exits with, and the legacy AUTOCLOSE_* values.
const TRANSITION = 150;
const AUTOCLOSE = { info: 6000, success: 2000, warn: 10000, error: 0 };

let wrapper;

const mountBar = () => {
    wrapper = mount(StatusBar, { attachTo: document.body });
    return wrapper;
};

const body = () => wrapper.find('.toast-container');
const content = () => wrapper.find('.toast');
// Bootstrap's toast states: `.show` always, `.showing` while fading in or out.
const shown = () => content().exists() && content().classes().includes('show') && !content().classes().includes('showing');

beforeEach(() => {
    vi.useFakeTimers();
    // Drop anything a previous test queued, then re-arm queueing (public API only).
    vueModule.setStatusHandler(() => {});
    vueModule.setStatusHandler(null);
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
    vi.useRealTimers();
});

describe('StatusBar', () => {
    it('renders nothing until a message arrives', () => {
        mountBar();

        expect(body().exists()).toBe(false);
    });

    it('registers as the bridge status handler on mount and drains what was queued before', async () => {
        vueModule.status('info', 'queued before mount');

        mountBar();
        await vi.runOnlyPendingTimersAsync();

        expect(content().find('.toast-message').text()).toBe('queued before mount');
    });

    it('deregisters on unmount so later messages are queued instead of lost', async () => {
        mountBar();
        await vi.runOnlyPendingTimersAsync();
        wrapper.unmount();
        wrapper = undefined;

        vueModule.status('info', 'after unmount');

        // Nothing threw, and a freshly mounted bar picks the message up.
        mountBar();
        await vi.runOnlyPendingTimersAsync();
        expect(content().find('.toast-message').text()).toBe('after unmount');
    });

    it.each([
        ['info', 'toast-icon ti ti-info-circle', 'toast-info'],
        ['success', 'toast-icon ti ti-circle-check', 'toast-success'],
        ['warn', 'toast-icon ti ti-alert-triangle', 'toast-warning'],
        ['error', 'toast-icon ti ti-alert-circle', 'toast-danger'],
    ])('renders the level icon and the card tone class for a %s message', async (level, iconClass, toneClass) => {
        mountBar();
        vueModule.status(level, 'the message');
        await vi.runOnlyPendingTimersAsync();

        expect(content().classes()).toContain(toneClass);
        expect(content().find('.toast-icon').attributes('class')).toBe(iconClass);
        expect(content().find('.toast-message').text()).toBe('the message');
        expect(content().find('button.toast-close').exists()).toBe(true);
    });

    it('renders the message as text, never as markup', async () => {
        mountBar();
        vueModule.status('info', '<b>bold</b> & fun');
        await vi.runOnlyPendingTimersAsync();

        expect(content().find('.toast-message').text()).toBe('<b>bold</b> & fun');
        expect(content().find('.toast-message b').exists()).toBe(false);
    });

    it('fades in by dropping .showing one tick after the message arrives', async () => {
        mountBar();
        vueModule.status('info', 'sliding');
        await wrapper.vm.$nextTick();

        expect(body().exists()).toBe(true);
        expect(shown()).toBe(false);

        // The component sets `visible` in its own nextTick callback, so the class
        // lands one render flush later - hence flushPromises rather than a tick.
        await flushPromises();
        expect(shown()).toBe(true);
    });

    it.each([
        ['info', AUTOCLOSE.info],
        ['success', AUTOCLOSE.success],
        ['warn', AUTOCLOSE.warn],
    ])('auto-closes a %s message after its default timeout', async (level, timeout) => {
        mountBar();
        vueModule.status(level, 'temporary');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(shown()).toBe(true);

        await vi.advanceTimersByTimeAsync(timeout - 1);
        expect(shown()).toBe(true);

        await vi.advanceTimersByTimeAsync(1);
        expect(shown()).toBe(false);

        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(body().exists()).toBe(false);
    });

    it('never auto-closes an error message', async () => {
        mountBar();
        vueModule.status('error', 'stays');
        await vi.advanceTimersByTimeAsync(TRANSITION + 60000);

        expect(shown()).toBe(true);
    });

    it('honours an explicit closeAfter, and falls back to the level default for a falsy one', async () => {
        mountBar();
        vueModule.status('info', 'quick', undefined, 1000);
        await vi.advanceTimersByTimeAsync(TRANSITION + 1000);
        expect(shown()).toBe(false);
        await vi.advanceTimersByTimeAsync(TRANSITION);

        // Legacy quirk kept on purpose: `closeAfter || AUTOCLOSE_X` in
        // humhub.ui.status.js means 0 does NOT mean "stay" for these levels.
        vueModule.status('info', 'default', undefined, 0);
        await vi.advanceTimersByTimeAsync(TRANSITION + AUTOCLOSE.info - 1);
        expect(shown()).toBe(true);
        await vi.advanceTimersByTimeAsync(1);
        expect(shown()).toBe(false);
    });

    it('replaces a visible message: the old one fades out before the new one appears', async () => {
        mountBar();
        vueModule.status('error', 'first');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(content().find('.toast-message').text()).toBe('first');

        vueModule.status('success', 'second');
        await wrapper.vm.$nextTick();

        // Still the old message, now sliding out.
        expect(content().find('.toast-message').text()).toBe('first');
        expect(shown()).toBe(false);

        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(content().find('.toast-message').text()).toBe('second');
        expect(content().find('.toast-icon').attributes('class')).toBe('toast-icon ti ti-circle-check');
    });

    it('closes on a click on the close button', async () => {
        mountBar();
        vueModule.status('error', 'closable');
        await vi.advanceTimersByTimeAsync(TRANSITION);

        await content().find('button.toast-close').trigger('click');
        expect(shown()).toBe(false);

        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(body().exists()).toBe(false);
    });

    it('offers a details toggle only when details were passed, and renders them in a pre block', async () => {
        mountBar();
        vueModule.status('error', 'no details');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(content().find('button.toast-details-toggle').exists()).toBe(false);

        vueModule.status('error', 'with details', 'Request failed: 500');
        await vi.advanceTimersByTimeAsync(TRANSITION * 2);

        const toggle = content().find('button.toast-details-toggle');
        expect(toggle.exists()).toBe(true);
        expect(content().find('.toast-details').exists()).toBe(false);
        expect(toggle.find('i').attributes('class')).toBe('ti ti-chevron-up');

        await toggle.trigger('click');
        expect(content().find('.toast-details pre').text()).toBe('Request failed: 500');
        expect(content().find('button.toast-details-toggle i').attributes('class')).toBe('ti ti-chevron-down');

        await toggle.trigger('click');
        expect(content().find('.toast-details').exists()).toBe(false);
    });

    it('toggles the details when the message text itself is clicked, like the legacy bar did', async () => {
        mountBar();
        vueModule.status('error', 'clickable', 'the trace');
        await vi.advanceTimersByTimeAsync(TRANSITION);

        expect(content().find('.toast-message').classes()).toContain('is-toggle');
        await content().find('.toast-message').trigger('click');

        expect(content().find('.toast-details pre').text()).toBe('the trace');
    });

    it('renders an Error object as message plus stack, and any other value as pretty JSON', async () => {
        mountBar();
        const error = new Error('boom');
        error.stack = 'Error: boom\n    at somewhere';
        vueModule.status('error', 'failed', error);
        await vi.advanceTimersByTimeAsync(TRANSITION);
        await content().find('button.toast-details-toggle').trigger('click');

        expect(content().find('.toast-details pre').text()).toContain('boom');
        expect(content().find('.toast-details pre').text()).toContain('at somewhere');

        vueModule.status('error', 'failed again', { status: 422, errors: { message: 'required' } });
        await vi.advanceTimersByTimeAsync(TRANSITION * 2);
        await content().find('button.toast-details-toggle').trigger('click');

        const text = content().find('.toast-details pre').text();
        expect(text).toContain('"status": 422');
        expect(text).toContain('"required"');
    });

    it('forgets an open details block when the next message arrives', async () => {
        mountBar();
        vueModule.status('error', 'first', 'details one');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        await content().find('button.toast-details-toggle').trigger('click');
        expect(content().find('.toast-details').exists()).toBe(true);

        vueModule.status('error', 'second', 'details two');
        await vi.advanceTimersByTimeAsync(TRANSITION * 2);

        expect(content().find('.toast-message').text()).toBe('second');
        expect(content().find('.toast-details').exists()).toBe(false);
    });

    it('is a Bootstrap toast fading in with .fade, .show and .showing', async () => {
        mountBar();
        vueModule.status('success', 'faded');
        await wrapper.vm.$nextTick();

        expect(content().classes()).toEqual(expect.arrayContaining(['toast', 'fade', 'show', 'showing', 'toast-success']));

        await flushPromises();
        expect(content().classes()).not.toContain('showing');
    });

    it('pauses the auto-close while the pointer is on the toast and starts over when it leaves', async () => {
        mountBar();
        vueModule.status('success', 'hover me');
        await vi.advanceTimersByTimeAsync(TRANSITION);

        await content().trigger('mouseenter');
        await vi.advanceTimersByTimeAsync(AUTOCLOSE.success * 3);
        expect(shown()).toBe(true);

        await content().trigger('mouseleave');
        await vi.advanceTimersByTimeAsync(AUTOCLOSE.success - 1);
        expect(shown()).toBe(true);
        await vi.advanceTimersByTimeAsync(1);
        expect(shown()).toBe(false);
    });

    it('pauses the auto-close while the focus is in the toast', async () => {
        mountBar();
        vueModule.status('info', 'focus me');
        await vi.advanceTimersByTimeAsync(TRANSITION);

        await content().trigger('focusin');
        await vi.advanceTimersByTimeAsync(AUTOCLOSE.info * 2);
        expect(shown()).toBe(true);

        // Focus moving between the toast's own buttons keeps the pause.
        const close = content().find('button.toast-close').element;
        await content().trigger('focusout', { relatedTarget: close });
        await vi.advanceTimersByTimeAsync(AUTOCLOSE.info * 2);
        expect(shown()).toBe(true);

        await content().trigger('focusout', { relatedTarget: null });
        await vi.advanceTimersByTimeAsync(AUTOCLOSE.info);
        expect(shown()).toBe(false);
    });
});
