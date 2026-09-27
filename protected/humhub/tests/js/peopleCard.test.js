import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import PeopleCard from '../../modules/user/vue/components/PeopleCard.vue';
import FriendshipButton from '../../modules/friendship/vue/FriendshipButton.vue';
import ExtensionSlot from '../../vue/ExtensionSlot.vue';
import PeopleCardFollowAction from '../../modules/user/vue/components/PeopleCardFollowAction.vue';
import PeopleCardFriendshipAction from '../../modules/friendship/vue/components/PeopleCardFriendshipAction.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');
// The entries of the `user.card-actions` slot, registered as in production.
await import('../../modules/user/vue/index.js');
await import('../../modules/friendship/vue/index.js');

const vueModule = globalThis.humhub.modules.vue;

// An item of `GET /api/v2/user` (UserSerializer::list()).
const userItem = (overrides = {}) => ({
    id: 7,
    guid: 'guid-7',
    displayName: 'Sara Schuster',
    url: '/u/sara',
    imageUrl: '/img/sara.jpg',
    contentContainerId: 12,
    bannerUrl: null,
    title: 'Sales Manager',
    details: ['GB Branch', '+44 020 555 5015'],
    tags: ['Leadership', 'Agile'],
    followerCount: 3,
    friendCount: 2,
    ...overrides,
});

// A `user/states` entry.
const userState = (overrides = {}) => ({
    isSelf: false,
    isFollowing: false,
    canFollow: true,
    friendship: { state: 'none', isFollowing: false },
    ...overrides,
});

// `user\widgets\PeopleDirectory::$buttonClasses`
const buttons = {
    friendClass: 'btn btn-primary',
    friendStateClass: 'btn btn-light',
    friendTogglerClass: 'btn btn-light',
    friendGroupClass: 'btn-group',
    followClass: 'btn btn-accent',
    followingClass: 'btn btn-light',
    placeholderClass: 'btn btn-light',
};

const mountCard = (props = {}) => mount(PeopleCard, {
    props: { user: userItem(), buttons, followEnabled: true, friendshipEnabled: true, icons: { check: '<i class="ti ti-check"></i>' }, ...props },
    global: { components: { ExtensionSlot, FriendshipButton, PeopleCardFollowAction, PeopleCardFriendshipAction } },
    attachTo: document.body,
});

