import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import NotificationMenu from '../../modules/notification/vue/NotificationMenu.vue';
import NotificationOverview from '../../modules/notification/vue/NotificationOverview.vue';
import { markAsSeen } from '../../modules/notification/vue/components/notificationApi.js';
import UserImage from '../../modules/user/vue/UserImage.vue';
import SpaceImage from '../../modules/space/vue/SpaceImage.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mountOptions = () => ({ global: { components: { UserImage, SpaceImage } } });

// The opaque group key the server sends (`NotificationListService::encodeCursor()`) - the
// value the live event carries as `notificationGroup`.
const groupKeyOf = (groupingKey) => btoa(`n1:${groupingKey}`).replace(/=+$/, '');

const notification = (overrides = {}) => ({
    id: 5,
    html: '<strong>Jane</strong> commented',
    url: '/notification/entry?id=5',
    isNew: true,
    createdAt: '2026-08-20T10:00:00+00:00',
    groupKey: groupKeyOf(overrides.id ?? 5),
    count: 1,
    priority: 'normal',
    originator: null,
    space: null,
    ...overrides,
});

const windowPayload = (results = [notification()], overrides = {}) => ({
    results,
    unseenCount: 2,
    nextCursor: null,
    ...overrides,
});

const menuProps = (overrides = {}) => ({
    initial: windowPayload(),
    overviewUrl: '/notification/overview',
    settingsUrl: '/notification/user',
    bellIconHtml: '<i class="ti ti-bell"></i>',
    checkIconHtml: '<i class="ti ti-check"></i>',
    cogIconHtml: '<i class="ti ti-settings"></i>',
    ...overrides,
});

let wrapper;
let getCalls;
let postCalls;
let postConfigs;

beforeEach(() => {
    getCalls = [];
    postCalls = [];
    postConfigs = [];
    globalThis.humhubStubs.event._handlers.clear();
    globalThis.humhubStubs.client.get = (url) => {
        getCalls.push(url);
        // Everything read after a mark-as-seen is seen, like the server would answer.
        return Promise.resolve(windowPayload(undefined, postCalls.length ? { unseenCount: 0 } : {}));
    };
    globalThis.humhubStubs.client.post = (url, cfg) => {
        postCalls.push(url);
        postConfigs.push(cfg);
        return Promise.resolve({ unseenCount: 0 });
    };
    document.title = 'Dashboard - HumHub';
    globalThis.humhubStubs.view.title = 'Dashboard - HumHub';
    delete globalThis.humhub.modules.mail;
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
});

