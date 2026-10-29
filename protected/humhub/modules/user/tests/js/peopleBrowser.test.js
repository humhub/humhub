import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import PeopleBrowser from '../../vue/PeopleBrowser.vue';
import FriendshipButton from '../../../../modules/friendship/vue/FriendshipButton.vue';
import EntityBrowser from '../../../../vue/EntityBrowser.vue';
import ExtensionSlot from '../../../../vue/ExtensionSlot.vue';
import PeopleCardFollowAction from '../../vue/components/PeopleCardFollowAction.vue';
import PeopleCardFriendshipAction from '../../../../modules/friendship/vue/components/PeopleCardFriendshipAction.vue';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');
// The entries of the `user.card-actions` slot, registered as in production.
await import('../../vue/index.js');
await import('../../../../modules/friendship/vue/index.js');

const filters = [
    { key: 'q', type: 'text', label: 'Search', placeholder: 'Search people...' },
    { key: 'scope', type: 'select', label: 'Status', options: [{ value: 'following', label: 'Following' }] },
    { key: 'fields[city]', type: 'select', label: 'City', options: [{ value: 'Berlin', label: 'Berlin' }] },
    { key: 'tag', type: 'picker', multiple: true, label: 'Tags', optionsUrl: '/api/v2/user/tags' },
];

const actions = [{ id: 'invite-people-button', icon: 'send', label: 'Invite new people', url: '/user/invite', modal: true, primary: true }];

const userItem = (id, overrides = {}) => ({
    id,
    guid: `guid-${id}`,
    displayName: `User ${id}`,
    url: `/u/user-${id}`,
    imageUrl: `/img/${id}.jpg`,
    contentContainerId: id + 10,
    bannerUrl: null,
    title: null,
    details: [],
    tags: ['Alpha'],
    followerCount: 1,
    friendCount: 2,
    ...overrides,
});

const userState = (overrides = {}) => ({
    isSelf: false,
    isFollowing: false,
    canFollow: true,
    friendship: { state: 'none', isFollowing: false },
    ...overrides,
});

const envelope = (results) => ({ results, total: results.length, page: 1, pageSize: 24, pages: 1 });
const params = (url) => new URL(url, 'http://localhost').searchParams;
const listCalls = () => globalThis.humhubStubs.client.get.mock.calls.filter(([url]) => url.startsWith('/api/v2/user?'));
const stateCalls = () => globalThis.humhubStubs.client.get.mock.calls.filter(([url]) => url.startsWith('/api/v2/user/states'));

// PUT/DELETE go through client.ajax(); answered per endpoint.
let ajax;

