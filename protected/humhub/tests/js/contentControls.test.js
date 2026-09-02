import { beforeEach, describe, expect, it, vi } from 'vitest';
import { config, flushPromises, mount } from '@vue/test-utils';
import ContentControls from '../../modules/content/vue/ContentControls.vue';
import DropdownMenu from '../../vue/DropdownMenu.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// ContentControls references <DropdownMenu> by tag only — resolved through the global Vue
// registry in production (ContentVueAsset depends on CoreVueAsset). `global.components`
// stands in for that registry here, and `v-additions` for the directive the island runtime
// registers on the real app (DropdownMenu's html escape hatch carries it).
config.global.components = { ...config.global.components, DropdownMenu };
config.global.directives = {
    ...config.global.directives,
    additions: {
        mounted(el) {
            globalThis.humhubStubs.additions.applyTo(jQuery(el));
        },
        updated(el) {
            globalThis.humhubStubs.additions.applyTo(jQuery(el));
        },
    },
};

const capabilities = {
    canEdit: true,
    canDelete: true,
    canAdminDelete: false,
    canPin: false,
    canArchive: true,
    canMove: true,
};

const respondWith = (body) => {
    globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(body));
};

// The menu only fetches when it is actually opened — Bootstrap owns the toggling, so the
// island listens for `show.bs.dropdown` on the toggle. Dispatching it is what a real open
// does here too.
const open = async (wrapper) => {
    wrapper.find('a[data-bs-toggle="dropdown"]').element
        .dispatchEvent(new Event('show.bs.dropdown'));
    await flushPromises();
};

