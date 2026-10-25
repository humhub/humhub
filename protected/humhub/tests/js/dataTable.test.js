import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import DataTable from '../../vue/DataTable.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const columns = [
    { key: 'name', type: 'main', label: 'Name', priority: 1, sort: 'name' },
    { key: 'groups', type: 'chip', label: 'Groups', priority: 2, filter: 'group' },
    { key: 'lastLogin', type: 'date', label: 'Last login', priority: 1, sort: 'lastLogin' },
    { key: 'createdAt', type: 'date', label: 'Registered', priority: 3 },
];
const actions = [
    { id: 'edit', label: 'Edit', kind: 'link', icon: 'pencil' },
    { id: 'disable', label: 'Disable', kind: 'request', confirm: 'Disable this user?' },
    { id: 'impersonate', label: 'Impersonate', kind: 'post' },
    { id: 'delete', label: 'Delete', kind: 'link', destructive: true },
];
const filters = [
    { key: 'q', type: 'text', label: 'Search' },
    { key: 'group', type: 'select', label: 'Group', options: [{ value: '1', label: 'Admins' }, { value: '2', label: 'Users' }] },
];
const row = (id, name, extra = {}) => ({
    id,
    cells: {
        name: { text: name, subtitle: `${name.toLowerCase()}@example.com`, image: '/img.png', url: `/u/${id}` },
        groups: [{ text: 'Admins', value: '1' }],
        lastLogin: { value: '2026-10-25T10:00:00+00:00', text: 'Oct 25, 2026', title: 'Oct 25, 2026, 10:00' },
        createdAt: { value: '2026-01-01T10:00:00+00:00', text: 'Jan 1, 2026', title: 'Jan 1, 2026, 10:00' },
    },
    actions: [
        { id: 'edit', url: `/admin/user/edit?id=${id}` },
        { id: 'disable', url: `/api/v2/admin/user/${id}/disable` },
        { id: 'impersonate', url: `/admin/user/impersonate?id=${id}` },
        { id: 'delete', url: `/admin/user/delete?id=${id}` },
    ],
    ...extra,
});
const envelope = (results, extra = {}) => ({ results, total: results.length, page: 1, pageSize: 25, pages: 1, ...extra });
const params = (url) => new URL(url, 'http://localhost').searchParams;
const lastRequest = () => params(globalThis.humhubStubs.client.get.mock.calls.at(-1)[0]);

const mountTable = (props = {}) => mount(DataTable, {
    attachTo: document.body,
    props: {
        tableId: 'users',
        label: 'Users',
        url: '/api/v2/admin/user',
        columns,
        actions,
        filters,
        labels: { one: '{count} user', other: '{count} users', range: '{from}–{to} of {total} users' },
        ...props,
    },
});

let wrapper;

