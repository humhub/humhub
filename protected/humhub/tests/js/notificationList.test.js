import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import NotificationEntry from '../../modules/notification/vue/components/NotificationEntry.vue';
import NotificationList from '../../modules/notification/vue/components/NotificationList.vue';
import UserImage from '../../modules/user/vue/UserImage.vue';
import SpaceImage from '../../modules/space/vue/SpaceImage.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mountOptions = () => ({ global: { components: { UserImage, SpaceImage } } });

const notification = (overrides = {}) => ({
    id: 5,
    html: '<strong>Jane</strong> commented on <em>Post</em>',
    url: '/notification/entry?id=5&cId=9',
    isNew: true,
    createdAt: '2026-08-20T10:00:00+00:00',
    groupKey: null,
    originator: {
        id: 2,
        guid: 'user-guid-2',
        displayName: 'Jane Doe',
        url: '/u/jane/',
        imageUrl: '/uploads/jane.jpg',
        contentContainerId: 22,
    },
    space: null,
    ...overrides,
});

const windowPayload = (results, overrides = {}) => ({
    results,
    unseenCount: results.filter((entry) => entry.isNew).length,
    nextCursor: null,
    ...overrides,
});

let wrapper;
let getCalls;

beforeEach(() => {
    getCalls = [];
    globalThis.humhubStubs.client.get = (url) => {
        getCalls.push(url);
        return Promise.resolve(windowPayload([notification()]));
    };
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
});

describe('NotificationEntry', () => {
    it('renders the legacy entry markup around the server-rendered sentence', () => {
        wrapper = mount(NotificationEntry, { ...mountOptions(), props: { notification: notification() } });

        const anchor = wrapper.find('a');
        expect(anchor.classes()).toContain('d-flex');
        expect(anchor.classes()).toContain('new');
        expect(anchor.attributes('href')).toBe('/notification/entry?id=5&cId=9');
        expect(anchor.attributes('data-notification-id')).toBe('5');
        expect(anchor.attributes('data-notification-group')).toBe('');
        expect(wrapper.find('strong').text()).toBe('Jane');
        expect(wrapper.find('.badge.badge-new').exists()).toBe(true);
        expect(wrapper.find('time[data-ui-addition="timeago"]').attributes('datetime'))
            .toBe('2026-08-20T10:00:00+00:00');
        expect(wrapper.find('img').attributes('src')).toBe('/uploads/jane.jpg');
    });

    it('carries the group key so a live event can be deduped against it', () => {
        wrapper = mount(NotificationEntry, {
            ...mountOptions(),
            props: { notification: notification({ groupKey: 'Some\\Notification:42' }) },
        });

        expect(wrapper.find('a').attributes('data-notification-group')).toBe('Some\\Notification:42');
    });

    it('renders a seen notification without the unread markers', () => {
        wrapper = mount(NotificationEntry, {
            ...mountOptions(),
            props: { notification: notification({ isNew: false }) },
        });

        expect(wrapper.find('a').classes()).not.toContain('new');
        expect(wrapper.find('.badge.badge-new').exists()).toBe(false);
    });

    it('renders the space badge only for a notification bound to a space', () => {
        wrapper = mount(NotificationEntry, { ...mountOptions(), props: { notification: notification() } });
        expect(wrapper.find('.space-acronym').exists()).toBe(false);

        wrapper.unmount();
        wrapper = mount(NotificationEntry, {
            ...mountOptions(),
            props: {
                notification: notification({
                    space: { id: 3, guid: 'space-3', name: 'Dev Team', url: '/s/dev/', color: '#abcdef', imageUrl: null, contentContainerId: 33 },
                }),
            },
        });

        expect(wrapper.find('.space-acronym').text()).toBe('DT');
    });
});

