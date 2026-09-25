import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import FollowButton from '../../modules/space/vue/FollowButton.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const FOLLOW_CHANGED = 'space:follow-changed';
const MEMBERSHIP_CHANGED = 'space:membership-changed';

// PUT (follow) and DELETE (unfollow) both go through the vue bridge's put()/del() → client.ajax().
let put;
let del;
const dispatchAjax = (url, cfg) => (cfg.method === 'PUT' ? put : del)(url, cfg);

// The `space/<id>/follow` response.
const followState = (overrides = {}) => ({
    isFollowing: false,
    followerCount: 3,
    canFollow: true,
    ...overrides,
});

const mountButton = (props = {}) => mount(FollowButton, {
    props: { spaceId: 5, spaceName: 'Product Team', ...props },
    attachTo: document.body,
});

// Everything dispatched on the bridge's bus for `type`, as the payloads.
const listen = (type) => {
    const payloads = [];
    const handler = (event, payload) => payloads.push(payload);
    globalThis.humhubStubs.event.on(type, handler);

    return { payloads, stop: () => globalThis.humhubStubs.event.off(type, handler) };
};

const trigger = (type, payload) => globalThis.humhubStubs.event.trigger(type, [payload]);

describe('FollowButton', () => {
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
        const wrapper = mountButton({ initial: followState() });

        const button = wrapper.find('button');
        expect(button.text()).toBe('Follow');
        expect(button.classes()).toContain('btn-secondary');
        expect(button.attributes('aria-pressed')).toBe('false');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('fetches the state when none was inlined', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(followState({ isFollowing: true })));
        const wrapper = mountButton();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/space/5/follow');
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

    it('renders nothing for a member, even one with a follow record', () => {
        const wrapper = mountButton({ initial: followState({ canFollow: false, isFollowing: true }), isMember: true });

        expect(wrapper.find('button').exists()).toBe(false);
    });

    it('uses the classes the caller passed', () => {
        const wrapper = mountButton({
            initial: followState(),
            followClass: 'btn btn-primary btn-sm',
            followingClass: 'btn btn-primary btn-sm active',
        });

        expect(wrapper.find('button').classes()).toEqual(['btn', 'btn-primary', 'btn-sm']);
    });

    it('follows through PUT, renders the answered state and dispatches it', async () => {
        const changes = listen(FOLLOW_CHANGED);
        const wrapper = mountButton({
            initial: followState(),
            checkIconHtml: '<i class="ti ti-check"></i>',
        });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        const [url, cfg] = put.mock.calls[0];
        expect(url).toBe('/api/v2/space/5/follow');
        expect(cfg.method).toBe('PUT');
        const button = wrapper.find('button');
        expect(button.text()).toBe('Following');
        expect(button.find('i.ti-check').exists()).toBe(true);
        expect(button.classes()).toContain('active');
        expect(button.attributes('aria-pressed')).toBe('true');
        expect(changes.payloads).toEqual([
            { spaceId: 5, isFollowing: true, followerCount: 4, canFollow: true },
        ]);
        expect(wrapper.emitted('change')[0][0]).toEqual({ spaceId: 5, isFollowing: true, followerCount: 4, canFollow: true });
    });

    it('keeps a hidden follower count null from the inlined state', () => {
        const wrapper = mountButton({ initial: followState({ followerCount: null }) });

        expect(wrapper.vm.followerCount).toBeNull();
    });

    it('keeps a hidden follower count null through a call and its events', async () => {
        const changes = listen(FOLLOW_CHANGED);
        put = vi.fn(() => Promise.resolve(followState({ isFollowing: true, followerCount: null })));
        const wrapper = mountButton({ initial: followState({ followerCount: null }) });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(changes.payloads).toEqual([
            { spaceId: 5, isFollowing: true, followerCount: null, canFollow: true },
        ]);
        expect(wrapper.emitted('change')[0][0].followerCount).toBeNull();
    });

    it('keeps a fetched hidden follower count null', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(followState({ followerCount: null })));
        const wrapper = mountButton();
        await flushPromises();

        expect(wrapper.emitted('change')[0][0].followerCount).toBeNull();
    });

    it('keeps a zero follower count a zero', async () => {
        del = vi.fn(() => Promise.resolve(followState({ followerCount: 0 })));
        const wrapper = mountButton({ initial: followState({ isFollowing: true, followerCount: 1 }) });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(wrapper.emitted('change')[0][0].followerCount).toBe(0);
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

    it('unfollows through DELETE after confirming', async () => {
        const changes = listen(FOLLOW_CHANGED);
        const wrapper = mountButton({ initial: followState({ isFollowing: true, followerCount: 4 }) });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
        expect(options.body).toContain('<strong>Product Team</strong>');
        const [url, cfg] = del.mock.calls[0];
        expect(url).toBe('/api/v2/space/5/follow');
        expect(cfg.method).toBe('DELETE');
        expect(wrapper.find('button').text()).toBe('Follow');
        expect(changes.payloads).toEqual([
            { spaceId: 5, isFollowing: false, followerCount: 3, canFollow: true },
        ]);
    });

    it('sends nothing when the unfollow confirmation is cancelled', async () => {
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(false));
        const wrapper = mountButton({ initial: followState({ isFollowing: true }) });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(del).not.toHaveBeenCalled();
        expect(wrapper.find('button').text()).toBe('Following');
    });

    it('escapes the space name it puts into the dialog', async () => {
        const wrapper = mountButton({
            initial: followState({ isFollowing: true }),
            spaceName: '<img src=x onerror=alert(1)>',
        });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        const options = globalThis.humhubStubs.modal.confirm.mock.calls[0][0];
        expect(options.body).toContain('&lt;img src=x onerror=alert(1)&gt;');
        expect(options.body).not.toContain('<img');
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
        expect(wrapper.find('button').attributes('aria-busy')).toBe('true');

        resolvePut(followState({ isFollowing: true }));
        await flushPromises();

        expect(wrapper.find('.spinner-border').exists()).toBe(false);
        expect(wrapper.find('button').text()).toBe('Following');
    });

    it('logs a failure, keeps the previous state and dispatches nothing', async () => {
        const changes = listen(FOLLOW_CHANGED);
        const failure = { status: 403 };
        put = vi.fn(() => Promise.reject(failure));
        const wrapper = mountButton({ initial: followState() });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
        expect(globalThis.humhubStubs.logCalls.error[0]).toEqual([failure, true]);
        expect(wrapper.find('button').text()).toBe('Follow');
        expect(wrapper.find('button').attributes('disabled')).toBeUndefined();
        expect(changes.payloads).toEqual([]);
    });

    describe('domain events', () => {
        it('takes over the follow state another button of the same space dispatched', async () => {
            const wrapper = mountButton({ initial: followState() });

            trigger(FOLLOW_CHANGED, { spaceId: 9, isFollowing: true, followerCount: 1, canFollow: true });
            await flushPromises();
            expect(wrapper.find('button').text()).toBe('Follow');

            trigger(FOLLOW_CHANGED, { spaceId: 5, isFollowing: true, followerCount: 4, canFollow: true });
            await flushPromises();
            expect(wrapper.find('button').text()).toBe('Following');
            expect(wrapper.emitted('change')[0][0]).toMatchObject({ followerCount: 4 });
        });

        it('disappears once the user became a member of the space', async () => {
            const wrapper = mountButton({ initial: followState() });

            trigger(MEMBERSHIP_CHANGED, { spaceId: 9, state: 'member' });
            await flushPromises();
            expect(wrapper.find('button').exists()).toBe(true);

            trigger(MEMBERSHIP_CHANGED, { spaceId: 5, state: 'member' });
            await flushPromises();
            expect(wrapper.find('button').exists()).toBe(false);
            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        });

        it('offers following again after leaving, from a fresh state', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(followState({ followerCount: 7 })));
            const wrapper = mountButton({ initial: followState({ canFollow: false }), isMember: true });
            expect(wrapper.find('button').exists()).toBe(false);

            trigger(MEMBERSHIP_CHANGED, { spaceId: 5, state: 'none' });
            await flushPromises();

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/space/5/follow');
            expect(wrapper.find('button').text()).toBe('Follow');
            expect(wrapper.emitted('change')[0][0]).toMatchObject({ followerCount: 7 });
        });

        it('ignores a membership change that keeps a non-member a non-member', async () => {
            const wrapper = mountButton({ initial: followState() });

            trigger(MEMBERSHIP_CHANGED, { spaceId: 5, state: 'applicant' });
            await flushPromises();

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
            expect(wrapper.find('button').text()).toBe('Follow');
        });

        it('unsubscribes when unmounted', () => {
            const wrapper = mountButton({ initial: followState() });
            wrapper.unmount();

            expect(globalThis.humhubStubs.event._handlers.get(FOLLOW_CHANGED).size).toBe(0);
            expect(globalThis.humhubStubs.event._handlers.get(MEMBERSHIP_CHANGED).size).toBe(0);
        });
    });
});
