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

        // main 240 + chip 160 + 2 dates 240 + actions 48: 688 do not fit into 520.
        Object.defineProperty(wrap, 'clientWidth', { configurable: true, value: 520 });
        wrapper.vm.fold();
        await wrapper.vm.$nextTick();

        expect(wrapper.findAll('thead th').map((th) => th.text())).toEqual(['Name', 'Last login', 'Details', 'Actions']);
        const toggle = wrapper.find('tr[data-id="1"] .data-table-details-toggle');
        expect(toggle.attributes('aria-label')).toBe('Details for Emma');
        expect(toggle.attributes('aria-expanded')).toBe('false');

        await toggle.trigger('click');
        const details = wrapper.find('.data-table-row-details');
        expect(details.findAll('dt').map((dt) => dt.text())).toEqual(['Groups', 'Registered']);
        expect(details.find('button.badge').text()).toBe('Admins');
        expect(toggle.attributes('aria-expanded')).toBe('true');

        // Back only with room to spare.
        Object.defineProperty(wrap, 'clientWidth', { configurable: true, value: 700 });
        wrapper.vm.fold();
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('thead th')).toHaveLength(4);
        Object.defineProperty(wrap, 'clientWidth', { configurable: true, value: 800 });
        wrapper.vm.fold();
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('thead th').map((th) => th.text())).toEqual(['Name', 'Groups', 'Last login', 'Registered', 'Actions']);
    });

    it('opens the record with a click on the row, and toggles the details of a row without one', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope([
            row(1, 'Emma'),
            row(2, 'Noah', { cells: { ...row(2, 'Noah').cells, name: { text: 'Noah' } } }),
        ])));
        wrapper = mountTable();
        await flushPromises();
        const open = vi.spyOn(wrapper.vm, 'open').mockImplementation(() => {});

        await wrapper.find('tr[data-id="1"] td.data-table-col-date').trigger('click');
        expect(open).toHaveBeenCalledWith('/u/1', false);
        await wrapper.find('tr[data-id="1"] td.data-table-col-date').trigger('click', { ctrlKey: true });
        expect(open).toHaveBeenLastCalledWith('/u/1', true);
        // Not a click on a button inside the row.
        await wrapper.find('tr[data-id="1"] button.badge').trigger('click');
        expect(open).toHaveBeenCalledTimes(2);
        expect(wrapper.find('tr[data-id="1"]').classes()).toContain('data-table-row-link');

        // Noah has no URL: nothing without folded columns, the details with them.
        await wrapper.find('tr[data-id="2"] td.data-table-col-date').trigger('click');
        expect(open).toHaveBeenCalledTimes(2);
        Object.defineProperty(wrapper.find('.data-table-wrap').element, 'clientWidth', { configurable: true, value: 520 });
        wrapper.vm.fold();
        await wrapper.vm.$nextTick();
        await wrapper.find('tr[data-id="2"] td.data-table-col-date').trigger('click');
        expect(wrapper.find('.data-table-row-details').exists()).toBe(true);
        expect(wrapper.find('tr[data-id="2"]').classes()).toContain('data-table-row-toggle');
    });

    describe('the columns chooser', () => {
        const optional = [
            { key: 'name', type: 'main', label: 'Name' },
            { key: 'groups', type: 'chip', label: 'Groups', priority: 2 },
            { key: 'lastLogin', type: 'date', label: 'Last login', priority: 1 },
            { key: 'createdAt', type: 'date', label: 'Registered', priority: 3, visible: false },
            { key: 'username', type: 'text', label: 'Username', priority: 3, visible: false },
        ];
        const headers = () => wrapper.findAll('thead th').map((th) => th.text()).filter((text) => text !== 'Actions');
        const options = () => [...document.querySelectorAll('.data-table-columns-option input')];

        beforeEach(() => {
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({}));
        });

        it('shows the default columns, or the ones the user chose', async () => {
            wrapper = mountTable({ columns: optional });
            await flushPromises();
            expect(headers()).toEqual(['Name', 'Groups', 'Last login']);

            wrapper.unmount();
            wrapper = mountTable({ columns: optional, columnChoice: ['username', 'unknown'] });
            await flushPromises();
            expect(headers()).toEqual(['Name', 'Username']);
        });

        it('chooses the columns in a dialog, within the limit, and saves the choice for the user', async () => {
            wrapper = mountTable({ columns: optional, columnsUrl: '/api/v2/account/table/users', maxColumns: 3 });
            await flushPromises();

            await wrapper.find('.data-table-columns-toggle').trigger('click');
            await flushPromises();
            expect(document.querySelector('.data-table-columns-limit').textContent).toBe('Choose up to 3 columns.');
            expect(options().map((input) => input.checked)).toEqual([true, true, false, false]);
            expect(document.querySelector('.data-table-columns-reset')).toBeNull();

            options()[2].click();
            await flushPromises();
            // At the limit, the switches that are off are locked.
            expect(options().map((input) => input.disabled)).toEqual([false, false, false, true]);
            expect(document.querySelector('.data-table-columns-reset')).not.toBeNull();

            document.querySelector('.data-table-columns-save').click();
            await flushPromises();
            expect(headers()).toEqual(['Name', 'Groups', 'Last login', 'Registered']);
            const [url, cfg] = globalThis.humhubStubs.client.ajax.mock.calls[0];
            expect(url).toBe('/api/v2/account/table/users');
            expect(cfg.method).toBe('PUT');
            expect(JSON.parse(cfg.data)).toEqual({ columns: ['groups', 'lastLogin', 'createdAt'] });
        });

        it('resets to the defaults and saves them as no choice', async () => {
            wrapper = mountTable({ columns: optional, columnsUrl: '/api/v2/account/table/users', columnChoice: ['username'] });
            await flushPromises();

            await wrapper.find('.data-table-columns-toggle').trigger('click');
            await flushPromises();
            document.querySelector('.data-table-columns-reset').click();
            await flushPromises();
            expect(options().map((input) => input.checked)).toEqual([true, true, false, false]);
            document.querySelector('.data-table-columns-save').click();
            await flushPromises();

            expect(headers()).toEqual(['Name', 'Groups', 'Last login']);
            expect(JSON.parse(globalThis.humhubStubs.client.ajax.mock.calls[0][1].data)).toEqual({ columns: null });
        });

        it('marks a chosen column folded away at this width', async () => {
            wrapper = mountTable({ columns: optional });
            await flushPromises();
            Object.defineProperty(wrapper.find('.data-table-wrap').element, 'clientWidth', { configurable: true, value: 470 });
            wrapper.vm.fold();
            await wrapper.vm.$nextTick();

            await wrapper.find('.data-table-columns-toggle').trigger('click');
            await flushPromises();
            const folded = [...document.querySelectorAll('.data-table-columns-option')]
                .filter((option) => option.querySelector('.data-table-columns-folded'))
                .map((option) => option.querySelector('label').textContent);
            expect(folded).toEqual(['Groups']);
        });
    });

    describe('the selection', () => {
        const bulkActions = [
            { id: 'enable', label: 'Enable', kind: 'request', bulkUrl: '/api/v2/admin/user/bulk/enable' },
            { id: 'disable', label: 'Disable', kind: 'request', bulkUrl: '/api/v2/admin/user/bulk/disable' },
            { id: 'edit', label: 'Edit', kind: 'link' },
        ];
        const offering = (id, name, ids) => row(id, name, { actions: ids.map((action) => ({ id: action, url: `/x/${id}/${action}` })) });
        const checkbox = (id) => wrapper.find(`tr[data-id="${id}"] .data-table-col-select input`);
        const bulkItems = () => wrapper.findAll('.data-table-bulk .dropdown-item').map((item) => item.text());

        beforeEach(() => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(envelope(
                [offering(1, 'Emma', ['disable', 'edit']), offering(2, 'Noah', ['disable']), offering(3, 'Mia', ['enable'])],
                { total: 30, pages: 2 },
            )));
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ done: 2, skipped: 0, message: '2 users were changed.' }));
        });

        it('offers no checkboxes without a bulk action', async () => {
            wrapper = mountTable();
            await flushPromises();
            expect(wrapper.find('.data-table-col-select').exists()).toBe(false);
        });

        it('selects rows, a range with Shift and the page, and shows the selection menu instead of the ⋮', async () => {
            wrapper = mountTable({ actions: bulkActions, headerMenu: [{ id: 'export', label: 'Export', url: '/export' }] });
            await flushPromises();
            expect(wrapper.find('.card-header-actions-menu').exists()).toBe(true);

            await checkbox(1).trigger('click');
            expect(wrapper.find('tr[data-id="1"]').classes()).toContain('table-active');
            expect(wrapper.find('.data-table-bulk-toggle').text()).toBe('1');
            expect(wrapper.find('.card-header-actions-menu').exists()).toBe(false);
            expect(bulkItems()).toEqual(['Disable (1)', 'Select all 30', 'Clear selection']);
            const header = wrapper.find('thead .data-table-col-select input').element;
            expect(header.indeterminate).toBe(true);

            await checkbox(3).trigger('click', { shiftKey: true });
            expect(wrapper.find('.data-table-bulk-toggle').text()).toBe('3');
            expect(bulkItems()).toEqual(['Enable (1)', 'Disable (2)', 'Select all 30', 'Clear selection']);
            expect(header.checked).toBe(true);

            await wrapper.find('thead .data-table-col-select input').setValue(false);
            expect(wrapper.find('.data-table-bulk').exists()).toBe(false);
        });

        it('applies a bulk action to the selected rows offering it, then reloads', async () => {
            wrapper = mountTable({ actions: bulkActions });
            await flushPromises();
            await wrapper.find('thead .data-table-col-select input').setValue(true);

            await wrapper.findAll('.data-table-bulk .dropdown-item').find((item) => item.text() === 'Disable (2)').trigger('click');
            await flushPromises();

            expect(globalThis.humhubStubs.modal.confirm).toHaveBeenCalledWith(expect.objectContaining({ body: 'Apply "Disable" to 2 selected entries?' }));
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/admin/user/bulk/disable', { data: { ids: [1, 2] } });
            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(2);
            expect(wrapper.find('.data-table-bulk').exists()).toBe(false);
        });

        it('selects every row of the filters and sends them as "all" with the filters', async () => {
            window.history.replaceState(null, '', '/admin/user/list?group=2');
            wrapper = mountTable({ actions: bulkActions });
            await flushPromises();
            await checkbox(2).trigger('click');

            await wrapper.findAll('.data-table-bulk .dropdown-item').find((item) => item.text() === 'Select all 30').trigger('click');
            expect(wrapper.find('.data-table-bulk-toggle').text()).toBe('30');
            expect(bulkItems()).toEqual(['Enable', 'Disable', 'Clear selection']);

            await wrapper.findAll('.data-table-bulk .dropdown-item').find((item) => item.text() === 'Enable').trigger('click');
            await flushPromises();
            expect(globalThis.humhubStubs.modal.confirm).toHaveBeenCalledWith(expect.objectContaining({ body: 'Apply "Enable" to all 30 entries?' }));
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/admin/user/bulk/enable?group=2', { data: { all: 1 } });
        });

        it('clears the selection with a filter or sort change, and keeps it across pages', async () => {
            wrapper = mountTable({ actions: bulkActions });
            await flushPromises();
            await checkbox(1).trigger('click');

            await wrapper.findAll('.pagination button').at(-1).trigger('click');
            await flushPromises();
            expect(wrapper.find('.data-table-bulk-toggle').text()).toBe('1');

            await wrapper.find('.data-table-sort').trigger('click');
            await flushPromises();
            expect(wrapper.find('.data-table-bulk').exists()).toBe(false);
        });
    });
});