describe('NotificationList', () => {
    it('renders the entries it was handed without fetching anything', () => {
        wrapper = mount(NotificationList, {
            ...mountOptions(),
            props: { initial: windowPayload([notification(), notification({ id: 6 })]) },
        });

        expect(wrapper.findAll('.hh-list > a')).toHaveLength(2);
        expect(getCalls).toHaveLength(0);
    });

    it('shows the empty state when there is nothing to list', () => {
        wrapper = mount(NotificationList, { ...mountOptions(), props: { initial: windowPayload([]) } });

        expect(wrapper.find('.hh-list .info').text()).toBe('There are no notifications yet.');
    });

    it('fetches the first page on reload, with the page size and filters', async () => {
        wrapper = mount(NotificationList, {
            ...mountOptions(),
            props: { pageSize: 20, categories: ['followed'], seen: 'unseen' },
        });

        await wrapper.vm.reload();
        await flushPromises();

        expect(getCalls).toHaveLength(1);
        expect(getCalls[0]).toContain('/api/v2/notification');
        expect(getCalls[0]).toContain('limit=20');
        expect(getCalls[0]).toContain('seen=unseen');
        expect(decodeURIComponent(getCalls[0])).toContain('categories[]=followed');
        expect(wrapper.findAll('.hh-list > a')).toHaveLength(1);
    });

    it('appends the next page and stops when the cursor is exhausted', async () => {
        globalThis.humhubStubs.client.get = (url) => {
            getCalls.push(url);
            return Promise.resolve(url.includes('cursor=')
                ? windowPayload([notification({ id: 3 })])
                : windowPayload([notification({ id: 5 })], { nextCursor: 5 }));
        };

        wrapper = mount(NotificationList, { ...mountOptions(), props: { pageSize: 1, showMoreButton: true } });
        await wrapper.vm.reload();
        await flushPromises();

        expect(wrapper.find('button').exists()).toBe(true);

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(getCalls[1]).toContain('cursor=5');
        expect(wrapper.findAll('.hh-list > a').map((entry) => entry.attributes('data-notification-id')))
            .toEqual(['5', '3']);
        // The second page had no cursor of its own, so there is nothing more to load.
        expect(wrapper.find('button').exists()).toBe(false);
    });

    it('emits the response so an island can track the unseen count', async () => {
        wrapper = mount(NotificationList, { ...mountOptions(), props: {} });

        await wrapper.vm.reload();
        await flushPromises();

        expect(wrapper.emitted('loaded')[0][0]).toMatchObject({ unseenCount: 1, nextCursor: null });
    });

    it('prepends a live notification and replaces an already listed one instead of duplicating it', () => {
        const listed = notification({ id: 5, groupKey: 'Group:1' });
        wrapper = mount(NotificationList, {
            ...mountOptions(),
            props: { initial: windowPayload([listed, notification({ id: 4 })]) },
        });

        wrapper.vm.prepend(notification({ id: 9 }));
        expect(wrapper.vm.items.map((entry) => entry.id)).toEqual([9, 5, 4]);

        // Same id: moved to the top, not duplicated.
        wrapper.vm.prepend(notification({ id: 4 }));
        expect(wrapper.vm.items.map((entry) => entry.id)).toEqual([4, 9, 5]);

        // Same group: the grown group replaces its previous entry.
        wrapper.vm.prepend(notification({ id: 11, groupKey: 'Group:1' }));
        expect(wrapper.vm.items.map((entry) => entry.id)).toEqual([11, 4, 9]);
    });

    it('answers whether an id or group is already listed', () => {
        wrapper = mount(NotificationList, {
            ...mountOptions(),
            props: { initial: windowPayload([notification({ id: 5, groupKey: 'Group:1' })]) },
        });

        expect(wrapper.vm.has({ id: 5 })).toBe(true);
        expect(wrapper.vm.has({ id: 99, groupKey: 'Group:1' })).toBe(true);
        expect(wrapper.vm.has({ id: 99, groupKey: null })).toBe(false);
    });

    it('pages on scroll when the list is scrolled near its bottom', async () => {
        globalThis.humhubStubs.client.get = (url) => {
            getCalls.push(url);
            return Promise.resolve(windowPayload([notification({ id: 5 })], { nextCursor: 5 }));
        };

        wrapper = mount(NotificationList, { ...mountOptions(), props: { pageSize: 1 } });
        await wrapper.vm.reload();
        await flushPromises();
        getCalls.length = 0;

        const list = wrapper.find('.hh-list').element;
        // jsdom has no layout, so the scroll geometry is stubbed.
        Object.defineProperty(list, 'scrollHeight', { value: 500, configurable: true });
        Object.defineProperty(list, 'clientHeight', { value: 100, configurable: true });
        Object.defineProperty(list, 'scrollTop', { value: 100, writable: true, configurable: true });

        await wrapper.find('.hh-list').trigger('scroll');
        expect(getCalls).toHaveLength(0);

        list.scrollTop = 395;
        await wrapper.find('.hh-list').trigger('scroll');
        await flushPromises();

        expect(getCalls).toHaveLength(1);
        expect(getCalls[0]).toContain('cursor=5');
    });

    it('surfaces a failed request through the platform error log instead of breaking the list', async () => {
        globalThis.humhubStubs.client.get = () => Promise.reject({ status: 500 });
        const errors = globalThis.humhubStubs.logCalls.error;
        errors.length = 0;

        wrapper = mount(NotificationList, { ...mountOptions(), props: { initial: windowPayload([notification()]) } });
        await wrapper.vm.reload();
        await flushPromises();

        expect(errors).toHaveLength(1);
        // The previously listed entries stay - a failed refresh is not an empty list.
        expect(wrapper.findAll('.hh-list > a')).toHaveLength(1);
    });
});
