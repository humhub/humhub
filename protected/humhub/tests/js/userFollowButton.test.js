import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import UserFollowButton from '../../modules/user/vue/UserFollowButton.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const FOLLOW_CHANGED = 'user:follow-changed';
const FRIENDSHIP_CHANGED = 'user:friendship-changed';

// PUT (follow) and DELETE (unfollow) both go through the vue bridge's put()/del() → client.ajax().
let put;
let del;
const dispatchAjax = (url, cfg) => (cfg.method === 'PUT' ? put : del)(url, cfg);

// The `user/<id>/follow` response.
const followState = (overrides = {}) => ({
    isFollowing: false,
    followerCount: 3,
    canFollow: true,
    ...overrides,
});

const mountButton = (props = {}) => mount(UserFollowButton, {
    props: { userId: 7, userName: 'Sara Schuster', ...props },
    attachTo: document.body,
});

// Everything dispatched on the bridge's bus for `type`, as the payloads.
const listen = (type) => {
    const payloads = [];
    globalThis.humhubStubs.event.on(type, (event, payload) => payloads.push(payload));

    return payloads;
};

const trigger = (type, payload) => globalThis.humhubStubs.event.trigger(type, [payload]);

describe('UserFollowButton', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhub.modules.url.config.template = '/__route__';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(followState()));
        put = vi.fn(() => Promise.resolve(followState({ isFollowing: true, followerCount: 4 })));
        del = vi.fn(() => Promise.resolve(followState()));
        globalThis.humhubStubs.client.ajax = vi.fn(dispatchAjax);
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.event._handlers.clear();
    });

    it('renders "Follow" from the inlined state without fetching', () => {
        const wrapper = mountButton({ initial: followState(), followClass: 'btn btn-accent' });

        const button = wrapper.find('button');
        expect(button.text()).toBe('Follow');
        expect(button.classes()).toEqual(['btn', 'btn-accent']);
        expect(button.attributes('aria-pressed')).toBe('false');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('shows the "Follow" icon while not following, the check while following', async () => {
        const wrapper = mountButton({
            initial: followState(),
            followIconHtml: '<i class="ti ti-send"></i>',
            checkIconHtml: '<i class="ti ti-check"></i>',
        });

        expect(wrapper.find('button .ti-send').exists()).toBe(true);
        expect(wrapper.find('button .ti-check').exists()).toBe(false);

        await wrapper.find('button').trigger('click');
        await flushPromises();
        expect(wrapper.find('button .ti-send').exists()).toBe(false);
        expect(wrapper.find('button .ti-check').exists()).toBe(true);
        expect(wrapper.find('button').text()).toBe('Following');
    });

    it('fetches the state when none was inlined', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(followState({ isFollowing: true })));
        const wrapper = mountButton();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/user/7/follow');
        await flushPromises();

        expect(wrapper.find('button').text()).toBe('Following');
    });

    it('renders nothing when following is not possible and not in place', () => {
        const wrapper = mountButton({ initial: followState({ canFollow: false }) });

        expect(wrapper.find('button').exists()).toBe(false);
    });

    it('still offers to unfollow when following was disabled afterwards', () => {
        const wrapper = mountButton({ initial: followState({ canFollow: false, isFollowing: true }) });

        expect(wrapper.find('button').text()).toBe('Following');
    });

    it('follows through PUT, renders the answered state and dispatches it', async () => {
        const changes = listen(FOLLOW_CHANGED);
        const wrapper = mountButton({
            initial: followState(),
            followingClass: 'btn btn-light',
            checkIconHtml: '<i class="ti ti-check"></i>',
        });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        const [url, cfg] = put.mock.calls[0];
        expect(url).toBe('/api/v2/user/7/follow');
        expect(cfg.method).toBe('PUT');
        const button = wrapper.find('button');
        expect(button.text()).toBe('Following');
        expect(button.find('i.ti-check').exists()).toBe(true);
        expect(button.classes()).toContain('btn-light');
        expect(button.attributes('aria-pressed')).toBe('true');
        const payload = { userId: 7, isFollowing: true, followerCount: 4, canFollow: true };
        expect(changes).toEqual([payload]);
        expect(wrapper.emitted('change')[0][0]).toEqual(payload);
    });

    it('keeps a follower count of a disabled follow feature null', async () => {
        put = vi.fn(() => Promise.resolve(followState({ isFollowing: true, followerCount: null })));
        const wrapper = mountButton({ initial: followState({ followerCount: null }) });
        expect(wrapper.vm.followerCount).toBeNull();

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(wrapper.emitted('change')[0][0].followerCount).toBeNull();
    });

    it('keeps reading "Following" under the pointer that just followed, until it leaves', async () => {
        const wrapper = mountButton({ initial: followState({ isFollowing: false }) });
        const button = wrapper.find('button');

        await button.trigger('mouseenter');
        await button.trigger('focus');
        await button.trigger('click');
        await flushPromises();
        // Browsers re-dispatch mouseenter after the label changed under a resting pointer.
        await button.trigger('mouseenter');
        expect(button.text()).toBe('Following');

        await button.trigger('mouseleave');
        await button.trigger('blur');
        await button.trigger('mouseenter');
        expect(button.text()).toBe('Unfollow');
    });

    it('reads "Unfollow" while hovered or focused', async () => {
        const wrapper = mountButton({ initial: followState({ isFollowing: true }) });
        const button = wrapper.find('button');

        await button.trigger('mouseenter');
        expect(button.text()).toBe('Unfollow');
        await button.trigger('mouseleave');
        expect(button.text()).toBe('Following');

        await button.trigger('focus');
        expect(button.text()).toBe('Unfollow');
        await button.trigger('blur');
        expect(button.text()).toBe('Following');
    });

    it('unfollows through DELETE after confirming, naming the user escaped', async () => {
        const changes = listen(FOLLOW_CHANGED);
        const wrapper = mountButton({ initial: followState({ isFollowing: true, followerCount: 4 }), userName: '<b>Sara</b>' });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
        expect(options.body).toContain('<strong>&lt;b&gt;Sara&lt;/b&gt;</strong>');
        const [url, cfg] = del.mock.calls[0];
        expect(url).toBe('/api/v2/user/7/follow');
        expect(cfg.method).toBe('DELETE');
        expect(wrapper.find('button').text()).toBe('Follow');
        expect(changes).toEqual([{ userId: 7, isFollowing: false, followerCount: 3, canFollow: true }]);
    });

    it('sends nothing when the unfollow confirmation is cancelled', async () => {
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(false));
        const wrapper = mountButton({ initial: followState({ isFollowing: true }) });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(del).not.toHaveBeenCalled();
        expect(wrapper.find('button').text()).toBe('Following');
    });

    it('reads "Following" again once a cancelled confirmation closed, though no mouseleave came', async () => {
        let cancel;
        globalThis.humhubStubs.modal.confirm = vi.fn(() => new Promise((resolve) => { cancel = () => resolve(false); }));
        const wrapper = mountButton({ initial: followState({ isFollowing: true }) });
        const button = wrapper.find('button');

        await button.trigger('mouseenter');
        await button.trigger('focus');
        await button.trigger('click');
        expect(button.text()).toBe('Unfollow');
        cancel();
        await flushPromises();

        expect(button.text()).toBe('Following');
    });

    it('ends the hover when it loses the focus, as a closing dropdown takes it without a mouseleave', async () => {
        const wrapper = mountButton({ initial: followState({ isFollowing: true }) });
        const button = wrapper.find('button');

        await button.trigger('mouseenter');
        await button.trigger('focus');
        await button.trigger('blur');

        expect(button.text()).toBe('Following');
    });

    it('shows a spinner while the request runs and sends only one', async () => {
        let resolvePut;
        put = vi.fn(() => new Promise((resolve) => {
            resolvePut = resolve;
        }));
        const wrapper = mountButton({ initial: followState() });

        await wrapper.find('button').trigger('click');
        await wrapper.find('button').trigger('click');

        expect(put).toHaveBeenCalledTimes(1);
        expect(wrapper.find('.spinner-border').exists()).toBe(true);
        expect(wrapper.find('button').attributes('disabled')).toBeDefined();

        resolvePut(followState({ isFollowing: true }));
        await flushPromises();

        expect(wrapper.find('button').text()).toBe('Following');
    });

    it('logs a failure, keeps the previous state and dispatches nothing', async () => {
        const changes = listen(FOLLOW_CHANGED);
        const failure = { status: 403 };
        put = vi.fn(() => Promise.reject(failure));
        const wrapper = mountButton({ initial: followState() });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error[0]).toEqual([failure, true]);
        expect(wrapper.find('button').text()).toBe('Follow');
        expect(changes).toEqual([]);
    });

    describe('domain events', () => {
        it('takes over the follow state another button of the same user dispatched', async () => {
            const wrapper = mountButton({ initial: followState() });

            trigger(FOLLOW_CHANGED, { userId: 9, isFollowing: true, followerCount: 1, canFollow: true });
            await flushPromises();
            expect(wrapper.find('button').text()).toBe('Follow');

            trigger(FOLLOW_CHANGED, { userId: 7, isFollowing: true, followerCount: 4, canFollow: true });
            await flushPromises();
            expect(wrapper.find('button').text()).toBe('Following');
            expect(wrapper.emitted('change')[0][0]).toMatchObject({ followerCount: 4 });
        });

        it('refetches its state once a friendship made the viewer follow', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(followState({ isFollowing: true, followerCount: 4 })));
            const wrapper = mountButton({ initial: followState() });

            trigger(FRIENDSHIP_CHANGED, { userId: 9, state: 'requestSent', isFollowing: true });
            await flushPromises();
            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();

            trigger(FRIENDSHIP_CHANGED, { userId: 7, state: 'requestSent', isFollowing: true });
            await flushPromises();

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/user/7/follow');
            expect(wrapper.find('button').text()).toBe('Following');
            expect(wrapper.emitted('change')[0][0]).toMatchObject({ isFollowing: true, followerCount: 4 });
        });

        it('ignores a friendship change that leaves the follow as it is', async () => {
            const wrapper = mountButton({ initial: followState({ isFollowing: true }) });

            trigger(FRIENDSHIP_CHANGED, { userId: 7, state: 'none', isFollowing: true });
            await flushPromises();

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
            expect(wrapper.find('button').text()).toBe('Following');
        });

        it('unsubscribes when unmounted', () => {
            const wrapper = mountButton({ initial: followState() });
            wrapper.unmount();

            expect(globalThis.humhubStubs.event._handlers.get(FOLLOW_CHANGED).size).toBe(0);
            expect(globalThis.humhubStubs.event._handlers.get(FRIENDSHIP_CHANGED).size).toBe(0);
        });
    });
});