describe('ContentControls', () => {
    beforeEach(() => {
        respondWith({ entries: [], capabilities });
        globalThis.humhubStubs.logCalls.error.length = 0;
    });

    it('renders the toggle without fetching anything', () => {
        const wrapper = mount(ContentControls, { props: { contentId: 42 } });

        expect(wrapper.find('a[data-bs-toggle="dropdown"]').exists()).toBe(true);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('passes rootClass through to the dropdown', () => {
        const wrapper = mount(ContentControls, {
            props: { contentId: 42, rootClass: 'nav nav-pills mb-0' },
        });

        expect(wrapper.element.className).toBe('nav nav-pills mb-0');
    });

    it('fetches the menu on open, once, and passes the view context along', async () => {
        const wrapper = mount(ContentControls, {
            props: { contentId: 42, viewContext: 'browser' },
        });

        await open(wrapper);

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        expect(globalThis.humhubStubs.client.get.mock.calls[0][0])
            .toContain('content/42/controls');
        expect(globalThis.humhubStubs.client.get.mock.calls[0][0])
            .toContain('viewContext=browser');

        await open(wrapper);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
    });

    it('tells the server which core entries the host renders itself', async () => {
        const wrapper = mount(ContentControls, {
            props: { contentId: 42, suppress: ['edit', 'delete', 'pin'] },
        });

        await open(wrapper);

        expect(globalThis.humhubStubs.client.get.mock.calls[0][0])
            .toContain('suppress=edit%2Cdelete%2Cpin');
    });

    it('renders the host entries immediately and appends the server ones after loading', async () => {
        respondWith({
            entries: [{ id: 'topics', label: 'Topics', icon: 'tags', sortOrder: 370 }],
            capabilities,
        });

        const wrapper = mount(ContentControls, {
            props: {
                contentId: 42,
                entries: [{ id: 'download', label: 'Download', sortOrder: 10 }],
            },
        });

        expect(wrapper.findAll('.dropdown-item').map((i) => i.text())).toEqual(['Download']);

        await open(wrapper);

        expect(wrapper.findAll('.dropdown-item').map((i) => i.text()))
            .toEqual(['Download', 'Topics']);
    });

    it('lets a host entry win over a server entry of the same id', async () => {
        respondWith({
            entries: [{ id: 'edit', label: 'Server edit', url: '/edit' }],
            capabilities,
        });

        const wrapper = mount(ContentControls, {
            props: { contentId: 42, entries: [{ id: 'edit', label: 'Native edit' }] },
        });

        await open(wrapper);

        const items = wrapper.findAll('.dropdown-item');
        expect(items).toHaveLength(1);
        expect(items[0].text()).toBe('Native edit');
    });

    it('exposes the capabilities to entry conditions and emits them', async () => {
        respondWith({ entries: [], capabilities: { ...capabilities, canDelete: false } });

        const wrapper = mount(ContentControls, {
            props: {
                contentId: 42,
                entries: [{
                    id: 'delete',
                    label: 'Delete',
                    condition: (context) => context.capabilities.canDelete === true,
                }],
            },
        });

        await open(wrapper);

        expect(wrapper.findAll('.dropdown-item')).toHaveLength(0);
        expect(wrapper.emitted('loaded')).toBeTruthy();
        expect(wrapper.emitted('loaded')[0][0].canDelete).toBe(false);
    });

    it('passes the host context through to the entries', async () => {
        const seen = [];
        const wrapper = mount(ContentControls, {
            props: {
                contentId: 42,
                context: { item: { id: 7 } },
                entries: [{
                    id: 'probe',
                    label: 'Probe',
                    condition: (context) => {
                        seen.push(context);
                        return true;
                    },
                }],
            },
        });

        await open(wrapper);

        expect(seen[seen.length - 1].item).toEqual({ id: 7 });
        expect(seen[seen.length - 1].contentId).toBe(42);
    });

    it('renders a server entry that could only be described as raw html', async () => {
        respondWith({
            entries: [{ id: 'legacy', html: '<a class="dropdown-item poll-close">Close poll</a>', sortOrder: 500 }],
            capabilities,
        });

        const wrapper = mount(ContentControls, { props: { contentId: 42 } });
        await open(wrapper);

        expect(wrapper.find('a.poll-close').exists()).toBe(true);
    });

    it('keeps the host entries usable and retries when the request fails', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('boom')));

        const wrapper = mount(ContentControls, {
            props: { contentId: 42, entries: [{ id: 'download', label: 'Download' }] },
        });

        await open(wrapper);

        expect(wrapper.findAll('.dropdown-item').map((i) => i.text())).toEqual(['Download']);
        expect(globalThis.humhubStubs.logCalls.error.length).toBeGreaterThan(0);

        // Not marked loaded: reopening asks again rather than leaving the menu short forever.
        respondWith({ entries: [{ id: 'topics', label: 'Topics' }], capabilities });
        await open(wrapper);

        expect(wrapper.findAll('.dropdown-item').map((i) => i.text()))
            .toEqual(['Download', 'Topics']);
    });

    it('lets a module override a server entry through the client registry', async () => {
        respondWith({
            entries: [{ id: 'report', label: 'Report', url: '/report' }],
            capabilities,
        });
        vueModule.registerMenuEntry('content.controls', { id: 'report', label: 'Report (Vue)' });

        const wrapper = mount(ContentControls, { props: { contentId: 42 } });
        await open(wrapper);

        const items = wrapper.findAll('.dropdown-item');
        expect(items).toHaveLength(1);
        expect(items[0].text()).toBe('Report (Vue)');

        vueModule.removeMenuEntry('content.controls', 'report');
    });

    /**
     * A host that raises this menu from its own row (a right-click) cannot reach the toggle
     * itself, so `open()` has to be reachable from the outside — and has to land on the
     * DropdownMenu inside, pointer position included.
     */
    it('forwards open() to the menu inside, so a host can raise it from a right-click', () => {
        const wrapper = mount(ContentControls, { props: { contentId: 42 } });
        const menu = wrapper.findComponent(DropdownMenu);
        const spy = vi.spyOn(menu.vm, 'open').mockImplementation(() => {});
        const event = { clientX: 30, clientY: 60 };

        wrapper.vm.open(event);

        expect(spy).toHaveBeenCalledWith(event);
    });
});