describe('PeopleCard', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhub.modules.url.config.template = '/index.php?r=__route__';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ isFollowing: true, followerCount: 4, canFollow: true }));
        globalThis.humhubStubs.modal.global.load = vi.fn(() => Promise.resolve());
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        globalThis.humhubStubs.event._handlers.clear();
    });

    it('renders cover, avatar, name, title, details and tags', () => {
        const wrapper = mountCard({ state: userState() });

        const title = wrapper.find('a.c-entity-card__title');
        expect(title.text()).toBe('Sara Schuster');
        expect(title.attributes('href')).toBe('/u/sara');
        const avatar = wrapper.find('a.c-entity-card__avatar');
        expect(avatar.attributes('href')).toBe('/u/sara');
        expect(avatar.attributes('aria-hidden')).toBe('true');
        expect(avatar.find('img').attributes('style')).toContain('width: 80px');
        // Without a banner the cover is plain grey (from the stylesheet).
        expect(wrapper.find('.c-entity-card__cover').attributes('style')).toBeUndefined();
        expect(wrapper.find('.c-entity-card__subtitle').text()).toBe('Sales Manager');
        expect(wrapper.findAll('.c-people-card__detail').map((line) => line.text())).toEqual(['GB Branch', '+44 020 555 5015']);
        expect(wrapper.findAll('.c-entity-card__tag').map((tag) => tag.text())).toEqual(['Leadership', 'Agile']);
        wrapper.unmount();
    });

    it('uses the banner as cover and leaves out an empty title', () => {
        const wrapper = mountCard({ user: userItem({ bannerUrl: '/banner.jpg', title: null, details: [], tags: [] }), state: null });

        expect(wrapper.find('.c-entity-card__cover').attributes('style')).toContain('background-image: url("/banner.jpg")');
        expect(wrapper.find('.c-entity-card__subtitle').exists()).toBe(false);
        expect(wrapper.find('.c-entity-card__body').exists()).toBe(true);
        expect(wrapper.find('.c-entity-card__tags').exists()).toBe(false);
        wrapper.unmount();
    });

    describe('stat pill', () => {
        it('shows the friends while friendship is on, opening the friend list', async () => {
            const wrapper = mountCard({ state: userState() });

            const stat = wrapper.find('.c-entity-card__stat--friends');
            expect(stat.text()).toBe('2');
            expect(stat.attributes('aria-label')).toBe('2 Friends');
            expect(wrapper.find('.c-entity-card__stat--followers').exists()).toBe(false);

            await stat.trigger('click');
            expect(globalThis.humhubStubs.modal.global.load).toHaveBeenCalledWith('/index.php?r=friendship%2Flist%2Fpopup&userId=7');
            wrapper.unmount();
        });

        it('shows the followers otherwise, opening the follower list', async () => {
            const wrapper = mountCard({ user: userItem({ friendCount: null }), friendshipEnabled: false, state: userState({ friendship: null }) });

            const stat = wrapper.find('.c-entity-card__stat--followers');
            expect(stat.text()).toBe('3');
            expect(stat.attributes('aria-label')).toBe('3 Followers');

            await stat.trigger('click');
            expect(globalThis.humhubStubs.modal.global.load).toHaveBeenCalledWith('/index.php?r=user%2Fprofile%2Ffollower-list&cguid=guid-7');
            wrapper.unmount();
        });

        it('has no pill without either count', () => {
            const wrapper = mountCard({ user: userItem({ friendCount: null, followerCount: null }), state: null });

            expect(wrapper.find('.c-entity-card__pill').exists()).toBe(false);
            wrapper.unmount();
        });
    });

    it('shows a disabled placeholder while the state loads, nothing without a state', () => {
        const loading = mountCard({ state: undefined });
        const placeholder = loading.find('.c-entity-card__footer .c-entity-card__placeholder');
        expect(placeholder.attributes('disabled')).toBeDefined();
        expect(loading.find('.c-entity-card__footer').text()).toBe('');
        loading.unmount();

        const missing = mountCard({ state: null });
        expect(missing.find('.c-entity-card__footer').exists()).toBe(false);
        missing.unmount();
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('feeds the friendship and follow button from the state without a request', () => {
        const wrapper = mountCard({ state: userState() });

        const actions = wrapper.findAll('.c-entity-card__footer .c-entity-card__action');
        expect(actions.map((action) => action.text())).toEqual(['Friends', 'Follow']);
        expect(actions[0].classes()).toEqual(expect.arrayContaining(['btn', 'btn-primary']));
        expect(actions[1].classes()).toEqual(expect.arrayContaining(['btn', 'btn-accent']));
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('renders only the buttons of the features that are on', () => {
        const followOnly = mountCard({ friendshipEnabled: false, state: userState({ friendship: null }) });
        expect(followOnly.findAll('.c-entity-card__footer .c-entity-card__action').map((action) => action.text())).toEqual(['Follow']);
        followOnly.unmount();

        const friendOnly = mountCard({ followEnabled: false, state: userState({ canFollow: false }) });
        expect(friendOnly.findAll('.c-entity-card__footer .c-entity-card__action').map((action) => action.text())).toEqual(['Friends']);
        friendOnly.unmount();
    });

    it('keeps an existing follow endable after following was disabled', async () => {
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ isFollowing: false, followerCount: 2, canFollow: false }));
        const wrapper = mountCard({ followEnabled: false, friendshipEnabled: false, state: userState({ isFollowing: true, canFollow: false, friendship: null }) });

        const button = wrapper.find('.c-entity-card__footer .c-entity-card__action');
        expect(button.text()).toBe('Following');

        await button.trigger('click');
        await flushPromises();
        expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledTimes(1);
        expect(globalThis.humhubStubs.client.ajax.mock.calls[0][1]).toMatchObject({ type: 'DELETE' });
        expect(wrapper.emitted('follow-change')).toEqual([[{ userId: 7, isFollowing: false, followerCount: 2, canFollow: false }]]);
        // Ended, it cannot be started again.
        expect(wrapper.find('.c-entity-card__footer .c-entity-card__action').exists()).toBe(false);
    });

    it('has no buttons on one\'s own card', () => {
        const wrapper = mountCard({ state: userState({ isSelf: true, canFollow: false, friendship: null }) });

        expect(wrapper.find('.c-entity-card__footer').exists()).toBe(false);
        wrapper.unmount();
    });

    it('emits filter-tag for a tag', async () => {
        const wrapper = mountCard({ state: null });

        const tag = wrapper.findAll('.c-entity-card__tag')[1];
        expect(tag.attributes('aria-label')).toBe('Filter by Agile');
        await tag.trigger('click');
        expect(wrapper.emitted('filter-tag')).toEqual([['Agile']]);
        wrapper.unmount();
    });

    it('emits the follow and the friendship button changes', async () => {
        const wrapper = mountCard({ state: userState() });

        await wrapper.find('.c-entity-card__footer button.c-entity-card__action').trigger('click');
        await flushPromises();
        expect(wrapper.emitted('follow-change')).toEqual([[{ userId: 7, isFollowing: true, followerCount: 4, canFollow: true }]]);

        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ state: 'requestSent', isFollowing: true }));
        await wrapper.find('.c-entity-card__footer a.c-entity-card__action').trigger('click');
        await flushPromises();
        expect(wrapper.emitted('friendship-change')).toEqual([[{ userId: 7, state: 'requestSent', isFollowing: true }]]);
        wrapper.unmount();
    });

    it('renders the components registered for the user.card-subtitle and user.card-actions slots', async () => {
        vueModule.register('TestPeopleCardSubtitle', {
            props: { user: { type: Object, required: true } },
            render() {
                return Vue.h('p', { class: 'c-entity-card__subtitle test-subtitle' }, `Branch of ${this.user.displayName}`);
            },
        });
        vueModule.register('TestPeopleCardAction', {
            props: { user: { type: Object, required: true }, state: { type: Object, default: null } },
            render() {
                return Vue.h('button', { class: 'c-entity-card__action test-action' }, `Message ${this.user.id} ${this.state.isFollowing}`);
            },
        });
        vueModule.registerSlotComponent('user.card-subtitle', 'TestPeopleCardSubtitle');
        vueModule.registerSlotComponent('user.card-actions', 'TestPeopleCardAction');

        vueModule.register('ExtensionSlot', ExtensionSlot);
        vueModule.register('FriendshipButton', FriendshipButton);
        vueModule.register('TestPeopleCardHost', {
            render() {
                return Vue.h(PeopleCard, { user: userItem(), state: userState(), followEnabled: true });
            },
        });
        const el = document.createElement('test-people-card-host');
        document.body.appendChild(el);
        await vueModule.mountElement(el);
        await flushPromises();

        expect(el.querySelector('.c-entity-card__header .test-subtitle').textContent).toBe('Branch of Sara Schuster');
        expect(el.querySelector('.c-entity-card__footer .test-action').textContent).toBe('Message 7 false');
    });

    describe('online status', () => {
        const indicator = (wrapper) => wrapper.find('.c-entity-card__avatar .user-online-status');

        it('shows the state\'s online status on the avatar, as the Image widget does', () => {
            const online = mountCard({ state: userState({ isOnline: true }) });
            expect(online.find('a.c-entity-card__avatar').classes()).toEqual(expect.arrayContaining(['has-online-status', 'img-size-large']));
            expect(indicator(online).classes()).toEqual(expect.arrayContaining(['tt', 'user-is-online']));
            expect(indicator(online).attributes('aria-label')).toBe('Online');
            online.unmount();

            const offline = mountCard({ state: userState({ isOnline: false }) });
            expect(indicator(offline).classes()).toContain('user-is-offline');
            expect(indicator(offline).attributes('aria-label')).toBe('Offline');
            offline.unmount();
        });

        it('shows none while the state loads, without a state or while the status is not shown', () => {
            for (const state of [undefined, null, userState({ isOnline: null }), userState()]) {
                const wrapper = mountCard({ state });
                expect(indicator(wrapper).exists()).toBe(false);
                expect(wrapper.find('a.c-entity-card__avatar').classes()).not.toContain('has-online-status');
                wrapper.unmount();
            }
        });
    });

    describe('card actions (user.card-actions)', () => {
        const TestMailAction = {
            name: 'TestMailAction',
            inheritAttrs: false,
            props: { user: { type: Object, required: true } },
            render() {
                return Vue.h('button', { type: 'button', class: 'c-entity-card__action test-mail' }, `Message ${this.user.id}`);
            },
        };
        const TestReplacementFollow = {
            name: 'TestReplacementFollow',
            inheritAttrs: false,
            props: { user: { type: Object, required: true }, state: { type: Object, required: true } },
            emits: ['follow-change'],
            render() {
                return Vue.h('button', {
                    type: 'button',
                    class: 'c-entity-card__action test-follow',
                    onClick: () => this.$emit('follow-change', { userId: this.user.id, isFollowing: !this.state.isFollowing }),
                }, 'Custom follow');
            },
        };
        vueModule.register('TestMailAction', TestMailAction);
        vueModule.register('TestReplacementFollow', TestReplacementFollow);

        const mountWithActions = (props = {}) => mount(PeopleCard, {
            props: { user: userItem(), buttons, followEnabled: true, friendshipEnabled: true, state: userState(), ...props },
            global: { components: { ExtensionSlot, FriendshipButton, PeopleCardFollowAction, PeopleCardFriendshipAction, TestMailAction, TestReplacementFollow } },
            attachTo: document.body,
        });
        const actionTexts = (wrapper) => wrapper.findAll('.c-entity-card__footer .c-entity-card__action').map((action) => action.text());

        // Removals are permanent, so every test starts from the production registrations.
        const restoreRegistrations = async () => {
            vueModule.resetSlotRegistry();
            vi.resetModules();
            await import('../../modules/user/vue/index.js');
            await import('../../modules/friendship/vue/index.js');
        };
        beforeEach(restoreRegistrations);
        afterEach(restoreRegistrations);

        it('are the core\'s own entries friendship and follow, in that order', () => {
            expect(vueModule.getSlotComponents('user.card-actions')).toEqual([
                { id: 'friendship', component: 'PeopleCardFriendshipAction', sortOrder: 100 },
                { id: 'follow', component: 'PeopleCardFollowAction', sortOrder: 200 },
            ]);

            const wrapper = mountWithActions();
            expect(actionTexts(wrapper)).toEqual(['Friends', 'Follow']);
            wrapper.unmount();
        });

        it('renders a module\'s action where its sortOrder puts it', async () => {
            const wrapper = mountWithActions();

            vueModule.registerSlotComponent('user.card-actions', 'TestMailAction', { id: 'mail', sortOrder: 300 });
            await flushPromises();
            expect(actionTexts(wrapper)).toEqual(['Friends', 'Follow', 'Message 7']);
            // Unused context keys are no attributes of the action.
            expect(wrapper.find('.test-mail').attributes('buttons')).toBeUndefined();

            vueModule.registerSlotComponent('user.card-actions', 'TestMailAction', { id: 'mail', sortOrder: 150 });
            await flushPromises();
            expect(actionTexts(wrapper)).toEqual(['Friends', 'Message 7', 'Follow']);
            wrapper.unmount();
        });

        it('drops a removed core action from a mounted card', async () => {
            const wrapper = mountWithActions();

            vueModule.removeSlotComponent('user.card-actions', 'follow');
            await flushPromises();
            expect(actionTexts(wrapper)).toEqual(['Friends']);

            vueModule.removeSlotComponent('user.card-actions', 'friendship');
            await flushPromises();
            expect(wrapper.find('.c-entity-card__footer .c-entity-card__action').exists()).toBe(false);
            wrapper.unmount();
        });

        it('renders the replacement registered under a core id in its place, with the card\'s callbacks', async () => {
            vueModule.registerSlotComponent('user.card-actions', 'TestReplacementFollow', { id: 'follow', sortOrder: 50 });
            const wrapper = mountWithActions();

            expect(actionTexts(wrapper)).toEqual(['Custom follow', 'Friends']);
            await wrapper.find('.test-follow').trigger('click');
            expect(wrapper.emitted('follow-change')).toEqual([[{ userId: 7, isFollowing: true }]]);
            wrapper.unmount();
        });

        it('are left out on one\'s own card, whatever is registered', () => {
            vueModule.registerSlotComponent('user.card-actions', 'TestMailAction', { id: 'mail', sortOrder: 300 });
            const wrapper = mountWithActions({ state: userState({ isSelf: true, canFollow: false, friendship: null }) });

            expect(wrapper.find('.c-entity-card__footer').exists()).toBe(false);
            wrapper.unmount();
        });
    });
});
