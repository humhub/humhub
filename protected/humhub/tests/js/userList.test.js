import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import UserList from '../../modules/user/vue/UserList.vue';
import UserImage from '../../modules/user/vue/UserImage.vue';

await import('../../resources/js/humhub/humhub.vue.js');

// UserList references <UserImage> by tag only (resolved through the global Vue
// component registry in production - see UserList.vue's own docblock and
// commentSection.test.js for the same pattern); @vue/test-utils' `global.components`
// stands in for that registry here.
const mountOptions = () => ({ global: { components: { UserImage } } });

const page = (users, overrides = {}) => ({
    total: users.length,
    users,
    hasMore: false,
    nextPage: null,
    ...overrides,
});

const user = (overrides = {}) => ({
    guid: 'user-guid-1',
    displayName: 'Alice',
    url: '/user/alice',
    imageUrl: '/uploads/alice.jpg',
    imageAlt: 'Profile picture of Alice',
    contentContainerId: 5,
    online: null,
    ...overrides,
});

describe('UserList', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(page([user()])));
        globalThis.humhubStubs.logCalls.error.length = 0;
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('loads page 1 on mount and renders a row per user', async () => {
        const wrapper = mount(UserList, { ...mountOptions(), props: { url: '/like/like/user-list?recordId=7' } });

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/like/like/user-list?recordId=7&page=1');
        await flushPromises();

        const rows = wrapper.findAll('.hh-list > a');
        expect(rows).toHaveLength(1);
        expect(rows[0].attributes('href')).toBe('/user/alice');
        expect(rows[0].find('h4').text()).toBe('Alice');
        expect(rows[0].find('img').attributes('src')).toBe('/uploads/alice.jpg');
    });

    it('appends the query string with a plain ? when the url has none', async () => {
        mount(UserList, { ...mountOptions(), props: { url: '/like/like/user-list' } });

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/like/like/user-list?page=1');
    });

    it('sends the pageSize prop as limit', async () => {
        mount(UserList, { ...mountOptions(), props: { url: '/like/like/user-list', pageSize: 5 } });

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/like/like/user-list?page=1&limit=5');
    });

    it('shows the empty-state message when the response has no users', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(page([])));

        const wrapper = mount(UserList, { ...mountOptions(), props: { url: '/x' } });
        await flushPromises();

        expect(wrapper.text()).toContain('No users found.');
        expect(wrapper.find('.hh-list').exists()).toBe(false);
    });

    it('shows an error message and logs when the request fails', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('network error')));

        const wrapper = mount(UserList, { ...mountOptions(), props: { url: '/x' } });
        await flushPromises();

        expect(wrapper.text()).toContain('Could not load the user list.');
        expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0);
    });

    it('renders a "Show more" link only while hasMore is true, and loads the next page on click', async () => {
        globalThis.humhubStubs.client.get = vi.fn()
            .mockResolvedValueOnce(page([user({ guid: 'g1', displayName: 'Alice' })], { hasMore: true, nextPage: 2 }))
            .mockResolvedValueOnce(page([user({ guid: 'g2', displayName: 'Bob' })], { hasMore: false, nextPage: null }));

        const wrapper = mount(UserList, { ...mountOptions(), props: { url: '/x' } });
        await flushPromises();

        const more = wrapper.find('.pagination-container a');
        expect(more.exists()).toBe(true);

        await more.trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenLastCalledWith('/x?page=2');

        const rows = wrapper.findAll('.hh-list > a');
        expect(rows).toHaveLength(2);
        expect(rows[0].find('h4').text()).toBe('Alice');
        expect(rows[1].find('h4').text()).toBe('Bob');
        expect(wrapper.find('.pagination-container').exists()).toBe(false);
    });

    it('ignores a second load-more click while the first is still in flight', async () => {
        let resolveSecond;
        globalThis.humhubStubs.client.get = vi.fn()
            .mockResolvedValueOnce(page([user()], { hasMore: true, nextPage: 2 }))
            .mockImplementationOnce(() => new Promise((resolve) => { resolveSecond = resolve; }));

        const wrapper = mount(UserList, { ...mountOptions(), props: { url: '/x' } });
        await flushPromises();

        const more = wrapper.find('.pagination-container a');
        await more.trigger('click');
        await more.trigger('click');

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(2);

        resolveSecond(page([user({ guid: 'g2' })], { hasMore: false }));
        await flushPromises();
    });
});
