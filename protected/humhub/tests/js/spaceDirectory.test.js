import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import SpaceDirectory from '../../modules/space/vue/SpaceDirectory.vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import TextareaField from '../../vue/TextareaField.vue';
import CardDirectory from '../../vue/CardDirectory.vue';
import ExtensionSlot from '../../vue/ExtensionSlot.vue';
import UiModal from '../../vue/UiModal.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const MEMBERSHIP_CHANGED = 'space:membership-changed';

const filters = [
    { key: 'q', type: 'text', label: 'Search', placeholder: 'Search Spaces...' },
    { key: 'scope', type: 'select', label: 'Status', options: [{ value: 'member', label: 'Member' }] },
];

const actions = [{ id: 'create-space-button', icon: 'plus', label: 'Create Space', url: '/space/create', modal: true }];

const spaceItem = (id, overrides = {}) => ({
    id,
    guid: `guid-${id}`,
    name: `Space ${id}`,
    url: `/s/space-${id}`,
    color: '#123456',
    imageUrl: null,
    contentContainerId: id + 10,
    description: null,
    tags: ['Alpha'],
    visibility: 'registered',
    archived: false,
    bannerUrl: null,
    memberCount: 2,
    followerCount: 1,
    ...overrides,
});

const spaceState = (overrides = {}) => ({
    isMember: false,
    isFollowing: false,
    newItems: 0,
    membership: { state: 'none', canJoin: true, needsApproval: false, canLeave: false, isOwner: false },
    canViewMembers: true,
    canViewFollowers: true,
    canFollow: true,
    memberCount: 2,
    followerCount: 1,
    ...overrides,
});

const envelope = (results) => ({ results, total: results.length, page: 1, pageSize: 24, pages: 1 });
const params = (url) => new URL(url, 'http://localhost').searchParams;
const listCalls = () => globalThis.humhubStubs.client.get.mock.calls.filter(([url]) => url.startsWith('/api/v2/space?'));
const stateCalls = () => globalThis.humhubStubs.client.get.mock.calls.filter(([url]) => url.startsWith('/api/v2/space/states'));

const deferred = () => {
    let resolve;
    const promise = new Promise((done) => { resolve = done; });
    return { promise, resolve };
};

