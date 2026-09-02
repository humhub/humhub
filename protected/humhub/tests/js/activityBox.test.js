import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import ActivityBox from '../../modules/activity/vue/ActivityBox.vue';
import UserImage from '../../modules/user/vue/UserImage.vue';
import SpaceImage from '../../modules/space/vue/SpaceImage.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

// `v-additions` is registered by the island runtime on the real app; here it stands in for
// that registration, so the entries hand themselves to the legacy enhancer pipeline the same
// way (see `commentSection.test.js` for the same stub).
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
        components: { UserImage, SpaceImage },
    },
});

const activity = (overrides = {}) => ({
    id: 7,
    key: 'k7',
    message: '<strong>Jane</strong> created a new post',
    url: '/s/product-team/',
    createdAt: '2026-08-20T10:00:00+00:00',
    groupCount: 1,
    user: { id: 2, guid: 'u2', displayName: 'Jane', url: '/u/jane/', imageUrl: '/u/jane/image.jpg' },
    space: { id: 3, guid: 's3', name: 'Product Team', url: '/s/product-team/', color: null, imageUrl: null },
    ...overrides,
});

const windowPayload = (results = [activity()], nextCursor = null) => ({ results, nextCursor });

const boxProps = (overrides = {}) => ({
    initial: windowPayload(),
    pageSize: 10,
    panelMenuHtml: '<div class="panel-menu">menu</div>',
    ...overrides,
});

const LIVE_EVENT = 'humhub:modules:activity:live:NewActivity';

let wrapper;
let getCalls;
let observers;
let getResponse;

/**
 * The box observes a sentinel to load more. jsdom has no IntersectionObserver, so this stub
 * records every instance and lets a test act as the browser and report an intersection.
 */
class IntersectionObserverStub {
    constructor(callback) {
        this.callback = callback;
        this.observed = [];
        observers.push(this);
    }

    observe(element) {
        this.observed.push(element);
    }

    disconnect() {
        this.observed = [];
    }
}

const intersect = () => {
    const observer = observers.filter((candidate) => candidate.observed.length).pop();
    observer.callback([{ isIntersecting: true, target: observer.observed[0] }]);
};

beforeEach(() => {
    vi.useFakeTimers();
    getCalls = [];
    observers = [];
    globalThis.IntersectionObserver = IntersectionObserverStub;
    getResponse = () => windowPayload([activity({ id: 8, key: 'k8' })], null);
    globalThis.humhubStubs.client.get = (url) => {
        getCalls.push(url);
        return Promise.resolve(getResponse());
    };
    globalThis.humhubStubs.event._handlers.clear();
});

afterEach(() => {
    vi.useRealTimers();
    wrapper?.unmount();
    wrapper = undefined;
    delete globalThis.IntersectionObserver;
});

const triggerLive = async (containerGuid = null) => {
    globalThis.humhubStubs.event.trigger(LIVE_EVENT, [[{ data: { activityId: 99, containerGuid } }]]);
    await vi.advanceTimersByTimeAsync(1000);
    await flushPromises();
};

