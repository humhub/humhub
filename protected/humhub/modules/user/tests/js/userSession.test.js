import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const $ = globalThis.$;
const TIMEOUT = 600; // seconds
const WARN_AT = (TIMEOUT - 120) * 1000;

// Bootstrap's modal, as far as the module uses it: shown with the `show` class, and its events
globalThis.bootstrap = {
    Modal: {
        getOrCreateInstance: (el) => ({
            show() {
                if (!el.classList.contains('show')) {
                    el.classList.add('show');
                    $(el).trigger('shown.bs.modal');
                }
            },
            hide() {
                if (el.classList.contains('show')) {
                    el.classList.remove('show');
                    $(el).trigger('hidden.bs.modal');
                }
            },
        }),
    },
};

const config = globalThis.humhub.config.module('user.session');
const dialog = () => document.getElementById('user-session-dialog');
const isOpen = () => !!dialog() && dialog().classList.contains('show');
const title = () => dialog().querySelector('.modal-title').textContent;
const button = () => dialog().querySelector('.modal-footer .btn');

// Each test gets a module of its own, as on a fresh page
const load = () => import('../../resources/js/humhub.user.session.js?t=' + Math.random());

let post;
let listeners;

// The listeners a module adds to the window and the document, removed after its test
const track = (target) => {
    const add = target.addEventListener;
    vi.spyOn(target, 'addEventListener').mockImplementation(function (...args) {
        listeners.push([target, args]);
        return add.apply(this, args);
    });
};

beforeEach(async () => {
    listeners = [];
    track(window);
    track(document);
    vi.useFakeTimers();
    $(document).off('ajaxSuccess.humhubUserSession');
    document.body.replaceChildren();
    window.localStorage.clear();
    Object.assign(config, {
        timeout: TIMEOUT,
        keepAliveUrl: '/user/session/keep-alive',
        text: {
            warningTitle: 'Are you still there?',
            warning: 'Nothing has happened for a while. For your security, you will be signed out in {time} minutes.',
            stay: 'Stay signed in',
            endedTitle: 'You have been signed out',
            ended: 'Nothing has happened for a while, so you have been signed out for your security.',
            signIn: 'Sign in again',
        },
    });
    post = vi.fn(() => Promise.resolve({}));
    globalThis.humhubStubs.client.post = post;
});

afterEach(() => {
    vi.restoreAllMocks();
    listeners.forEach(([target, args]) => target.removeEventListener(...args));
    vi.clearAllTimers();
    vi.useRealTimers();
});

describe('user.session', () => {
    it('asks two minutes before the session ends, with the time left', async () => {
        await load();

        await vi.advanceTimersByTimeAsync(WARN_AT - 1000);
        expect(isOpen()).toBe(false);

        await vi.advanceTimersByTimeAsync(1000);
        expect(isOpen()).toBe(true);
        expect(title()).toBe('Are you still there?');
        expect(dialog().getAttribute('aria-labelledby')).toBe('user-session-dialog-title');
        expect(document.getElementById(dialog().getAttribute('aria-describedby')).textContent)
            .toBe('Nothing has happened for a while. For your security, you will be signed out in 2:00 minutes.');
        expect(document.activeElement).toBe(button());
        expect(button().textContent).toBe('Stay signed in');

        await vi.advanceTimersByTimeAsync(5000);
        expect(dialog().querySelector('.modal-body p').textContent).toContain('in 1:55 minutes');
        expect(post).not.toHaveBeenCalled();
    });

    it('extends the session with the button', async () => {
        await load();
        await vi.advanceTimersByTimeAsync(WARN_AT);

        button().click();
        await vi.advanceTimersByTimeAsync(0);

        expect(isOpen()).toBe(false);
        expect(post).toHaveBeenCalledWith('/user/session/keep-alive', { dataType: 'json' });

        // The time starts over
        await vi.advanceTimersByTimeAsync(WARN_AT - 1000);
        expect(isOpen()).toBe(false);
        await vi.advanceTimersByTimeAsync(1000);
        expect(isOpen()).toBe(true);
    });

    it('extends the session silently while the user is working on the page', async () => {
        await load();
        await vi.advanceTimersByTimeAsync(60 * 1000);
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'a' }));

        await vi.advanceTimersByTimeAsync(WARN_AT);

        expect(post).toHaveBeenCalledTimes(1);
        expect(isOpen()).toBe(false);
    });

    it('counts from the last request of the page', async () => {
        await load();
        await vi.advanceTimersByTimeAsync(300 * 1000);
        $(document).trigger('ajaxSuccess');

        await vi.advanceTimersByTimeAsync(WARN_AT - 1000);
        expect(isOpen()).toBe(false);
        await vi.advanceTimersByTimeAsync(1000);
        expect(isOpen()).toBe(true);
    });

    it('closes the question when another tab extended the session', async () => {
        await load();
        await vi.advanceTimersByTimeAsync(WARN_AT);
        expect(isOpen()).toBe(true);

        window.dispatchEvent(new StorageEvent('storage', {
            key: 'humhub.user.session.contact',
            newValue: String(Date.now()),
        }));

        expect(isOpen()).toBe(false);
        expect(post).not.toHaveBeenCalled();
    });

    it('says the session ended once the time is up', async () => {
        await load();
        await vi.advanceTimersByTimeAsync(TIMEOUT * 1000);

        expect(isOpen()).toBe(true);
        expect(title()).toBe('You have been signed out');
        expect(button().textContent).toBe('Sign in again');
        // The open question turned into this: said by the alert, the focus stays on the button
        expect(dialog().querySelector('[role="alert"]').textContent)
            .toBe('You have been signed out. Nothing has happened for a while, so you have been signed out for your security.');
        expect(post).not.toHaveBeenCalled();
    });

    it('says the session ended when the server no longer extends it', async () => {
        post.mockImplementation(() => Promise.reject({ status: 403 }));
        await load();
        await vi.advanceTimersByTimeAsync(WARN_AT);

        button().click();
        await vi.advanceTimersByTimeAsync(0);

        expect(isOpen()).toBe(true);
        expect(title()).toBe('You have been signed out');
    });

    it('does nothing without a time limit (a remembered login)', async () => {
        config.timeout = 0;
        await load();

        await vi.advanceTimersByTimeAsync(TIMEOUT * 2000);

        expect(dialog()).toBeNull();
        expect(post).not.toHaveBeenCalled();
    });
});
