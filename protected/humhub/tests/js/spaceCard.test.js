import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import SpaceCard from '../../modules/space/vue/components/SpaceCard.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import TextareaField from '../../vue/TextareaField.vue';
import ExtensionSlot from '../../vue/ExtensionSlot.vue';
import UiModal from '../../vue/UiModal.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// An item of `GET /api/v2/space` (SpaceSerializer::list()).
const spaceItem = (overrides = {}) => ({
    id: 5,
    guid: 'guid-5',
    name: 'Product Team',
    url: '/s/product-team',
    color: '#6fdbe8',
    imageUrl: null,
    contentContainerId: 12,
    description: 'Where the product is made.',
    tags: ['Product', 'Team'],
    visibility: 'registered',
    archived: false,
    bannerUrl: null,
    memberCount: 7,
    followerCount: 3,
    ...overrides,
});

// A `space/states` entry.
const spaceState = (overrides = {}) => ({
    isMember: false,
    isFollowing: false,
    newItems: 0,
    membership: { state: 'none', canJoin: true, needsApproval: false, canLeave: false, isOwner: false },
    canViewMembers: true,
    canViewFollowers: true,
    canFollow: true,
    memberCount: 7,
    followerCount: 3,
    ...overrides,
});

// `space\widgets\SpaceDirectory::$buttonClasses`
const buttons = {
    buttonClass: 'btn btn-primary',
    pendingClass: 'btn btn-secondary',
    memberClass: 'btn btn-secondary',
    togglerClass: 'btn btn-primary',
    groupClass: 'btn-group',
    followClass: 'btn btn-accent',
    followingClass: 'btn btn-secondary',
    placeholderClass: 'btn btn-secondary',
};

const mountCard = (props = {}) => mount(SpaceCard, {
    props: { space: spaceItem(), buttons, icons: { check: '<i class="ti ti-check"></i>' }, ...props },
    global: { components: { ExtensionSlot, UiModal, HumHubForm, TextareaField } },
    attachTo: document.body,
});

const stat = (wrapper, kind) => wrapper.find(`.c-space-card__stat--${kind}`);