describe('NotificationMenu', () => {
    it('renders the legacy menu markup with the badge from the inlined first page', () => {
        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });

        expect(wrapper.find('#icon-notifications').exists()).toBe(true);
        expect(wrapper.find('#icon-notifications i.ti.ti-bell').exists()).toBe(true);
        expect(wrapper.find('#badge-notifications').text()).toBe('2');
        expect(wrapper.find('#dropdown-notifications.dropdown-menu').exists()).toBe(true);
        expect(wrapper.find('.dropdown-footer a').attributes('href')).toBe('/notification/overview');
        expect(wrapper.find('#mark-seen-link').exists()).toBe(true);
        // The inlined page means no request for the first paint.
        expect(getCalls).toHaveLength(0);
        expect(wrapper.findAll('.hh-list > a')).toHaveLength(1);
    });

    it('hides the badge and the mark-as-seen action without unread notifications', () => {
        wrapper = mount(NotificationMenu, {
            ...mountOptions(),
            props: menuProps({ initial: windowPayload([], { unseenCount: 0 }) }),
        });

        expect(wrapper.find('#badge-notifications').exists()).toBe(false);
        expect(wrapper.find('#mark-seen-link').exists()).toBe(false);
    });

    it('reloads the list when the dropdown opens', async () => {
        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });

        wrapper.find('#icon-notifications').element.dispatchEvent(new Event('show.bs.dropdown'));
        await flushPromises();

        expect(getCalls).toHaveLength(1);
        expect(getCalls[0]).toContain('/api/v2/notification');
    });

    it('refreshes after a live notification that is not listed yet, and ignores one that is', async () => {
        vi.useFakeTimers();
        try {
            globalThis.humhubStubs.client.get = (url) => {
                getCalls.push(url);
                return Promise.resolve(windowPayload(undefined, { unseenCount: 3 }));
            };
            wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });

            // listed by id, and by group key
            globalThis.humhubStubs.event.trigger('humhub:modules:notification:live:NewNotification', [
                [
                    { data: { notificationId: 5, notificationGroup: null } },
                    { data: { notificationId: 80, notificationGroup: groupKeyOf(5) } },
                ],
            ]);
            await vi.advanceTimersByTimeAsync(1000);
            expect(getCalls).toHaveLength(0);
            expect(wrapper.find('#badge-notifications').text()).toBe('2');

            globalThis.humhubStubs.event.trigger('humhub:modules:notification:live:NewNotification', [
                [{ data: { notificationId: 77, notificationGroup: groupKeyOf(77) } }],
            ]);
            await vi.advanceTimersByTimeAsync(1000);
            await flushPromises();
            expect(getCalls).toHaveLength(1);
            expect(wrapper.find('#badge-notifications').text()).toBe('3');
        } finally {
            vi.useRealTimers();
        }
    });

    it('debounces a burst of live notifications into one request and takes the server count', async () => {
        vi.useFakeTimers();
        try {
            globalThis.humhubStubs.client.get = (url) => {
                getCalls.push(url);
                return Promise.resolve(windowPayload(undefined, { unseenCount: 4 }));
            };
            wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });

            for (const id of [91, 92, 93]) {
                globalThis.humhubStubs.event.trigger('humhub:modules:notification:live:NewNotification', [
                    [{ data: { notificationId: id, notificationGroup: groupKeyOf(id) } }],
                ]);
                await vi.advanceTimersByTimeAsync(100);
            }
            expect(getCalls).toHaveLength(0);

            await vi.advanceTimersByTimeAsync(500);
            await flushPromises();

            expect(getCalls).toHaveLength(1);
            // the server's count, not 2 + 3 events
            expect(wrapper.find('#badge-notifications').text()).toBe('4');
        } finally {
            vi.useRealTimers();
        }
    });

    it('refreshes the list instead of only counting while the dropdown is open', async () => {
        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });
        wrapper.find('#icon-notifications').element.dispatchEvent(new Event('show.bs.dropdown'));
        await flushPromises();
        getCalls.length = 0;

        globalThis.humhubStubs.event.trigger('humhub:modules:notification:live:NewNotification', [
            [{ data: { notificationId: 78, notificationGroup: null } }],
        ]);
        await flushPromises();

        expect(getCalls).toHaveLength(1);
    });

    it('triggers the legacy count event on every change', async () => {
        const handler = vi.fn();
        globalThis.humhubStubs.event.on('humhub:notification:updateCount', handler);

        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });
        globalThis.humhubStubs.event.trigger('humhub:notification:setCount', [3]);
        await flushPromises();

        expect(handler).toHaveBeenCalledTimes(1);
        expect(handler.mock.calls[0][1]).toBe(3);
    });

    it('prefixes the document title with the unread count, mail module included', async () => {
        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });

        expect(document.title).toBe('(2) Dashboard - HumHub');

        globalThis.humhub.modules.mail = { notification: { getNewMessageCount: () => 3 } };
        globalThis.humhubStubs.event.trigger('humhub:modules:notification:UpdateTitleNotificationCount');
        await flushPromises();

        expect(document.title).toBe('(5) Dashboard - HumHub');
    });

    it('restores the plain page title once nothing is unread', async () => {
        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });

        await wrapper.find('#mark-seen-link').trigger('click');
        await flushPromises();

        expect(postCalls[0]).toContain('/api/v2/notification/mark-as-seen');
        expect(document.title).toBe('Dashboard - HumHub');
        expect(wrapper.find('#badge-notifications').exists()).toBe(false);
    });

    it('accepts a pushed count (the pjax layout addon and the overview island both use it)', async () => {
        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });

        globalThis.humhubStubs.event.trigger('humhub:notification:setCount', [7]);
        await flushPromises();

        expect(wrapper.find('#badge-notifications').text()).toBe('7');
        expect(document.title).toBe('(7) Dashboard - HumHub');
    });

    it('unsubscribes from the live and title events on unmount', async () => {
        wrapper = mount(NotificationMenu, { ...mountOptions(), props: menuProps() });
        wrapper.unmount();
        wrapper = undefined;

        // Nothing throws and no title update happens after unmount.
        document.title = 'Untouched';
        globalThis.humhubStubs.event.trigger('humhub:modules:notification:UpdateTitleNotificationCount');
        globalThis.humhubStubs.event.trigger('humhub:modules:notification:live:NewNotification', [
            [{ data: { notificationId: 123, notificationGroup: null } }],
        ]);
        await flushPromises();

        expect(document.title).toBe('Untouched');
    });
});

