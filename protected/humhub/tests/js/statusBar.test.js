import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import StatusBar from '../../vue/StatusBar.vue';

// The component talks to the real bridge (it registers itself as the status
// handler on mount), so the runtime module has to be loaded here as well.
await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// Mirrors the constants in StatusBar.vue - the slide duration the CSS transition
// in _user-feedback.scss uses, and the legacy AUTOCLOSE_* values.
const TRANSITION = 500;
const AUTOCLOSE = { info: 6000, success: 2000, warn: 10000, error: 0 };

let wrapper;

const mountBar = () => {
    wrapper = mount(StatusBar, { attachTo: document.body });
    return wrapper;
};

const body = () => wrapper.find('.status-bar-body');
const content = () => wrapper.find('.status-bar-content');

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

        expect(content().find('span').text()).toBe('queued before mount');
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
        expect(content().find('span').text()).toBe('after unmount');
    });

    it.each([
        ['info', 'fa fa-info-circle info'],
        ['success', 'fa fa-check-circle success'],
        ['warn', 'fa fa-exclamation-triangle warning'],
        ['error', 'fa fa-exclamation-circle error'],
    ])('renders the legacy icon markup for a %s message', async (level, iconClass) => {
        mountBar();
        vueModule.status(level, 'the message');
        await vi.runOnlyPendingTimersAsync();

        expect(content().find('i').attributes('class')).toBe(iconClass);
        expect(content().find('span').text()).toBe('the message');
        expect(content().find('a.status-bar-close').exists()).toBe(true);
    });

    it('renders the message as text, never as markup', async () => {
        mountBar();
        vueModule.status('info', '<b>bold</b> & fun');
        await vi.runOnlyPendingTimersAsync();

        expect(content().find('span').text()).toBe('<b>bold</b> & fun');
        expect(content().find('span b').exists()).toBe(false);
    });

    it('slides in by gaining the visible class one tick after the message arrives', async () => {
        mountBar();
        vueModule.status('info', 'sliding');
        await wrapper.vm.$nextTick();

        expect(body().exists()).toBe(true);
        expect(body().classes()).not.toContain('status-bar-visible');

        // The component sets `visible` in its own nextTick callback, so the class
        // lands one render flush later - hence flushPromises rather than a tick.
        await flushPromises();
        expect(body().classes()).toContain('status-bar-visible');
    });

    it.each([
        ['info', AUTOCLOSE.info],
        ['success', AUTOCLOSE.success],
        ['warn', AUTOCLOSE.warn],
    ])('auto-closes a %s message after its default timeout', async (level, timeout) => {
        mountBar();
        vueModule.status(level, 'temporary');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(body().classes()).toContain('status-bar-visible');

        await vi.advanceTimersByTimeAsync(timeout - 1);
        expect(body().classes()).toContain('status-bar-visible');

        await vi.advanceTimersByTimeAsync(1);
        expect(body().classes()).not.toContain('status-bar-visible');

        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(body().exists()).toBe(false);
    });

    it('never auto-closes an error message', async () => {
        mountBar();
        vueModule.status('error', 'stays');
        await vi.advanceTimersByTimeAsync(TRANSITION + 60000);

        expect(body().classes()).toContain('status-bar-visible');
    });

    it('honours an explicit closeAfter, and falls back to the level default for a falsy one', async () => {
        mountBar();
        vueModule.status('info', 'quick', undefined, 1000);
        await vi.advanceTimersByTimeAsync(TRANSITION + 1000);
        expect(body().classes()).not.toContain('status-bar-visible');
        await vi.advanceTimersByTimeAsync(TRANSITION);

        // Legacy quirk kept on purpose: `closeAfter || AUTOCLOSE_X` in
        // humhub.ui.status.js means 0 does NOT mean "stay" for these levels.
        vueModule.status('info', 'default', undefined, 0);
        await vi.advanceTimersByTimeAsync(TRANSITION + AUTOCLOSE.info - 1);
        expect(body().classes()).toContain('status-bar-visible');
        await vi.advanceTimersByTimeAsync(1);
        expect(body().classes()).not.toContain('status-bar-visible');
    });

    it('replaces a visible message: the old one slides out before the new one appears', async () => {
        mountBar();
        vueModule.status('error', 'first');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(content().find('span').text()).toBe('first');

        vueModule.status('success', 'second');
        await wrapper.vm.$nextTick();

        // Still the old message, now sliding out.
        expect(content().find('span').text()).toBe('first');
        expect(body().classes()).not.toContain('status-bar-visible');

        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(content().find('span').text()).toBe('second');
        expect(content().find('i').attributes('class')).toBe('fa fa-check-circle success');
    });

    it('closes on a click on the close button', async () => {
        mountBar();
        vueModule.status('error', 'closable');
        await vi.advanceTimersByTimeAsync(TRANSITION);

        await content().find('a.status-bar-close').trigger('click');
        expect(body().classes()).not.toContain('status-bar-visible');

        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(body().exists()).toBe(false);
    });

    it('offers a details toggle only when details were passed, and renders them in a pre block', async () => {
        mountBar();
        vueModule.status('error', 'no details');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        expect(content().find('a.showMore').exists()).toBe(false);

        vueModule.status('error', 'with details', 'Request failed: 500');
        await vi.advanceTimersByTimeAsync(TRANSITION * 2);

        const toggle = content().find('a.showMore');
        expect(toggle.exists()).toBe(true);
        expect(content().find('.status-bar-details').exists()).toBe(false);
        expect(toggle.find('i').attributes('class')).toBe('fa fa-angle-up');

        await toggle.trigger('click');
        expect(content().find('.status-bar-details pre').text()).toBe('Request failed: 500');
        expect(content().find('a.showMore i').attributes('class')).toBe('fa fa-angle-down');

        await toggle.trigger('click');
        expect(content().find('.status-bar-details').exists()).toBe(false);
    });

    it('toggles the details when the message text itself is clicked, like the legacy bar did', async () => {
        mountBar();
        vueModule.status('error', 'clickable', 'the trace');
        await vi.advanceTimersByTimeAsync(TRANSITION);

        expect(content().find('span').classes()).toContain('status-bar-toggle');
        await content().find('span').trigger('click');

        expect(content().find('.status-bar-details pre').text()).toBe('the trace');
    });

    it('renders an Error object as message plus stack, and any other value as pretty JSON', async () => {
        mountBar();
        const error = new Error('boom');
        error.stack = 'Error: boom\n    at somewhere';
        vueModule.status('error', 'failed', error);
        await vi.advanceTimersByTimeAsync(TRANSITION);
        await content().find('a.showMore').trigger('click');

        expect(content().find('.status-bar-details pre').text()).toContain('boom');
        expect(content().find('.status-bar-details pre').text()).toContain('at somewhere');

        vueModule.status('error', 'failed again', { status: 422, errors: { message: 'required' } });
        await vi.advanceTimersByTimeAsync(TRANSITION * 2);
        await content().find('a.showMore').trigger('click');

        const text = content().find('.status-bar-details pre').text();
        expect(text).toContain('"status": 422');
        expect(text).toContain('"required"');
    });

    it('forgets an open details block when the next message arrives', async () => {
        mountBar();
        vueModule.status('error', 'first', 'details one');
        await vi.advanceTimersByTimeAsync(TRANSITION);
        await content().find('a.showMore').trigger('click');
        expect(content().find('.status-bar-details').exists()).toBe(true);

        vueModule.status('error', 'second', 'details two');
        await vi.advanceTimersByTimeAsync(TRANSITION * 2);

        expect(content().find('span').text()).toBe('second');
        expect(content().find('.status-bar-details').exists()).toBe(false);
    });
});
