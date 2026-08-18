import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import LikeButton from '../../modules/like/vue/LikeButton.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// Registered once at file scope (the registry is shared/global per test
// file — a second register() call would just be a no-op debug log) so the
// registry+attribute-parsing integration test below can mount via the real
// <like-button> tag instead of @vue/test-utils' direct component mount.
vueModule.register('LikeButton', LikeButton);

// DOM fixture helper: build elements without HTML-string parsing (copied
// from vue.test.js).
const createTag = (tag, attributes = {}, parent = document.body) => {
    const el = document.createElement(tag);
    Object.entries(attributes).forEach(([name, value]) => el.setAttribute(name, value));
    parent.appendChild(el);
    return el;
};

describe('LikeButton', () => {
    beforeEach(() => {
        globalThis.humhub.modules.url.config.template = '/__route__';
        globalThis.humhub.config.module('user').isGuest = false;
        globalThis.humhub.config.module('user').loginUrl = '/user/auth/login';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ currentUserLiked: true, likeCounter: 3 }));
        globalThis.humhubStubs.logCalls.error.length = 0;
    });

    afterEach(() => {
        delete globalThis.humhub.modules.url.config.template;
        delete globalThis.humhub.config.module('user').isGuest;
        delete globalThis.humhub.config.module('user').loginUrl;
    });

    it('renders from provided initial state without fetching', () => {
        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: false },
        });

        expect(wrapper.find('a.like').exists()).toBe(true);
        expect(wrapper.find('a.unlike').exists()).toBe(false);
        expect(wrapper.find('.likeCount').text()).toBe('(2)');
        expect(wrapper.find('a[data-bs-target="#globalModal"]').attributes('href')).toBe(
            '/like/like/user-list?recordId=7',
        );
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('fetches state from the info endpoint when initial props are omitted', async () => {
        let resolveGet;
        globalThis.humhubStubs.client.get = vi.fn(() => new Promise((resolve) => { resolveGet = resolve; }));

        const wrapper = mount(LikeButton, { props: { recordId: 7 } });

        expect(wrapper.find('.likeLinkContainer').exists()).toBe(false);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/like/like/info?recordId=7');

        resolveGet({ currentUserLiked: true, likeCounter: 5 });
        await vi.waitFor(() => expect(wrapper.find('.likeLinkContainer').exists()).toBe(true));

        expect(wrapper.find('a.unlike').exists()).toBe(true);
        expect(wrapper.find('.likeCount').text()).toBe('(5)');
    });

    it('falls back to a visible, un-liked state when the info fetch fails', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('network error')));

        const wrapper = mount(LikeButton, { props: { recordId: 7 } });

        await vi.waitFor(() => expect(wrapper.find('.likeLinkContainer').exists()).toBe(true));

        expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0);
        expect(wrapper.find('a.like').exists()).toBe(true);
        expect(wrapper.find('a.unlike').exists()).toBe(false);
        expect(wrapper.find('.likeCount').exists()).toBe(false);
    });

    it('posts to the like url and switches state on click', async () => {
        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: false },
        });

        await wrapper.find('a.like').trigger('click');
        await vi.waitFor(() => expect(wrapper.find('a.unlike').exists()).toBe(true));

        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/like/like/like?recordId=7');
        expect(wrapper.find('.likeCount').text()).toBe('(3)');
    });

    it('posts to the unlike url when already liked', async () => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ currentUserLiked: false, likeCounter: 1 }));

        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: true },
        });

        await wrapper.find('a.unlike').trigger('click');
        await vi.waitFor(() => expect(wrapper.find('a.like').exists()).toBe(true));

        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/like/like/unlike?recordId=7');
        expect(wrapper.find('.likeCount').text()).toBe('(1)');
    });

    it('ignores clicks while a request is in flight and releases the guard after settle', async () => {
        let resolvePost;
        globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));

        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: false },
        });

        await wrapper.find('a.like').trigger('click');
        await wrapper.find('a.like').trigger('click');
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);

        resolvePost({ currentUserLiked: true, likeCounter: 3 });
        await vi.waitFor(() => expect(wrapper.find('a.unlike').exists()).toBe(true));

        await wrapper.find('a.unlike').trigger('click');
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(2);
    });

    it('keeps its state and logs when the toggle request fails', async () => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject(new Error('network error')));

        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: false },
        });

        await wrapper.find('a.like').trigger('click');
        await vi.waitFor(() => expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0));

        expect(wrapper.find('a.like').exists()).toBe(true);
        expect(wrapper.find('.likeCount').text()).toBe('(2)');
    });

    it('fires the legacy humhub:like:liked event on like, not on unlike', async () => {
        const container = document.createElement('div');
        document.body.appendChild(container);
        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: false },
            attachTo: container,
        });

        const liked = vi.fn();
        jQuery(document).on('humhub:like:liked', liked);

        await wrapper.find('a.like').trigger('click');
        await vi.waitFor(() => expect(liked).toHaveBeenCalled());

        jQuery(document).off('humhub:like:liked', liked);
        wrapper.unmount();
        container.remove();
    });

    it('does not fire humhub:like:liked on unlike', async () => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ currentUserLiked: false, likeCounter: 1 }));

        const container = document.createElement('div');
        document.body.appendChild(container);
        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: true },
            attachTo: container,
        });

        const liked = vi.fn();
        jQuery(document).on('humhub:like:liked', liked);

        await wrapper.find('a.unlike').trigger('click');
        await vi.waitFor(() => expect(wrapper.find('a.like').exists()).toBe(true));

        expect(liked).not.toHaveBeenCalled();

        jQuery(document).off('humhub:like:liked', liked);
        wrapper.unmount();
        container.remove();
    });

    it('hides the counter link when the count is zero', () => {
        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 0, currentUserLiked: false },
        });

        expect(wrapper.find('.likeLinkContainer').exists()).toBe(true);
        expect(wrapper.find('.likeCount').exists()).toBe(false);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('renders a login link and a non-interactive count for guests', () => {
        globalThis.humhub.config.module('user').isGuest = true;

        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 2, currentUserLiked: false },
        });

        const loginLink = wrapper.find('a[data-bs-target="#globalModal"]');
        expect(loginLink.exists()).toBe(true);
        expect(loginLink.attributes('href')).toBe('/user/auth/login');
        expect(loginLink.text()).toBe('Like');

        expect(wrapper.find('.likeCount').exists()).toBe(true);
        expect(wrapper.find('.likeCount').text()).toBe('(2)');
        expect(wrapper.find('.likeCount').element.tagName).not.toBe('A');

        expect(wrapper.find('a.like').exists()).toBe(false);
        expect(wrapper.find('a.unlike').exists()).toBe(false);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
    });

    it('renders only the login link for guests when the count is zero', () => {
        globalThis.humhub.config.module('user').isGuest = true;

        const wrapper = mount(LikeButton, {
            props: { recordId: 7, likeCount: 0, currentUserLiked: false },
        });

        expect(wrapper.find('a[data-bs-target="#globalModal"]').exists()).toBe(true);
        expect(wrapper.find('.likeCount').exists()).toBe(false);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
    });

    it('fetches the count for guests when no initial state is provided', async () => {
        globalThis.humhub.config.module('user').isGuest = true;
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ currentUserLiked: false, likeCounter: 4 }));

        const wrapper = mount(LikeButton, { props: { recordId: 7 } });

        expect(wrapper.find('.likeLinkContainer').exists()).toBe(false);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/like/like/info?recordId=7');

        await vi.waitFor(() => expect(wrapper.find('.likeLinkContainer').exists()).toBe(true));

        expect(wrapper.find('a[data-bs-target="#globalModal"]').exists()).toBe(true);
        expect(wrapper.find('.likeCount').text()).toBe('(4)');
        expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
    });

    it('mounts through the registry, coercing "false"/"0" attributes as ready state and fetching for bare ones', async () => {
        const readyEl = createTag('like-button', {
            'record-id': '7',
            'like-count': '0',
            'current-user-liked': 'false',
        });

        await vueModule.mountElement(readyEl);

        expect(jQuery(readyEl).find('a.like').length).toBe(1);
        expect(jQuery(readyEl).find('.likeCount').length).toBe(0);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();

        const fetchEl = createTag('like-button', { 'record-id': '8' });

        await vueModule.mountElement(fetchEl);

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/like/like/info?recordId=8');
    });
});
