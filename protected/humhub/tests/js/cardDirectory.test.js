import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { h } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import CardDirectory from '../../vue/CardDirectory.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const filters = [
    { key: 'q', type: 'text', label: 'Search' },
    { key: 'status', type: 'tags', multiple: true, label: 'Status', options: [{ value: '', label: 'All' }, { value: 'installed', label: 'Installed' }] },
    { key: 'id', type: 'text', hidden: true },
];
const envelope = (results, extra = {}) => ({ results, total: results.length, page: 1, pageSize: 24, pages: 1, ...extra });
const params = (url) => new URL(url, 'http://localhost').searchParams;

const mountDirectory = (props = {}) => mount(CardDirectory, {
    props: { url: '/api/v2/things', filters, ...props },
    slots: { card: ({ item }) => h('span', { class: 'thing' }, item.name) },
});

const deferred = () => {
    let resolve;
    const promise = new Promise((r) => { resolve = r; });
    return { promise, resolve };
};

describe('CardDirectory', () => {
    beforeEach(() => {
        window.history.replaceState(null, '', '/directory');
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([{ id: 1, name: 'One' }])));
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('fetches the first page and renders the cards', async () => {
        const wrapper = mountDirectory();
        await flushPromises();

        const url = globalThis.humhubStubs.client.get.mock.calls[0][0];
        expect(url.startsWith('/api/v2/things?')).toBe(true);
        expect(params(url).get('page')).toBe('1');
        expect(params(url).get('pageSize')).toBe('24');
        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['One']);
    });

    it('starts from the values in the page URL', async () => {
        window.history.replaceState(null, '', '/directory?q=abc&status=installed');
        mountDirectory();
        await flushPromises();

        // One request, with the values the FilterBar read from the URL while it was created.
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        const query = params(globalThis.humhubStubs.client.get.mock.calls[0][0]);
        expect(query.get('q')).toBe('abc');
        expect(query.get('status')).toBe('installed');
    });

    // URL sync, the debounce and the dropping of hidden filters are the FilterBar's
    // (filterBar.test.js) - here only that their result reaches the endpoint.
    it('fetches with the debounced search and reports the values it loaded with', async () => {
        vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        const wrapper = mountDirectory();
        await flushPromises();

        await wrapper.find('input[type="text"]').setValue('cal');
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        vi.advanceTimersByTime(300);
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(2);
        expect(params(globalThis.humhubStubs.client.get.mock.calls[1][0]).get('q')).toBe('cal');
        expect(window.location.search).toBe('?q=cal');
        expect(wrapper.emitted('loaded').at(-1)[0].values.q).toBe('cal');
    });

    it('applies a tag at once and drops a hidden filter', async () => {
        window.history.replaceState(null, '', '/directory?id=calendar');
        const wrapper = mountDirectory();
        await flushPromises();
        expect(params(globalThis.humhubStubs.client.get.mock.calls[0][0]).get('id')).toBe('calendar');

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        await flushPromises();

        const query = params(globalThis.humhubStubs.client.get.mock.calls[1][0]);
        expect(query.get('status')).toBe('installed');
        expect(query.has('id')).toBe(false);
        expect(window.location.search).toBe('?status=installed');
    });

    it('never lets an older response overwrite a newer one', async () => {
        const first = deferred();
        const second = deferred();
        globalThis.humhubStubs.client.get = vi.fn()
            .mockReturnValueOnce(first.promise)
            .mockReturnValueOnce(second.promise);
        const wrapper = mountDirectory();

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        second.resolve(envelope([{ id: 2, name: 'Newer' }]));
        await flushPromises();
        first.resolve(envelope([{ id: 1, name: 'Older' }]));
        await flushPromises();

        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['Newer']);
    });

    it('appends further pages', async () => {
        globalThis.humhubStubs.client.get = vi.fn()
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 1, name: 'One' }], { total: 2, pages: 2 })))
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 2, name: 'Two' }], { total: 2, page: 2, pages: 2 })));
        const wrapper = mountDirectory();
        await flushPromises();

        await wrapper.find('.cards-more button').trigger('click');
        await flushPromises();

        expect(params(globalThis.humhubStubs.client.get.mock.calls[1][0]).get('page')).toBe('2');
        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['One', 'Two']);
        expect(wrapper.find('.cards-more').exists()).toBe(false);
    });

    it('shows an error and retries', async () => {
        globalThis.humhubStubs.client.get = vi.fn()
            .mockReturnValueOnce(Promise.reject({ status: 503, message: 'Down' }))
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 1, name: 'One' }])));
        const wrapper = mountDirectory();
        await flushPromises();

        expect(wrapper.find('[role="alert"]').text()).toBe('Down');
        await wrapper.find('.c-card-grid__message--error button').trigger('click');
        await flushPromises();
        expect(wrapper.findAll('.thing')).toHaveLength(1);
    });

    // CardGrid emits `retry` both when the first page has no items and when a failure hits a
    // grid that already shows old cards under a filter set that no longer matches them - those
    // stale cards must not linger under the new filter, so a page-1 failure clears `items`
    // (and `page`/`pages`) and the full-grid error/retry replaces them. Retrying then re-fetches
    // page 1 (the failed page), not necessarily the same as `reload()`'s hard-coded page 1 -
    // `retry()` tracks whichever page actually failed.
    it('clears stale cards when a filter change fails, and retry re-fetches page 1', async () => {
        // The rejection is built lazily (`mockImplementationOnce`, not a pre-built
        // `Promise.reject()`) so it exists only once `client.get()` is actually called and its
        // `.catch()` attached in the same tick — a promise built upfront and consumed only after
        // a later `await` sits unhandled for a moment and trips Node's rejection warning.
        globalThis.humhubStubs.client.get = vi.fn()
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 1, name: 'One' }], { updateCount: 3 })))
            .mockImplementationOnce(() => Promise.reject({ status: 503, message: 'Down' }))
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 2, name: 'Two' }])));
        const wrapper = mountDirectory({ metaKeys: ['updateCount'] });
        await flushPromises();
        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['One']);

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        await flushPromises();

        expect(wrapper.findAll('.thing')).toHaveLength(0);
        expect(wrapper.find('[role="alert"]').text()).toBe('Down');
        // Not just the cards - the counts that described them are stale under the new filter too.
        expect(wrapper.vm.total).toBe(0);
        expect(wrapper.vm.meta).toEqual({});

        await wrapper.find('.c-card-grid__message--error button').trigger('click');
        await flushPromises();

        expect(params(globalThis.humhubStubs.client.get.mock.calls[2][0]).get('page')).toBe('1');
        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['Two']);
    });

    // A failure while paging (page 2) is a different case: the already-shown first page stays
    // put (CardGrid renders the error below the grid, not in place of it), and retry re-fetches
    // that same failed page rather than restarting at page 1.
    it('keeps cards when paging fails, and retry re-fetches the failed page', async () => {
        globalThis.humhubStubs.client.get = vi.fn()
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 1, name: 'One' }], { total: 2, pages: 2 })))
            .mockImplementationOnce(() => Promise.reject({ status: 503, message: 'Down' }))
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 2, name: 'Two' }], { total: 2, page: 2, pages: 2 })));
        const wrapper = mountDirectory();
        await flushPromises();

        await wrapper.find('.cards-more button').trigger('click');
        await flushPromises();

        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['One']);
        expect(wrapper.find('[role="alert"]').text()).toBe('Down');

        await wrapper.find('.cards-error button').trigger('click');
        await flushPromises();

        expect(params(globalThis.humhubStubs.client.get.mock.calls[2][0]).get('page')).toBe('2');
        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['One', 'Two']);
    });

    it('replaces one item and exposes the envelope fields asked for', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([{ id: 1, name: 'One' }], { updateCount: 3 })));
        const wrapper = mountDirectory({ metaKeys: ['updateCount'] });
        await flushPromises();

        expect(wrapper.emitted('loaded')[0][0].meta).toEqual({ updateCount: 3 });
        expect(wrapper.emitted('loaded')[0][0].values).toEqual(expect.objectContaining({ q: '' }));
        wrapper.vm.replaceItem(1, { id: 1, name: 'Changed' });
        await flushPromises();
        expect(wrapper.find('.thing').text()).toBe('Changed');
    });

    it('delegates reloadFilterOptions() to the filter bar', async () => {
        const withRemoteOptions = [
            { key: 'q', type: 'text', label: 'Search' },
            { key: 'categoryId', type: 'select', label: 'Category', options: [{ value: '', label: 'All' }], optionsUrl: '/api/v2/cats' },
        ];
        const wrapper = mountDirectory({ filters: withRemoteOptions });
        await flushPromises();
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/cats');

        globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(
            url === '/api/v2/cats' ? { results: [{ id: 1, name: 'Tools', count: 2 }] } : envelope([]),
        ));
        wrapper.vm.reloadFilterOptions();
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/cats');
        expect(wrapper.findAll('[role="option"]').map((o) => o.text())).toEqual(['Tools (2)']);
    });

    it('renders the page toolbar with title, actions and notice slots', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([{ id: 1, name: 'One' }], { updateCount: 3 })));
        const wrapper = mount(CardDirectory, {
            props: { url: '/api/v2/things', filters, title: 'Things', metaKeys: ['updateCount'] },
            slots: {
                card: ({ item }) => h('span', { class: 'thing' }, item.name),
                actions: ({ meta, total }) => h('button', { class: 'act' }, `${meta.updateCount ?? '-'}/${total}`),
                notice: () => h('p', { class: 'note' }, 'Heads up'),
            },
        });
        await flushPromises();

        const toolbar = wrapper.find('section.c-page-toolbar');
        const title = toolbar.find('h1.c-page-toolbar__title');
        expect(title.text()).toBe('Things');
        expect(toolbar.attributes('aria-labelledby')).toBe(title.attributes('id'));
        expect(toolbar.find('.c-page-toolbar__actions .act').text()).toBe('3/1');
        expect(toolbar.find('.c-filter-bar').exists()).toBe(true);
        expect(wrapper.classes()).toContain('c-card-directory');
        // The notice sits between the toolbar and the grid.
        const notice = wrapper.find('.c-card-directory__notice');
        expect(notice.text()).toBe('Heads up');
        expect(notice.element.previousElementSibling).toBe(toolbar.element);
        expect(notice.element.nextElementSibling).toBe(wrapper.find('.c-card-grid').element);
    });

    it('renders neither header nor notice without title, actions and notice', async () => {
        const wrapper = mountDirectory();
        await flushPromises();

        expect(wrapper.find('.c-page-toolbar__header').exists()).toBe(false);
        expect(wrapper.find('.c-card-directory__notice').exists()).toBe(false);
    });

    it('delegates setFilter() to the filter bar, and rejects an unknown key', async () => {
        const wrapper = mountDirectory();
        await flushPromises();

        expect(wrapper.vm.setFilter('status', ['installed'])).toBe(true);
        await flushPromises();
        expect(params(globalThis.humhubStubs.client.get.mock.calls[1][0]).get('status')).toBe('installed');
        expect(window.location.search).toBe('?status=installed');

        expect(wrapper.vm.setFilter('nope', 'x')).toBe(false);
        await flushPromises();
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(2);
    });

    it('hands every card its index within the page it was loaded with', async () => {
        globalThis.humhubStubs.client.get = vi.fn()
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 1, name: 'One' }, { id: 2, name: 'Two' }], { total: 3, pages: 2 })))
            .mockReturnValueOnce(Promise.resolve(envelope([{ id: 3, name: 'Three' }], { total: 3, page: 2, pages: 2 })));
        const wrapper = mount(CardDirectory, {
            props: { url: '/api/v2/things', filters },
            slots: { card: ({ item, index }) => h('span', { class: 'thing' }, `${item.name}${index}`) },
        });
        await flushPromises();
        await wrapper.find('.cards-more button').trigger('click');
        await flushPromises();

        expect(wrapper.findAll('.thing').map((n) => n.text())).toEqual(['One0', 'Two1', 'Three0']);
        expect(wrapper.findAll('.c-card-grid__cell').map((cell) => cell.element.style.getPropertyValue('--card-stagger-index')))
            .toEqual(['0', '1', '0']);
    });

    it('resets the visible filters through the clear-all X and forwards filter slots', async () => {
        window.history.replaceState(null, '', '/directory?q=abc&status=installed');
        const wrapper = mount(CardDirectory, {
            props: { url: '/api/v2/things', filters },
            slots: {
                card: ({ item }) => h('span', { class: 'thing' }, item.name),
                'filter-status': ({ value }) => h('em', { class: 'custom-status' }, value.join(',')),
            },
        });
        await flushPromises();
        expect(wrapper.find('.custom-status').text()).toBe('installed');

        await wrapper.find('.c-filter-bar__clear').trigger('click');
        await flushPromises();

        const query = params(globalThis.humhubStubs.client.get.mock.calls[1][0]);
        expect(query.has('q')).toBe(false);
        expect(query.has('status')).toBe(false);
        expect(window.location.search).toBe('');
    });

    it('loads without a filter bar when there are no filters', async () => {
        const wrapper = mountDirectory({ filters: [] });
        await flushPromises();

        expect(wrapper.find('.c-filter-bar').exists()).toBe(false);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        expect(wrapper.findAll('.thing')).toHaveLength(1);
        expect(() => wrapper.vm.reloadFilterOptions()).not.toThrow();
        expect(wrapper.vm.setFilter('q', 'x')).toBe(false);
    });

    it('does not sync the URL without syncUrl', async () => {
        window.history.replaceState(null, '', '/directory?q=abc');
        const wrapper = mountDirectory({ syncUrl: false });
        await flushPromises();
        expect(params(globalThis.humhubStubs.client.get.mock.calls[0][0]).has('q')).toBe(false);

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        await flushPromises();
        expect(params(globalThis.humhubStubs.client.get.mock.calls[1][0]).get('status')).toBe('installed');
        expect(window.location.search).toBe('?q=abc');
    });
});