describe('SpaceDirectory', () => {
    let spaces;
    let states;
    let wrapper;

    beforeEach(() => {
        document.body.replaceChildren();
        window.history.replaceState(null, '', '/spaces');
        globalThis.humhub.modules.url.config.template = '/index.php?r=__route__';
        globalThis.humhubStubs.event._handlers.clear();
        globalThis.humhubStubs.modal.global.load = vi.fn(() => Promise.resolve());
        spaces = [spaceItem(1), spaceItem(2)];
        states = { 1: spaceState(), 2: spaceState({ isMember: true, canFollow: false, membership: { state: 'member', canLeave: true } }) };
        globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(
            url.startsWith('/api/v2/space/states') ? { results: states } : envelope(spaces),
        ));
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ isFollowing: true, followerCount: 5, canFollow: true }));
    });

    afterEach(() => {
        vi.useRealTimers();
        wrapper?.unmount();
        wrapper = null;
    });

    const mountIt = async () => {
        wrapper = mount(SpaceDirectory, {
            props: { filters, actions, buttons: { followClass: 'btn btn-secondary btn-sm' } },
            global: { components: { CardDirectory, ExtensionSlot, UiModal, HumHubForm, TextareaField } },
            attachTo: document.body,
        });
        await flushPromises();
        return wrapper;
    };

    it('renders the toolbar with title, actions and filters', async () => {
        await mountIt();

        expect(wrapper.find('.c-page-toolbar__title').text()).toBe('Spaces');
        const create = wrapper.find('.c-page-toolbar__actions a[data-action-id="create-space-button"]');
        expect(create.attributes('aria-label')).toBe('Create Space');
        expect(create.find('.ti-plus').exists()).toBe(true);
        expect(wrapper.find('.c-filter-bar').exists()).toBe(true);
    });

    it('lists the directory spaces and loads their states once per page', async () => {
        await mountIt();

        expect(listCalls()).toHaveLength(1);
        const list = params(listCalls()[0][0]);
        expect(list.get('purpose')).toBe('directory');
        expect(list.get('page')).toBe('1');

        expect(stateCalls()).toHaveLength(1);
        expect(params(stateCalls()[0][0]).getAll('ids[]')).toEqual(['1', '2']);

        expect(wrapper.findAll('.c-space-card__title').map((n) => n.text())).toEqual(['Space 1', 'Space 2']);
        const first = wrapper.find('[data-id="1"] .c-space-card__footer');
        expect(first.text()).toContain('Join');
        expect(first.text()).toContain('Follow');
        expect(wrapper.find('[data-id="2"] .c-space-card__footer').text()).toBe('Member');
    });

    it('shows the space skeletons while the first page loads', async () => {
        const pending = deferred();
        globalThis.humhubStubs.client.get = vi.fn(() => pending.promise);
        wrapper = mount(SpaceDirectory, { props: { filters }, global: { components: { CardDirectory } }, attachTo: document.body });
        await flushPromises();

        expect(wrapper.findAll('.c-space-card-skeleton').length).toBeGreaterThan(0);
        expect(wrapper.find('.c-card-skeleton__version').exists()).toBe(false);
        pending.resolve(envelope([]));
    });

    it('shows placeholders until the states arrive', async () => {
        const pending = deferred();
        globalThis.humhubStubs.client.get = vi.fn((url) => (
            url.startsWith('/api/v2/space/states') ? pending.promise : Promise.resolve(envelope(spaces))
        ));
        await mountIt();

        expect(wrapper.findAll('.c-space-card__placeholder')).toHaveLength(2);
        pending.resolve({ results: states });
        await flushPromises();
        expect(wrapper.findAll('.c-space-card__placeholder')).toHaveLength(0);
    });

    it('searches for a tag clicked on a card', async () => {
        await mountIt();

        // Set from outside the bar, the search applies at once (no text debounce).
        await wrapper.find('[data-id="1"] .c-space-card__tag').trigger('click');
        await flushPromises();

        expect(params(listCalls().at(-1)[0]).get('q')).toBe('Alpha');
        expect(wrapper.find('.c-filter-bar input[type="search"], .c-filter-bar input[type="text"]').element.value).toBe('Alpha');
    });

    it('updates the follower count after following', async () => {
        await mountIt();

        const follow = wrapper.find('[data-id="1"] .c-space-card__footer button.c-space-card__action');
        expect(follow.text()).toBe('Follow');
        expect(wrapper.find('[data-id="1"] .c-space-card__stat--followers').text()).toBe('1');

        await follow.trigger('click');
        await flushPromises();

        expect(wrapper.find('[data-id="1"] .c-space-card__stat--followers').text()).toBe('5');
        expect(wrapper.find('[data-id="1"] .c-space-card__footer button.c-space-card__action').text()).toBe('Following');
    });

    it('refetches the state of a space whose membership changed', async () => {
        await mountIt();
        states = { 1: spaceState({ isMember: true, canFollow: false, memberCount: 3, membership: { state: 'member', canLeave: true } }) };

        globalThis.humhubStubs.event.trigger(MEMBERSHIP_CHANGED, [{ spaceId: 1, state: 'member' }]);
        await flushPromises();

        expect(params(stateCalls().at(-1)[0]).getAll('ids[]')).toEqual(['1']);
        expect(wrapper.find('[data-id="1"] .c-space-card__stat--members').text()).toBe('3');
        expect(wrapper.find('[data-id="1"] .c-space-card__footer button.c-space-card__action').exists()).toBe(false);
    });

    it('ignores membership changes of spaces it does not show', async () => {
        await mountIt();
        const before = stateCalls().length;

        globalThis.humhubStubs.event.trigger(MEMBERSHIP_CHANGED, [{ spaceId: 99, state: 'member' }]);
        await flushPromises();

        expect(stateCalls()).toHaveLength(before);
    });
});