describe('SpaceCard', () => {
    beforeEach(() => {
        document.body.replaceChildren();
        globalThis.humhub.modules.url.config.template = '/index.php?r=__route__';
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ isFollowing: true, followerCount: 4, canFollow: true }));
        globalThis.humhubStubs.modal.global.load = vi.fn(() => Promise.resolve());
        globalThis.humhubStubs.event._handlers.clear();
    });

    it('renders cover, avatar, name, description and tags', () => {
        const wrapper = mountCard({ state: spaceState() });

        const title = wrapper.find('a.c-space-card__title');
        expect(title.text()).toBe('Product Team');
        expect(title.attributes('href')).toBe('/s/product-team');
        expect(wrapper.find('.c-space-card__avatar').attributes('href')).toBe('/s/product-team');
        expect(wrapper.find('.c-space-card__avatar').attributes('aria-hidden')).toBe('true');
        expect(wrapper.find('.space-acronym').text()).toBe('PT');
        // Without a banner the cover is plain grey (from the stylesheet), not the space's colour.
        expect(wrapper.find('.c-space-card__cover').attributes('style')).toBeUndefined();
        expect(wrapper.find('.c-space-card__text').text()).toBe('Where the product is made.');
        expect(wrapper.findAll('.c-space-card__tag').map((tag) => tag.text())).toEqual(['Product', 'Team']);
        expect(wrapper.find('.c-space-card__archived').exists()).toBe(false);
        wrapper.unmount();
    });

    it('uses the banner as cover and notes an archived space', () => {
        const wrapper = mountCard({ space: spaceItem({ bannerUrl: '/banner.jpg', archived: true, description: null }), state: null });

        const cover = wrapper.find('.c-space-card__cover');
        expect(cover.attributes('style')).toContain('background-image: url("/banner.jpg")');
        expect(wrapper.find('.c-space-card__archived').text()).toBe('Archived');
        expect(wrapper.find('.c-space-card__text').exists()).toBe(false);
        wrapper.unmount();
    });

    it('shows a disabled placeholder while the state loads, nothing without a state', () => {
        const loading = mountCard({ state: undefined });
        const placeholder = loading.find('.c-space-card__footer .c-space-card__placeholder');
        expect(placeholder.exists()).toBe(true);
        expect(placeholder.attributes('disabled')).toBeDefined();
        expect(placeholder.attributes('aria-hidden')).toBe('true');
        expect(loading.find('.c-space-card__footer').text()).toBe('');
        loading.unmount();

        const missing = mountCard({ state: null });
        expect(missing.find('.c-space-card__footer').exists()).toBe(false);
        missing.unmount();
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('feeds the membership and follow button from the state without a request', () => {
        const wrapper = mountCard({ state: spaceState() });

        const footer = wrapper.find('.c-space-card__footer');
        const join = footer.find('a.c-space-card__action');
        expect(join.text()).toBe('Join');
        expect(join.classes()).toEqual(expect.arrayContaining(['btn', 'btn-primary']));
        const follow = footer.find('button.c-space-card__action');
        expect(follow.text()).toBe('Follow');
        expect(follow.classes()).toEqual(expect.arrayContaining(['btn', 'btn-accent']));
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        wrapper.unmount();
    });

    it('shows the member state and hides the follow button for a member', () => {
        const wrapper = mountCard({
            state: spaceState({ isMember: true, canFollow: false, membership: { state: 'member', canLeave: true } }),
        });

        const actions = wrapper.findAll('.c-space-card__footer .c-space-card__action');
        expect(actions).toHaveLength(1);
        expect(actions[0].text()).toBe('Member');
        expect(actions[0].classes()).toContain('btn-secondary');
        wrapper.unmount();
    });

    describe('stat pill', () => {
        it('shows followers and members, both opening their lists', async () => {
            const wrapper = mountCard({ state: spaceState() });

            const followers = stat(wrapper, 'followers');
            const members = stat(wrapper, 'members');
            expect(followers.element.tagName).toBe('BUTTON');
            expect(followers.text()).toBe('3');
            expect(followers.find('.visually-hidden').exists()).toBe(false);
            expect(followers.attributes('aria-label')).toBe('3 Followers');
            expect(members.element.tagName).toBe('BUTTON');
            expect(members.text()).toBe('7');
            expect(members.attributes('aria-label')).toBe('7 Members');

            await followers.trigger('click');
            await members.trigger('click');
            expect(globalThis.humhubStubs.modal.global.load.mock.calls.map(([url]) => url)).toEqual([
                '/index.php?r=space%2Fspace%2Ffollower-list&cguid=guid-5',
                '/index.php?r=space%2Fmembership%2Fmembers-list&cguid=guid-5',
            ]);
            wrapper.unmount();
        });

        it('shows the counts, but opens nothing, while the state loads', () => {
            const wrapper = mountCard({ state: undefined });

            expect(stat(wrapper, 'followers').element.tagName).toBe('SPAN');
            expect(stat(wrapper, 'followers').find('.visually-hidden').text()).toBe('3 Followers');
            expect(stat(wrapper, 'members').element.tagName).toBe('SPAN');
            wrapper.unmount();
        });

        it('offers the member list only with canViewMembers', () => {
            const wrapper = mountCard({ state: spaceState({ canViewMembers: false }) });

            expect(stat(wrapper, 'members').element.tagName).toBe('SPAN');
            expect(stat(wrapper, 'followers').element.tagName).toBe('BUTTON');
            wrapper.unmount();
        });

        it('leaves out a count the space does not show', () => {
            const noFollowers = mountCard({
                space: spaceItem({ followerCount: null }),
                state: spaceState({ followerCount: null, canViewFollowers: false }),
            });
            expect(stat(noFollowers, 'followers').exists()).toBe(false);
            expect(stat(noFollowers, 'members').exists()).toBe(true);
            noFollowers.unmount();

            const neither = mountCard({
                space: spaceItem({ followerCount: null, memberCount: null }),
                state: spaceState({ followerCount: null, memberCount: null, canViewFollowers: false }),
            });
            expect(neither.find('.c-space-card__pill').exists()).toBe(false);
            neither.unmount();
        });

        it('takes the counts of a loaded state over the item', () => {
            const wrapper = mountCard({ state: spaceState({ followerCount: 9, memberCount: 8 }) });

            expect(stat(wrapper, 'followers').text()).toBe('9');
            expect(stat(wrapper, 'members').text()).toBe('8');
            wrapper.unmount();
        });
    });

    it('emits filter-tag for a tag', async () => {
        const wrapper = mountCard({ state: null });

        const tag = wrapper.findAll('.c-space-card__tag')[1];
        expect(tag.attributes('aria-label')).toBe('Filter by Team');
        await tag.trigger('click');
        expect(wrapper.emitted('filter-tag')).toEqual([['Team']]);
        wrapper.unmount();
    });

    it('emits the follow button change', async () => {
        const wrapper = mountCard({ state: spaceState() });

        await wrapper.find('.c-space-card__footer button.c-space-card__action').trigger('click');
        await flushPromises();

        expect(wrapper.emitted('follow-change')).toEqual([[{ spaceId: 5, isFollowing: true, followerCount: 4, canFollow: true }]]);
        wrapper.unmount();
    });

    it('renders the components registered for the space.card-subtitle slot with the space', async () => {
        vueModule.register('TestSpaceCardSubtitle', {
            props: { space: { type: Object, required: true } },
            render() {
                return Vue.h('p', { class: 'c-space-card__subtitle' }, `Category of ${this.space.name}`);
            },
        });
        vueModule.registerSlotComponent('space.card-subtitle', 'TestSpaceCardSubtitle');

        // Registered components resolve through the island app's registry, as in production
        // (where the core set, `ExtensionSlot` included, is registered by CoreVueAsset).
        vueModule.register('ExtensionSlot', ExtensionSlot);
        vueModule.register('TestSpaceCardSubtitleHost', {
            render() {
                return Vue.h(SpaceCard, { space: spaceItem(), state: null });
            },
        });
        const el = document.createElement('test-space-card-subtitle-host');
        document.body.appendChild(el);
        await vueModule.mountElement(el);
        await flushPromises();

        expect(el.querySelector('.c-space-card__header .c-space-card__subtitle').textContent).toBe('Category of Product Team');
    });
});