describe('PeopleBrowser', () => {
    let users;
    let states;
    let wrapper;

    beforeEach(() => {
        document.body.replaceChildren();
        window.history.replaceState(null, '', '/people');
        globalThis.humhub.modules.url.config.template = '/index.php?r=__route__';
        globalThis.humhubStubs.event._handlers.clear();
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        users = [userItem(1), userItem(2)];
        states = { 1: userState(), 2: userState({ isSelf: true, canFollow: false, friendship: null }) };
        globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(
            url.startsWith('/api/v2/user/states') ? { results: states }
                : url.startsWith('/api/v2/user/1/follow') ? { isFollowing: true, followerCount: 6, canFollow: true }
                    : envelope(users),
        ));
        ajax = vi.fn((url) => Promise.resolve(url.endsWith('/friendship')
            ? { state: 'friends', isFollowing: true }
            : { isFollowing: true, followerCount: 5, canFollow: true }));
        globalThis.humhubStubs.client.ajax = ajax;
    });

    afterEach(() => {
        wrapper?.unmount();
        wrapper = null;
    });

    const mountIt = async (props = {}) => {
        wrapper = mount(PeopleBrowser, {
            props: { filters, actions, followEnabled: true, friendshipEnabled: true, buttons: { followClass: 'btn btn-accent', friendClass: 'btn btn-primary', friendGroupClass: 'btn-group' }, ...props },
            global: { components: { EntityBrowser, ExtensionSlot, FriendshipButton, PeopleCardFollowAction, PeopleCardFriendshipAction } },
            attachTo: document.body,
        });
        await flushPromises();
        return wrapper;
    };

    it('renders the page header with title, actions and filters', async () => {
        await mountIt();

        expect(wrapper.find('.page-header-title').text()).toBe('People');
        const invite = wrapper.find('.page-header-actions a[data-action-id="invite-people-button"]');
        expect(invite.attributes('aria-label')).toBe('Invite new people');
        expect(invite.classes()).toContain('btn-accent');
        expect(wrapper.find('.filter-bar-body').exists()).toBe(true);
    });

    it('lists the directory users and loads their states once per page', async () => {
        await mountIt();

        expect(listCalls()).toHaveLength(1);
        expect(params(listCalls()[0][0]).get('purpose')).toBe('directory');
        expect(stateCalls()).toHaveLength(1);
        expect(params(stateCalls()[0][0]).getAll('ids[]')).toEqual(['1', '2']);

        expect(wrapper.findAll('.entity-card-title').map((n) => n.text())).toEqual(['User 1', 'User 2']);
        const first = wrapper.find('[data-id="1"] .entity-card-footer');
        // Friendship on: the friendship button, not the follow button.
        expect(first.text()).toBe('Friends');
        // One's own card has no buttons.
        expect(wrapper.find('[data-id="2"] .entity-card-footer').exists()).toBe(false);
    });

    it('sends a profile field filter as a PHP-style array parameter, from and to the page URL', async () => {
        window.history.replaceState(null, '', '/people?fields%5Bcity%5D=Berlin');
        await mountIt();

        expect(params(listCalls()[0][0]).get('fields[city]')).toBe('Berlin');
        expect(new URL(listCalls()[0][0], 'http://localhost').search).toContain('fields%5Bcity%5D=Berlin');
        const select = wrapper.find('#people-filter-fields-city');
        expect(select.exists()).toBe(true);
        expect(select.text()).toBe('Berlin');
        expect(wrapper.find('.form-search-filter-fields-city').exists()).toBe(true);
    });

    it('shows the people skeletons while the first page loads', async () => {
        let resolve;
        globalThis.humhubStubs.client.get = vi.fn(() => new Promise((done) => { resolve = done; }));
        wrapper = mount(PeopleBrowser, { props: { filters }, global: { components: { EntityBrowser } }, attachTo: document.body });
        await flushPromises();

        expect(wrapper.findAll('.people-card-skeleton').length).toBeGreaterThan(0);
        resolve(envelope([]));
    });

    it('adds a tag clicked on a card to the tag filter', async () => {
        await mountIt();

        await wrapper.find('[data-id="1"] .entity-card-tag').trigger('click');
        await flushPromises();

        expect(params(listCalls().at(-1)[0]).get('tag')).toBe('Alpha');
        expect(params(listCalls().at(-1)[0]).get('q')).toBeNull();
    });

    it('searches for a tag clicked on a card without a tag filter', async () => {
        await mountIt({ filters: filters.filter((filter) => filter.key !== 'tag') });

        await wrapper.find('[data-id="1"] .entity-card-tag').trigger('click');
        await flushPromises();

        expect(params(listCalls().at(-1)[0]).get('q')).toBe('Alpha');
    });

    it('updates the follower count after following', async () => {
        await mountIt({ friendshipEnabled: false });
        states[1] = userState({ friendship: null });

        const follow = wrapper.find('[data-id="1"] .entity-card-footer button.entity-card-action');
        expect(follow.text()).toBe('Follow');
        expect(wrapper.find('[data-id="1"] .entity-card-stat-followers').text()).toBe('1');

        await follow.trigger('click');
        await flushPromises();

        expect(wrapper.find('[data-id="1"] .entity-card-stat-followers').text()).toBe('5');
        expect(wrapper.find('[data-id="1"] .entity-card-footer button.entity-card-action').text()).toBe('Following');
    });

    it('follows a friendship change: friend count and state', async () => {
        states[1] = userState({ friendship: { state: 'requestReceived', isFollowing: false } });
        await mountIt();
        expect(wrapper.find('[data-id="1"] .entity-card-stat-friends').text()).toBe('2');

        // Accept the request.
        await wrapper.find('[data-id="1"] .entity-card-footer .btn-group > a').trigger('click');
        await flushPromises();

        expect(wrapper.find('[data-id="1"] .entity-card-stat-friends').text()).toBe('3');
        expect(wrapper.vm.$refs.directory.states[1].friendship).toEqual({ state: 'friends', isFollowing: true });
        expect(wrapper.find('[data-id="1"] .entity-card-footer').text()).toBe('Friends');
    });
});