describe('DataTable', () => {
    beforeEach(() => {
        window.history.replaceState(null, '', '/admin/user/list');
        window.localStorage.clear();
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([row(1, 'Emma'), row(2, 'Noah')])));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
    });

    afterEach(() => {
        wrapper?.unmount();
        wrapper = undefined;
    });

    it('loads the first page and renders the typed cells', async () => {
        wrapper = mountTable();
        expect(wrapper.findAll('.data-table-skeleton')).toHaveLength(5);
        await flushPromises();

        const query = lastRequest();
        expect(query.get('page')).toBe('1');
        expect(query.get('pageSize')).toBe('25');
        expect(query.has('sort')).toBe(false);

        const first = wrapper.find('tr[data-id="1"]');
        expect(first.find('th[scope="row"] .data-table-main-name').text()).toBe('Emma');
        expect(first.find('.data-table-main-name').attributes('href')).toBe('/u/1');
        expect(first.find('.data-table-main-subtitle').text()).toBe('emma@example.com');
        expect(first.find('button.badge').text()).toBe('Admins');
        expect(first.find('time').attributes('datetime')).toBe('2026-10-25T10:00:00+00:00');
        expect(first.find('time').attributes('title')).toBe('Oct 25, 2026, 10:00');
        expect(wrapper.find('.data-table-count').text()).toBe('2 users');
    });

    it('sorts by a column: ascending, descending, then the default order, back on page 1', async () => {
        wrapper = mountTable();
        await flushPromises();
        const header = () => wrapper.findAll('th')[0];

        expect(header().attributes('aria-sort')).toBe('none');
        await header().find('.data-table-sort').trigger('click');
        await flushPromises();
        expect(lastRequest().get('sort')).toBe('name');
        expect(lastRequest().get('page')).toBe('1');
        expect(header().attributes('aria-sort')).toBe('ascending');

        await header().find('.data-table-sort').trigger('click');
        await flushPromises();
        expect(lastRequest().get('sort')).toBe('-name');
        expect(header().attributes('aria-sort')).toBe('descending');

        await header().find('.data-table-sort').trigger('click');
        await flushPromises();
        expect(lastRequest().has('sort')).toBe(false);
        expect(header().attributes('aria-sort')).toBe('none');
        // A column without `sort` has no button and no aria-sort.
        expect(wrapper.findAll('th')[1].find('.data-table-sort').exists()).toBe(false);
        expect(wrapper.findAll('th')[1].attributes('aria-sort')).toBeUndefined();
    });

    it('sets the column filter when a chip is pressed', async () => {
        wrapper = mountTable();
        await flushPromises();

        await wrapper.find('tr[data-id="1"] button.badge').trigger('click');
        await flushPromises();

        expect(lastRequest().get('group')).toBe('1');
        expect(window.location.search).toBe('?group=1');
    });

    it('shows an empty state, with "Clear filters" while filters are set', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([])));
        wrapper = mountTable();
        await flushPromises();

        expect(wrapper.find('.data-table-message').text()).toContain('No results found!');
        expect(wrapper.find('.data-table-message button').exists()).toBe(false);
        expect(wrapper.find('.data-table-footer').exists()).toBe(false);

        window.history.replaceState(null, '', '/admin/user/list?group=2');
        wrapper.unmount();
        wrapper = mountTable();
        await flushPromises();
        const clear = wrapper.find('.data-table-message button');
        expect(clear.text()).toBe('Clear filters');
        await clear.trigger('click');
        await flushPromises();
        expect(lastRequest().has('group')).toBe(false);
    });

    it('replaces the rows with an error and retries', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject({ message: 'Boom' }));
        wrapper = mountTable();
        await flushPromises();

        expect(wrapper.find('.data-table-message [role="alert"]').text()).toBe('Boom');
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([row(1, 'Emma')])));
        await wrapper.find('.data-table-message button').trigger('click');
        await flushPromises();
        expect(wrapper.findAll('tr[data-id]')).toHaveLength(1);
    });

    it('pages with at most five slots and counts the range', async () => {
        globalThis.humhubStubs.client.get = vi.fn((url) => {
            const page = Number(params(url).get('page'));
            return Promise.resolve(envelope([row(page, `User ${page}`)], { total: 300, page, pages: 12 }));
        });
        wrapper = mountTable();
        await flushPromises();
        const slots = () => wrapper.findAll('.pagination .page-item').slice(1, -1).map((item) => item.text());

        expect(wrapper.find('.data-table-count').text()).toBe('1–25 of 300 users');
        expect(slots()).toEqual(['1', '2', '3', '…', '12']);
        expect(wrapper.find('.pagination [aria-current="page"]').text()).toBe('1');
        expect(wrapper.find('.pagination .page-item').classes()).toContain('disabled');

        await wrapper.findAll('.pagination button').find((button) => button.text() === '3').trigger('click');
        await flushPromises();
        expect(lastRequest().get('page')).toBe('3');
        await wrapper.findAll('.pagination button').at(-1).trigger('click');
        await flushPromises();
        expect(lastRequest().get('page')).toBe('4');
        expect(slots()).toEqual(['1', '…', '4', '…', '12']);
        expect(wrapper.find('.data-table-count').text()).toBe('76–100 of 300 users');

        await wrapper.findAll('.pagination button').find((button) => button.text() === '12').trigger('click');
        await flushPromises();
        expect(slots()).toEqual(['1', '…', '10', '11', '12']);
    });

    it('remembers the rows per page and starts again on page 1', async () => {
        wrapper = mountTable();
        await flushPromises();

        await wrapper.find('.data-table-page-size select').setValue('50');
        await flushPromises();
        expect(lastRequest().get('pageSize')).toBe('50');
        expect(lastRequest().get('page')).toBe('1');
        expect(window.localStorage.getItem('humhub.dataTable.users.pageSize')).toBe('50');

        wrapper.unmount();
        wrapper = mountTable();
        await flushPromises();
        expect(lastRequest().get('pageSize')).toBe('50');
    });

    it('offers one action as a button and several in a menu, the destructive ones last in red', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([
            row(1, 'Emma'),
            row(2, 'Noah', { actions: [{ id: 'edit', url: '/admin/user/edit?id=2' }] }),
            row(3, 'Mia', { actions: [] }),
        ])));
        wrapper = mountTable();
        await flushPromises();

        const items = wrapper.findAll('tr[data-id="1"] .dropdown-item');
        expect(items.map((item) => item.text())).toEqual(['Edit', 'Disable', 'Impersonate', 'Delete']);
        expect(items.at(-1).classes()).toContain('text-danger');
        expect(wrapper.find('tr[data-id="1"] .dropdown-divider').exists()).toBe(true);
        expect(wrapper.find('tr[data-id="1"] [data-bs-toggle="dropdown"]').attributes('aria-label')).toBe('More actions for Emma');

        const single = wrapper.find('tr[data-id="2"] .data-table-col-actions a');
        expect(single.attributes('href')).toBe('/admin/user/edit?id=2');
        expect(single.classes()).toContain('btn-icon-only');
        expect(single.find('.ti-pencil').exists()).toBe(true);
        expect(single.attributes('aria-label')).toBe('Edit: Noah');

        expect(wrapper.find('tr[data-id="3"] .data-table-col-actions').text()).toBe('');
    });

    it('runs a request action after confirmation and replaces the row with the answer', async () => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({
            row: row(1, 'Emma (disabled)', { actions: [{ id: 'edit', url: '/admin/user/edit?id=1' }] }),
        }));
        wrapper = mountTable();
        await flushPromises();

        await wrapper.findAll('tr[data-id="1"] .dropdown-item')[1].trigger('click');
        await flushPromises();

        expect(globalThis.humhubStubs.modal.confirm).toHaveBeenCalledWith(expect.objectContaining({ body: 'Disable this user?', confirmText: 'Disable' }));
        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/admin/user/1/disable');
        expect(wrapper.find('tr[data-id="1"] .data-table-main-name').text()).toBe('Emma (disabled)');
        expect(wrapper.find('tr[data-id="1"]').classes()).not.toContain('is-busy');
    });

    it('does nothing when the confirmation is cancelled, and removes a row the answer removes', async () => {
        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(false));
        wrapper = mountTable();
        await flushPromises();

        await wrapper.findAll('tr[data-id="1"] .dropdown-item')[1].trigger('click');
        await flushPromises();
        expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();

        globalThis.humhubStubs.modal.confirm = vi.fn(() => Promise.resolve(true));
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ removed: true }));
        await wrapper.findAll('tr[data-id="1"] .dropdown-item')[1].trigger('click');
        await flushPromises();
        expect(wrapper.find('tr[data-id="1"]').exists()).toBe(false);
        expect(wrapper.find('.data-table-count').text()).toBe('1 user');
    });

    it('submits a post action as a form with the CSRF token', async () => {
        document.head.insertAdjacentHTML('beforeend', '<meta name="csrf-param" content="_csrf"><meta name="csrf-token" content="t0k3n">');
        const submit = vi.spyOn(HTMLFormElement.prototype, 'submit').mockImplementation(() => {});
        wrapper = mountTable();
        await flushPromises();

        await wrapper.findAll('tr[data-id="1"] .dropdown-item')[2].trigger('click');
        await flushPromises();

        expect(submit).toHaveBeenCalledTimes(1);
        const form = submit.mock.contexts[0];
        expect(form.getAttribute('action')).toBe('/admin/user/impersonate?id=1');
        expect(form.method).toBe('post');
        expect(form.querySelector('input[name="_csrf"]').value).toBe('t0k3n');
        submit.mockRestore();
    });

    it('folds the columns that do not fit into the row details, the highest priority number first', async () => {
        wrapper = mountTable();
        await flushPromises();
        const wrap = wrapper.find('.data-table-wrap').element;

        // main 240 + chip 160 + 2 dates 240 + details/actions 96: 736 do not fit into 520.
        Object.defineProperty(wrap, 'clientWidth', { configurable: true, value: 520 });
        wrapper.vm.fold();
        await wrapper.vm.$nextTick();

        expect(wrapper.findAll('thead th').map((th) => th.text())).toEqual(['Name', 'Last login', 'Details', 'Actions']);
        const toggle = wrapper.find('tr[data-id="1"] .data-table-details-toggle');
        expect(toggle.attributes('aria-label')).toBe('Details for Emma');
        expect(toggle.attributes('aria-expanded')).toBe('false');

        // The whole row toggles - but not a click on its link.
        await wrapper.find('tr[data-id="1"] .data-table-main-name').trigger('click');
        expect(wrapper.find('.data-table-row-details').exists()).toBe(false);
        await wrapper.find('tr[data-id="1"] td.data-table-col-date').trigger('click');
        const details = wrapper.find('.data-table-row-details');
        expect(details.findAll('dt').map((dt) => dt.text())).toEqual(['Groups', 'Registered']);
        expect(details.find('button.badge').text()).toBe('Admins');
        expect(toggle.attributes('aria-expanded')).toBe('true');

        // Back only with room to spare.
        Object.defineProperty(wrap, 'clientWidth', { configurable: true, value: 740 });
        wrapper.vm.fold();
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('thead th')).toHaveLength(4);
        Object.defineProperty(wrap, 'clientWidth', { configurable: true, value: 800 });
        wrapper.vm.fold();
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('thead th').map((th) => th.text())).toEqual(['Name', 'Groups', 'Last login', 'Registered', 'Actions']);
    });
});
