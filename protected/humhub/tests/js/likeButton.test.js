import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import LikeButton from '../../modules/like/resources/vue/LikeButton.vue';

await import('../../resources/js/humhub/humhub.vue.js');

const defaultProps = {
    likeUrl: '/like/like/like?recordId=7',
    unlikeUrl: '/like/like/unlike?recordId=7',
    userListUrl: '/like/like/user-list?recordId=7',
    likeCount: 2,
    currentUserLiked: false,
    title: 'User1',
};

describe('LikeButton', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ currentUserLiked: true, likeCounter: 3 }));
    });

    it('renders the like state from its props', () => {
        const wrapper = mount(LikeButton, { props: defaultProps });
        expect(wrapper.find('a.like').exists()).toBe(true);
        expect(wrapper.find('a.unlike').exists()).toBe(false);
        expect(wrapper.find('.likeCount').text()).toBe('(2)');
        expect(wrapper.find('a[data-bs-target="#globalModal"]').attributes('href')).toBe(defaultProps.userListUrl);
    });

    it('posts to likeUrl and switches to unlike on click', async () => {
        const wrapper = mount(LikeButton, { props: defaultProps });
        await wrapper.find('a.like').trigger('click');
        await vi.waitFor(() => expect(wrapper.find('a.unlike').exists()).toBe(true));
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(defaultProps.likeUrl);
        expect(wrapper.find('.likeCount').text()).toBe('(3)');
    });

    it('posts to unlikeUrl when already liked', async () => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ currentUserLiked: false, likeCounter: 1 }));
        const wrapper = mount(LikeButton, { props: { ...defaultProps, currentUserLiked: true } });
        await wrapper.find('a.unlike').trigger('click');
        await vi.waitFor(() => expect(wrapper.find('a.like').exists()).toBe(true));
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith(defaultProps.unlikeUrl);
    });

    it('hides the counter link when the count is zero', () => {
        const wrapper = mount(LikeButton, { props: { ...defaultProps, likeCount: 0 } });
        expect(wrapper.find('.likeCount').exists()).toBe(false);
    });

    it('fires the legacy humhub:like:liked event on like', async () => {
        const container = document.createElement('div');
        document.body.appendChild(container);
        const wrapper = mount(LikeButton, { props: defaultProps, attachTo: container });

        const liked = vi.fn();
        jQuery(document).on('humhub:like:liked', liked);

        await wrapper.find('a.like').trigger('click');
        await vi.waitFor(() => expect(liked).toHaveBeenCalled());

        jQuery(document).off('humhub:like:liked', liked);
        wrapper.unmount();
        container.remove();
    });

    it('ignores clicks while a request is in flight', async () => {
        let resolvePost;
        globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => { resolvePost = resolve; }));

        const wrapper = mount(LikeButton, { props: defaultProps });

        await wrapper.find('a.like').trigger('click');
        await wrapper.find('a.like').trigger('click');
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);

        resolvePost({ currentUserLiked: true, likeCounter: 3 });
        await vi.waitFor(() => expect(wrapper.find('a.unlike').exists()).toBe(true));

        await wrapper.find('a.unlike').trigger('click');
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(2);
    });

    it('keeps its state and logs when the request fails', async () => {
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject(new Error('network error')));

        const wrapper = mount(LikeButton, { props: defaultProps });
        await wrapper.find('a.like').trigger('click');
        await vi.waitFor(() => expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0));

        expect(wrapper.find('a.like').exists()).toBe(true);
        expect(wrapper.find('.likeCount').text()).toBe('(2)');
    });

    it('does not fire humhub:like:liked on unlike', async () => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ currentUserLiked: false, likeCounter: 1 }));

        const container = document.createElement('div');
        document.body.appendChild(container);
        const wrapper = mount(LikeButton, {
            props: { ...defaultProps, currentUserLiked: true },
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
});