describe('ActivityBox', () => {
    it('renders the panel from the inlined first page without a request', () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps() });

        // The panel element itself is the widget's mount point (see `ActivityBox::run()`),
        // so the component renders its contents rather than the panel.
        expect(wrapper.find('.panel-menu').text()).toBe('menu');
        expect(wrapper.find('.panel-heading').text()).toContain('activities');
        expect(wrapper.find('#activity-box-content.activities').exists()).toBe(true);
        expect(getCalls).toHaveLength(0);

        const entry = wrapper.find('.activity-entry');
        expect(entry.attributes('data-activity-id')).toBe('7');
        expect(entry.find('a').attributes('href')).toBe('/s/product-team/');
        expect(entry.find('.activity-box-entry').html()).toContain('<strong>Jane</strong> created a new post');
        expect(entry.find('time[data-ui-addition="timeago"]').attributes('datetime'))
            .toBe('2026-08-20T10:00:00+00:00');
    });

    it('marks the panel body as the collapse target of the panel menu', () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps() });

        // `ui.panel.PanelMenu` collapses the first `.collapse` inside the panel and only walks
        // the siblings of its own <ul> when there is none - a walk that cannot work here,
        // because the menu is mounted inside the element carrying `v-html`. Without this class
        // the widget throws on init and the panel menu stays dead (the throw is swallowed by
        // `Component.instance()`).
        const body = wrapper.find('.panel-body');
        expect(body.classes()).toContain('collapse');
        // `show` keeps the body visible until the widget applies the remembered state.
        expect(body.classes()).toContain('show');
        expect(body.find('#activity-box-content').exists()).toBe(true);
    });

    it('renders the space badge on the dashboard and hides it inside a container', () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps() });
        expect(wrapper.findComponent(SpaceImage).exists()).toBe(true);

        wrapper.unmount();
        wrapper = mount(ActivityBox, {
            ...mountOptions(),
            props: boxProps({ containerGuid: 's3' }),
        });

        expect(wrapper.findComponent(SpaceImage).exists()).toBe(false);
        expect(wrapper.findComponent(UserImage).exists()).toBe(true);
    });

    it('shows the empty state without entries', () => {
        wrapper = mount(ActivityBox, {
            ...mountOptions(),
            props: boxProps({ initial: windowPayload([]) }),
        });

        expect(wrapper.find('.activity-entry').exists()).toBe(false);
        expect(wrapper.text()).toContain('There are no activities yet.');
        expect(wrapper.find('.stream-end').exists()).toBe(false);
    });

    it('loads the next page when the sentinel comes into view', async () => {
        wrapper = mount(ActivityBox, {
            ...mountOptions(),
            props: boxProps({ initial: windowPayload([activity()], 'YTE6NDI'), containerGuid: 's3' }),
        });
        await flushPromises();

        expect(wrapper.find('.stream-end').exists()).toBe(true);
        // Mounting arms the observer, it does not fetch: the widget inlines a page that fills
        // the box, so nothing is loaded until the sentinel actually comes into view.
        expect(getCalls).toHaveLength(0);

        intersect();
        await flushPromises();

        expect(getCalls).toHaveLength(1);
        expect(getCalls[0]).toContain('/api/v2/activity');
        // The cursor travels back untouched, and the container scope travels with it.
        expect(getCalls[0]).toContain('cursor=YTE6NDI');
        expect(getCalls[0]).toContain('containerGuid=s3');

        expect(wrapper.findAll('.activity-entry')).toHaveLength(2);
        // The answered page carried no cursor, so there is nothing left to observe.
        expect(wrapper.find('.stream-end').exists()).toBe(false);
    });

    it('never asks for more without a cursor', async () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps() });
        await flushPromises();

        expect(wrapper.find('.stream-end').exists()).toBe(false);
        expect(observers.filter((observer) => observer.observed.length)).toHaveLength(0);
        expect(getCalls).toHaveLength(0);
    });
    it('updates an entry that grew in place, without moving it', async () => {
        wrapper = mount(ActivityBox, {
            ...mountOptions(),
            props: boxProps({
                initial: windowPayload([activity(), activity({ id: 9, key: 'k9' })]),
            }),
        });

        // The same entry (same key), grown into a group and therefore represented by another
        // activity - exactly what the server reports after a grouping.
        getResponse = () => windowPayload([
            activity({ id: 12, key: 'k7', message: '<strong>Jane</strong> and 2 others', groupCount: 3 }),
        ], null);

        await triggerLive();

        expect(getCalls).toHaveLength(1);
        const entries = wrapper.findAll('.activity-entry');
        expect(entries).toHaveLength(2);
        // Replaced where it stood - the second entry is untouched.
        expect(entries[0].attributes('data-activity-id')).toBe('12');
        expect(entries[0].html()).toContain('and 2 others');
        expect(entries[1].attributes('data-activity-id')).toBe('9');
    });

    it('holds a new entry back while the list is scrolled down and inserts it at the top', async () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps() });
        const list = wrapper.find('#activity-box-content').element;
        Object.defineProperty(list, 'scrollTop', { value: 120, writable: true });

        getResponse = () => windowPayload([activity({ id: 20, key: 'k20' }), activity()], null);
        await triggerLive();

        // Still the entry the reader is looking at.
        expect(wrapper.findAll('.activity-entry')).toHaveLength(1);
        expect(wrapper.find('.activity-entry').attributes('data-activity-id')).toBe('7');

        list.scrollTop = 0;
        await wrapper.find('#activity-box-content').trigger('scroll');
        await flushPromises();

        const entries = wrapper.findAll('.activity-entry');
        expect(entries).toHaveLength(2);
        expect(entries[0].attributes('data-activity-id')).toBe('20');
        expect(entries[1].attributes('data-activity-id')).toBe('7');
    });

    it('inserts right away when the list is already at the top', async () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps() });

        getResponse = () => windowPayload([activity({ id: 20, key: 'k20' }), activity()], null);
        await triggerLive();

        expect(wrapper.findAll('.activity-entry')).toHaveLength(2);
        expect(wrapper.find('.activity-entry').attributes('data-activity-id')).toBe('20');
    });

    it('ignores live events of other containers when it is scoped to one', async () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps({ containerGuid: 's3' }) });

        await triggerLive('another-container');
        expect(getCalls).toHaveLength(0);

        await triggerLive('s3');
        expect(getCalls).toHaveLength(1);
    });

    it('asks once for a burst of live events', async () => {
        wrapper = mount(ActivityBox, { ...mountOptions(), props: boxProps() });

        globalThis.humhubStubs.event.trigger(LIVE_EVENT, [[{ data: { activityId: 1, containerGuid: null } }]]);
        globalThis.humhubStubs.event.trigger(LIVE_EVENT, [[{ data: { activityId: 2, containerGuid: null } }]]);
        globalThis.humhubStubs.event.trigger(LIVE_EVENT, [[{ data: { activityId: 3, containerGuid: null } }]]);
        await vi.advanceTimersByTimeAsync(1000);
        await flushPromises();

        expect(getCalls).toHaveLength(1);
    });
});
