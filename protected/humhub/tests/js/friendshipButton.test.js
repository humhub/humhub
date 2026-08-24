import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import FriendshipButton from '../../modules/friendship/vue/FriendshipButton.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

// FriendshipSerializer::state()
const state = (overrides = {}) => ({ state: 'none', isFollowing: false, ...overrides });

const mountButton = (props = {}) => mount(FriendshipButton, {
    props: { userId: 7, userName: 'Sara Schuster', ...props },
});

// The server-rendered UserFollowButton pair of a user, which the island toggles.
const createFollowButtons = (userId) => {
    const container = document.createElement('div');
    const create = (className, hidden) => {
        const element = document.createElement('a');
        element.className = hidden ? `${className} d-none` : className;
        element.setAttribute('data-content-container-id', String(userId));
        container.appendChild(element);

        return element;
    };

    const unfollow = create('unfollowButton', true);
    const follow = create('followButton', false);
    document.body.appendChild(container);

    return { follow, unfollow };
};

describe('FriendshipButton', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhub.modules.url.config.template = '/__route__';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(state()));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(state({ state: 'requestSent', isFollowing: true })));
        // DELETE goes through the vue bridge's del() → client.ajax()
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve(state()));
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        globalThis.humhubStubs.logCalls.error.length = 0;
    });

    it('renders the add button from the inlined state without fetching', () => {
        const wrapper = mountButton({ initial: state(), plusIconHtml: '<i class="fa fa-plus"></i>' });

        const button = wrapper.find('a');
        expect(button.text()).toBe('Friends');
        expect(button.find('i.fa-plus').exists()).toBe(true);
        expect(button.classes()).toContain('btn-accent');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('fetches the state when none was inlined', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(state({ state: 'friends' })));
        const wrapper = mountButton();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/user/7/friendship');
        await flushPromises();

        expect(wrapper.text()).toBe('Friends');
    });

    it('sends a request after confirming, naming the other user', async () => {
        const wrapper = mountButton({ initial: state() });

        await wrapper.find('a').trigger('click');
        await flushPromises();

        const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
        expect(options.body).toContain('send a friendship request');
        expect(options.body).toContain('<strong>Sara Schuster</strong>');
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/user/7/friendship');
        expect(wrapper.text()).toBe('Pending');
    });

    it('sends nothing when the confirmation is cancelled', async () => {
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(false));
        const wrapper = mountButton({ initial: state() });

        await wrapper.find('a').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
        expect(wrapper.text()).toBe('Friends');
    });

    it('does not send a second request while one is in flight', async () => {
        let resolvePost;
        globalThis.humhubStubs.client.post = vi.fn(() => new Promise((resolve) => {
            resolvePost = resolve;
        }));
        const wrapper = mountButton({ initial: state() });

        await wrapper.find('a').trigger('click');
        await flushPromises();
        await wrapper.find('a').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledTimes(1);
        resolvePost(state({ state: 'requestSent' }));
        await flushPromises();
    });

    it('withdraws a sent request through DELETE', async () => {
        const wrapper = mountButton({ initial: state({ state: 'requestSent' }) });

        expect(wrapper.text()).toBe('Pending');
        await wrapper.find('a').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.modal.confirm.mock.calls[0][0].body)
            .toContain('withdraw your friendship request');
        const [url, cfg] = globalThis.humhubStubs.client.ajax.mock.calls[0];
        expect(url).toBe('/api/v2/user/7/friendship');
        expect(cfg.method).toBe('DELETE');
        expect(wrapper.text()).toBe('Friends');
    });

    describe('received request', () => {
        const received = () => state({ state: 'requestReceived' });

        it('renders the accept button with a deny entry', () => {
            const wrapper = mountButton({ initial: received(), timesIconHtml: '<i class="fa fa-times"></i>' });

            expect(wrapper.find('.btn-group').exists()).toBe(true);
            expect(wrapper.find('.dropdown-toggle').exists()).toBe(true);
            expect(wrapper.find('.btn-group > a').text()).toBe('Accept Friend Request');
            expect(wrapper.find('.dropdown-menu a').text()).toBe('Deny friend request');
            expect(wrapper.find('.dropdown-menu i.fa-times').exists()).toBe(true);
        });

        it('accepts through the same POST that sends a request', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve(state({ state: 'friends', isFollowing: true })));
            const wrapper = mountButton({ initial: received() });

            await wrapper.find('.btn-group > a').trigger('click');
            await flushPromises();

            expect(globalThis.humhubStubs.modal.confirm.mock.calls[0][0].body)
                .toContain('accept the friendship request');
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/user/7/friendship');
            expect(wrapper.text()).toBe('Friends');
        });

        it('denies through DELETE', async () => {
            const wrapper = mountButton({ initial: received() });

            await wrapper.find('.dropdown-menu a').trigger('click');
            await flushPromises();

            expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalled();
            expect(wrapper.text()).toBe('Friends');
        });
    });

    it('ends a friendship after confirming', async () => {
        const wrapper = mountButton({
            initial: state({ state: 'friends', isFollowing: true }),
            checkIconHtml: '<i class="fa fa-check"></i>',
        });

        expect(wrapper.find('i.fa-check').exists()).toBe(true);
        await wrapper.find('a').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.modal.confirm.mock.calls[0][0].body)
            .toContain('end your friendship');
        expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalled();
    });

    it('escapes the display name it puts into the dialog', async () => {
        const wrapper = mountButton({
            initial: state(),
            userName: '<img src=x onerror=alert(1)>',
        });

        await wrapper.find('a').trigger('click');
        await flushPromises();

        const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
        expect(options.body).toContain('&lt;img src=x onerror=alert(1)&gt;');
        expect(options.body).not.toContain('<img');
    });

    describe('follow buttons', () => {
        it('shows the unfollow button once the request made the viewer follow', async () => {
            const buttons = createFollowButtons(7);
            const wrapper = mountButton({ initial: state() });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            expect(buttons.unfollow.classList.contains('d-none')).toBe(false);
            expect(buttons.follow.classList.contains('d-none')).toBe(true);
        });

        it('leaves the buttons of another user alone', async () => {
            const other = createFollowButtons(9);
            const wrapper = mountButton({ initial: state() });

            await wrapper.find('a').trigger('click');
            await flushPromises();

            expect(other.follow.classList.contains('d-none')).toBe(false);
            expect(other.unfollow.classList.contains('d-none')).toBe(true);
        });
    });

    it('logs a failed transition and keeps the current state', async () => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ status: 403 }));
        const wrapper = mountButton({ initial: state() });

        await wrapper.find('a').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
        expect(wrapper.text()).toBe('Friends');
    });
});