const overviewProps = (overrides = {}) => ({
    initial: windowPayload(),
    filters: [
        { id: 'social', title: 'Reactions on my content' },
        { id: 'content', title: 'New content in my spaces' },
    ],
    icons: { check: '<i class="ti ti-check"></i>', cog: '<i class="ti ti-settings"></i>' },
    settingsUrl: '/notification/user',
    ...overrides,
});

describe('NotificationOverview', () => {
    it('renders the page panels with the inlined first page and the filter', () => {
        wrapper = mount(NotificationOverview, { ...mountOptions(), props: overviewProps() });

        expect(wrapper.find('#notification_overview_list').exists()).toBe(true);
        expect(wrapper.find('#notification_overview_markseen').exists()).toBe(true);
        expect(wrapper.findAll('.form-check')).toHaveLength(3); // "all" + two groups
        expect(getCalls).toHaveLength(0);
    });

    it('sends no group filter while every group is selected', async () => {
        wrapper = mount(NotificationOverview, { ...mountOptions(), props: overviewProps() });

        await wrapper.findAll('.btn-group button')[1].trigger('click'); // "unseen"
        await flushPromises();

        expect(getCalls).toHaveLength(1);
        expect(getCalls[0]).toContain('seen=unseen');
        expect(getCalls[0]).not.toContain('groups');
    });

    it('sends the selected groups once the selection is narrowed', async () => {
        wrapper = mount(NotificationOverview, { ...mountOptions(), props: overviewProps() });

        // Uncheck the second group.
        const checkboxes = wrapper.findAll('.form-check input');
        await checkboxes[2].setValue(false);
        await flushPromises();

        expect(decodeURIComponent(getCalls[0])).toContain('groups[]=social');
        expect(decodeURIComponent(getCalls[0])).not.toContain('groups[]=content');
    });

    it('clears and restores the whole selection through the all checkbox', async () => {
        wrapper = mount(NotificationOverview, { ...mountOptions(), props: overviewProps() });

        const all = wrapper.findAll('.form-check input')[0];
        expect(all.element.checked).toBe(true);

        await all.setValue(false);
        await flushPromises();
        // Nothing selected is a filter of its own (an empty list), not "no filter".
        expect(getCalls[0]).toContain('groups');
        expect(wrapper.findAll('.form-check input')[1].element.checked).toBe(false);

        await wrapper.findAll('.form-check input')[0].setValue(true);
        await flushPromises();
        expect(wrapper.findAll('.form-check input')[1].element.checked).toBe(true);
    });

    it('marks everything as seen, reloads the list and tells the notification menu', async () => {
        const counts = [];
        globalThis.humhubStubs.event.on('humhub:notification:setCount', (event, count) => counts.push(count));

        wrapper = mount(NotificationOverview, { ...mountOptions(), props: overviewProps() });

        await wrapper.find('#notification_overview_markseen').trigger('click');
        await flushPromises();

        expect(postCalls[0]).toContain('/api/v2/notification/mark-as-seen');
        expect(getCalls).toHaveLength(1);
        expect(wrapper.find('#notification_overview_markseen').exists()).toBe(false);
        // The badge and the document title belong to the menu island on the same page.
        expect(counts).toEqual([0]);
    });

    it('refreshes its list when the menu marked everything as seen', async () => {
        wrapper = mount(NotificationOverview, { ...mountOptions(), props: overviewProps() });

        globalThis.humhubStubs.event.trigger('humhub:notification:setCount', [0]);
        await flushPromises();

        // The list is refetched; what its unread markers look like afterwards is the server's
        // answer, which this test's stub keeps unchanged on purpose.
        expect(getCalls).toHaveLength(1);
    });
});

describe('notificationApi.markAsSeen', () => {
    it('marks everything without ids and the given entries with ids', async () => {
        expect(await markAsSeen()).toEqual({ unseenCount: 0 });
        expect(postConfigs[0]).toBeUndefined();

        await markAsSeen([5, 7]);
        expect(postCalls[1]).toContain('/api/v2/notification/mark-as-seen');
        expect(postConfigs[1]).toEqual({ data: { ids: [5, 7] } });
    });
});

describe('notificationApi.markAsSeen with an empty list', () => {
    it('sends nothing - an empty list is not "everything"', async () => {
        expect(await markAsSeen([])).toBeNull();
        expect(postCalls).toHaveLength(0);
    });
});
