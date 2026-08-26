import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import SpaceChooser from '../../modules/space/vue/SpaceChooser.vue';
import SpaceChooserToggle from '../../modules/space/vue/SpaceChooserToggle.vue';
import SpaceImage from '../../modules/space/vue/SpaceImage.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const additionsDirective = {
    mounted(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
    updated(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
};

const mountOptions = () => ({
    global: {
        directives: { additions: additionsDirective },
        components: { SpaceImage },
    },
});

const space = (overrides = {}) => ({
    id: 3,
    guid: 's3',
    name: 'Product Team',
    url: '/s/product-team/',
    color: null,
    imageUrl: null,
    contentContainerId: 21,
    description: 'Where the product happens',
    tags: [],
    visibility: 2,
    archived: false,
    extensions: {},
    ...overrides,
});

const page = (results = [space()], overrides = {}) => ({
    results,
    total: results.length,
    page: 1,
    pageSize: 25,
    pages: 1,
    ...overrides,
});

let wrapper;
let getCalls;
let listResponse;
let statesResponse;

/**
 * The menu is a Bootstrap dropdown: the island listens on the `.dropdown` around it, which the
 * PHP widget renders. Mounting into one reproduces that structure.
 */
const mountInDropdown = (props = {}) => {
    const host = document.createElement('li');
    host.className = 'nav-item dropdown';
    document.body.appendChild(host);

    return mount(SpaceChooser, { ...mountOptions(), props, attachTo: host });
};

const openMenu = async () => {
    document.querySelector('.dropdown').dispatchEvent(new Event('show.bs.dropdown'));
    await flushPromises();
    await flushPromises();
};

beforeEach(() => {
    getCalls = [];
    listResponse = page();
    statesResponse = { s3: { isMember: true, isFollowing: false, newItems: 2 } };

    globalThis.humhubStubs.event._handlers.clear();
    globalThis.humhubStubs.client.get = (url) => {
        getCalls.push(url);

        return Promise.resolve(url.indexOf('/states') !== -1
            ? { results: statesResponse }
            : listResponse);
    };
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
    document.querySelectorAll('.dropdown').forEach((node) => node.remove());
});

describe('SpaceChooser', () => {
    it('renders the menu without asking for anything until it is opened', () => {
        wrapper = mountInDropdown({ createSpaceUrl: '/space/create', directoryUrl: '/spaces' });

        expect(wrapper.find('#space-menu-search').exists()).toBe(true);
        expect(wrapper.find('#space-menu-spaces.hh-list').exists()).toBe(true);
        expect(wrapper.find('#space-directory-link a').attributes('href')).toBe('/spaces');
        expect(wrapper.find('.dropdown-footer a').attributes('data-action-url')).toBe('/space/create');
        // Lazy: a page whose menu is never opened costs no space queries.
        expect(getCalls).toHaveLength(0);
    });

    it('loads the caller\'s own spaces and what they are to them when opened', async () => {
        wrapper = mountInDropdown();
        await openMenu();

        expect(getCalls[0]).toContain('/api/v2/space');
        expect(getCalls[0]).toContain('scope=mine');
        // The states are asked for exactly the spaces displayed.
        expect(getCalls[1]).toContain('/api/v2/space/states');
        expect(getCalls[1]).toContain('s3');

        const item = wrapper.find('[data-space-chooser-item]');
        expect(item.attributes('data-space-guid')).toBe('s3');
        expect(item.attributes('data-space-member')).toBeDefined();
        expect(item.find('.space-name').text()).toBe('Product Team');
        expect(item.find('[data-message-count]').text()).toBe('2');
    });

    it('marks a space the caller only follows', async () => {
        statesResponse = { s3: { isMember: false, isFollowing: true, newItems: 0 } };
        wrapper = mountInDropdown();
        await openMenu();

        const item = wrapper.find('[data-space-chooser-item]');
        expect(item.attributes('data-space-following')).toBeDefined();
        expect(item.find('.fa-star').exists()).toBe(true);
        // No count without a membership - "new since your last visit" has no meaning then.
        expect(item.find('[data-message-count]').exists()).toBe(false);
    });

    it('searches every visible space once the keyword is long enough', async () => {
        vi.useFakeTimers();
        wrapper = mountInDropdown();
        await openMenu();
        getCalls.length = 0;

        await wrapper.find('#space-menu-search').setValue('p');
        await vi.advanceTimersByTimeAsync(400);
        await flushPromises();

        // One character is not searched for; the hint says so.
        expect(getCalls.every((url) => url.indexOf('q=') === -1)).toBe(true);
        expect(wrapper.text()).toContain('Please enter at least 2 characters');

        await wrapper.find('#space-menu-search').setValue('pro');
        await vi.advanceTimersByTimeAsync(400);
        await flushPromises();

        expect(getCalls.some((url) => url.indexOf('q=pro') !== -1)).toBe(true);
        // A search reaches every space the caller may see, so it carries no scope.
        expect(getCalls.filter((url) => url.indexOf('q=pro') !== -1)[0]).not.toContain('scope=');
        vi.useRealTimers();
    });

    it('says when the caller has no spaces and when a search finds none', async () => {
        listResponse = page([], { total: 0, pages: 0 });
        statesResponse = {};
        wrapper = mountInDropdown();
        await openMenu();

        expect(wrapper.text()).toContain('You are not a member of or following any Spaces.');

        vi.useFakeTimers();
        await wrapper.find('#space-menu-search').setValue('nothing');
        await vi.advanceTimersByTimeAsync(400);
        await flushPromises();

        expect(wrapper.text()).toContain('No Spaces found.');
        vi.useRealTimers();
    });

    it('counts content arriving live in a space the caller is a member of', async () => {
        wrapper = mountInDropdown();
        await openMenu();

        globalThis.humhubStubs.event.trigger('humhub:modules:content:live:NewContent', [
            [{ data: { sguid: 's3', originator: 'someone-else' } }],
        ]);
        await flushPromises();

        expect(wrapper.find('[data-message-count]').text()).toBe('3');
    });

    it('ignores live content of the caller\'s own, silent content and profile content', async () => {
        wrapper = mountInDropdown();
        await openMenu();

        globalThis.humhubStubs.event.trigger('humhub:modules:content:live:NewContent', [
            [
                { data: { sguid: 's3', silent: true } },
                { data: { uguid: 'u1' } },
            ],
        ]);
        await flushPromises();

        expect(wrapper.find('[data-message-count]').text()).toBe('2');
    });

    it('re-reads the list after the caller follows or unfollows a space', async () => {
        wrapper = mountInDropdown();
        await openMenu();
        getCalls.length = 0;

        globalThis.humhubStubs.event.trigger('humhub:space:followed', [{ guid: 'other' }]);
        await flushPromises();

        // The menu is closed while this happens, so nothing is fetched yet...
        expect(getCalls).toHaveLength(0);

        await openMenu();
        // ...but the next open reads a fresh list rather than showing a stale one.
        expect(getCalls.some((url) => url.indexOf('scope=mine') !== -1)).toBe(true);
    });
});

describe('SpaceChooserToggle', () => {
    it('shows the current space and falls back to the placeholder outside one', async () => {
        wrapper = mount(SpaceChooserToggle, {
            props: {
                initialImageHtml: '<img class="current-space-image" src="/s3.jpg">',
                noSpaceIconHtml: '<i class="fa fa-dot-circle-o"></i>',
            },
        });

        expect(wrapper.find('img.current-space-image').exists()).toBe(true);

        // A pjax navigation out of the space section, the way the platform announces it.
        globalThis.humhub.modules.space = { isSpacePage: () => false };
        globalThis.humhubStubs.event.trigger('humhub:ready', []);
        await flushPromises();

        expect(wrapper.find('img.current-space-image').exists()).toBe(false);
        expect(wrapper.find('.no-space').text()).toContain('My spaces');
    });

    it('follows a pjax navigation into another space', async () => {
        wrapper = mount(SpaceChooserToggle, { props: { noSpaceIconHtml: '<i></i>' } });

        expect(wrapper.find('.no-space').exists()).toBe(true);

        globalThis.humhubStubs.event.trigger('humhub:space:changed', [
            { guid: 's9', image: '<img class="current-space-image" src="/s9.jpg">' },
        ]);
        await flushPromises();

        expect(wrapper.find('img.current-space-image').attributes('src')).toBe('/s9.jpg');
    });
});
