import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { h } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import FilterBar, { TEXT_DEBOUNCE_MS } from '../../vue/FilterBar.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const filters = [
    { key: 'q', type: 'text', label: 'Search', placeholder: 'Name…' },
    { key: 'categoryId', type: 'select', label: 'Category', options: [{ value: '', label: 'All' }], optionsUrl: '/api/v2/cats' },
    { key: 'status', type: 'tags', multiple: true, label: 'Status', options: [{ value: '', label: 'All' }, { value: 'a', label: 'A' }, { value: 'b', label: 'B' }] },
    { key: 'mine', type: 'checkbox', label: 'Mine' },
    { key: 'id', type: 'text', hidden: true },
];
const values = () => ({ q: '', categoryId: '', status: [], mine: false, id: '' });

const mountBar = (modelValue = values(), options = {}) => mount(FilterBar, {
    props: { filters, modelValue, ...options.props },
    slots: options.slots,
});

const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);

describe('FilterBar', () => {
    beforeEach(() => {
        window.history.replaceState(null, '', '/directory');
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [{ id: 1, name: 'Tools', count: 3 }] }));
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('renders every visible filter and no hidden one', () => {
        const wrapper = mountBar();

        const search = wrapper.find('.c-search-field input[type="text"]');
        expect(search.attributes('placeholder')).toBe('Name…');
        expect(search.attributes('aria-label')).toBe('Search');
        expect(wrapper.find('.c-search-field .ti-search').exists()).toBe(true);
        expect(wrapper.find('.c-select [aria-haspopup="listbox"]').exists()).toBe(true);
        expect(wrapper.find('#filter-categoryId').attributes('aria-label')).toBe('Category');
        expect(wrapper.findAll('[role="group"] button').map((b) => b.text())).toEqual(['All', 'A', 'B']);
        expect(wrapper.find('input[type="checkbox"]').exists()).toBe(true);
        expect(wrapper.find('#filter-id').exists()).toBe(false);
    });

    it('prefixes the control ids with idPrefix', () => {
        const wrapper = mountBar(values(), { props: { idPrefix: 'files-filter' } });

        expect(wrapper.find('#files-filter-categoryId').exists()).toBe(true);
    });

    it('loads remote options and disables the select meanwhile', async () => {
        const wrapper = mountBar();

        expect(wrapper.find('.c-select__button').attributes('disabled')).toBeDefined();
        await flushPromises();
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/cats');
        expect(wrapper.find('.c-select__button').attributes('disabled')).toBeUndefined();
        // The static empty option is the placeholder ("all" state), not a listed option.
        expect(wrapper.find('.c-select__button').text()).toBe('All');
        expect(wrapper.findAll('[role="option"]').map((o) => o.text())).toEqual(['Tools (3)']);
    });

    it('emits nothing on creation when the model already holds the initial values', () => {
        expect(emitted(mountBar())).toEqual([]);
    });

    it('emits the initial values at once: the defaults, completed from an empty model', () => {
        expect(emitted(mountBar({}))).toEqual([values()]);
    });

    it('starts from the values in the page URL and emits them on creation', () => {
        window.history.replaceState(null, '', '/directory?q=abc&status=a,b');
        const wrapper = mountBar();

        expect(emitted(wrapper)).toEqual([{ ...values(), q: 'abc', status: ['a', 'b'] }]);
        expect(wrapper.find('.c-search-field input').element.value).toBe('abc');
    });

    it('neither reads nor writes the page URL without syncUrl', async () => {
        window.history.replaceState(null, '', '/directory?q=abc');
        const wrapper = mountBar(values(), { props: { syncUrl: false } });
        expect(emitted(wrapper)).toEqual([]);

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        expect(emitted(wrapper)).toEqual([{ ...values(), status: ['a'] }]);
        expect(window.location.search).toBe('?q=abc');
    });

    it('debounces typing, then emits the applied values and writes the URL', async () => {
        vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        const wrapper = mountBar();

        await wrapper.find('.c-search-field input').setValue('cal');
        expect(emitted(wrapper)).toEqual([]);
        expect(window.location.search).toBe('');
        vi.advanceTimersByTime(TEXT_DEBOUNCE_MS);

        expect(emitted(wrapper)).toEqual([{ ...values(), q: 'cal' }]);
        expect(window.location.search).toBe('?q=cal');
    });

    // A value edited back to what is already applied, before the debounce fires, must cancel
    // the pending timer rather than just skip scheduling a new one - otherwise the stale timer
    // still fires a redundant apply later for values that were never really "changed".
    it('emits nothing when a debounced value is edited back to the applied one', async () => {
        vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        const wrapper = mountBar();

        await wrapper.find('.c-search-field input').setValue('cal');
        await wrapper.find('.c-search-field input').setValue('');
        vi.advanceTimersByTime(TEXT_DEBOUNCE_MS);
        await flushPromises();

        expect(emitted(wrapper)).toEqual([]);
    });

    it('applies a tag at once and drops a hidden filter', async () => {
        window.history.replaceState(null, '', '/directory?id=calendar');
        const wrapper = mountBar();
        expect(emitted(wrapper)).toEqual([{ ...values(), id: 'calendar' }]);

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        await flushPromises();

        expect(emitted(wrapper).at(-1)).toEqual({ ...values(), status: ['a'] });
        expect(window.location.search).toBe('?status=a');
    });

    // jquery.pjax.modified.js keeps its own state object in `history.state` and compares its
    // `url` field against the current location on `popstate` - refreshing the address bar via
    // replaceState without refreshing that field would leave PJAX comparing against the filter
    // values from before this change. Any other field of that state is passed through as-is.
    it("refreshes a PJAX state's url instead of leaving it stale, keeping its other fields", async () => {
        window.history.replaceState({ id: 'abc', url: window.location.href, container: '#content' }, '', '/directory');
        const wrapper = mountBar();

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        await flushPromises();

        expect(window.history.state).toEqual({
            id: 'abc',
            container: '#content',
            url: `${window.location.origin}/directory?status=a`,
        });
    });

    it('passes a history state through unchanged when it carries no PJAX url', async () => {
        window.history.replaceState({ some: 'thing' }, '', '/directory');
        const wrapper = mountBar();

        await wrapper.findAll('[role="group"] button')[1].trigger('click');
        await flushPromises();

        expect(window.history.state).toEqual({ some: 'thing' });
    });

    it('emits a select choice and its clearing', async () => {
        const wrapper = mountBar({ ...values(), categoryId: '1' });
        await flushPromises();

        expect(wrapper.find('.c-select__button').text()).toBe('Tools (3)');
        await wrapper.find('.c-select__clear').trigger('click');
        await flushPromises();
        expect(emitted(wrapper)[0]).toEqual(values());

        await wrapper.setProps({ modelValue: values() });
        await wrapper.find('.c-select__button').trigger('click');
        await wrapper.find('[role="option"]').trigger('click');
        await flushPromises();
        expect(emitted(wrapper)[1]).toEqual({ ...values(), categoryId: '1' });
    });

    it('toggles multiple tags and clears them with the empty option', async () => {
        const wrapper = mountBar({ ...values(), status: ['a'] });
        const buttons = wrapper.findAll('[role="group"] button');

        expect(buttons[1].classes()).toContain('active');
        await buttons[2].trigger('click');
        await flushPromises();
        expect(emitted(wrapper)[0].status).toEqual(['a', 'b']);
        await buttons[1].trigger('click');
        await flushPromises();
        expect(emitted(wrapper)[1].status).toEqual(['b']);
        await buttons[0].trigger('click');
        await flushPromises();
        expect(emitted(wrapper)[2].status).toEqual([]);
    });

    it('lets a slot replace one filter, its update applied like a change in the bar', async () => {
        const wrapper = mountBar(values(), {
            slots: { 'filter-mine': ({ value, update }) => h('em', { onClick: () => update(!value) }, `custom ${value}`) },
        });

        expect(wrapper.find('em').text()).toBe('custom false');
        expect(wrapper.find('input[type="checkbox"]').exists()).toBe(false);
        await wrapper.find('em').trigger('click');
        await flushPromises();
        expect(emitted(wrapper)).toEqual([{ ...values(), mine: true }]);
        expect(wrapper.find('em').text()).toBe('custom true');
    });

    it('offers a clear-all X while a visible filter differs from its default, and resets them', async () => {
        expect(mountBar().find('.c-filter-bar__clear').exists()).toBe(false);
        // A hidden filter set from outside (a link's context) does not count.
        expect(mountBar({ ...values(), id: 'calendar' }).find('.c-filter-bar__clear').exists()).toBe(false);

        const wrapper = mountBar({ ...values(), q: 'x', status: ['a'] });
        const clear = wrapper.find('.c-filter-bar__clear');
        expect(clear.attributes('aria-label')).toBe('Clear all filters');
        expect(clear.attributes('title')).toBe('Clear all filters');
        expect(clear.attributes('data-filter-bar-keep')).toBeDefined();
        await clear.trigger('click');
        await flushPromises();

        expect(emitted(wrapper)).toEqual([values()]);
        expect(wrapper.find('.c-filter-bar__clear').exists()).toBe(false);
    });

    it('applies setFilter() like a change in the bar, and rejects an unknown key', async () => {
        const wrapper = mountBar();

        expect(wrapper.vm.setFilter('status', ['a'])).toBe(true);
        await flushPromises();
        expect(emitted(wrapper)).toEqual([{ ...values(), status: ['a'] }]);
        expect(window.location.search).toBe('?status=a');

        expect(wrapper.vm.setFilter('nope', 'x')).toBe(false);
        await flushPromises();
        expect(emitted(wrapper)).toHaveLength(1);
    });

    it('applies a text filter set by setFilter() at once, cancelling a pending debounce', async () => {
        vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        const wrapper = mountBar();

        // Typing starts a debounce ...
        await wrapper.find('.c-search-field input[type="text"]').setValue('typ');
        await flushPromises();
        expect(emitted(wrapper)).toEqual([]);

        // ... which setFilter() replaces by applying at once, without waiting.
        expect(wrapper.vm.setFilter('q', 'Alpha')).toBe(true);
        await flushPromises();
        expect(emitted(wrapper)).toEqual([{ ...values(), q: 'Alpha' }]);
        expect(wrapper.find('.c-search-field input[type="text"]').element.value).toBe('Alpha');

        vi.advanceTimersByTime(TEXT_DEBOUNCE_MS);
        await flushPromises();
        expect(emitted(wrapper)).toHaveLength(1);

        // Typing afterwards is debounced again.
        await wrapper.find('.c-search-field input[type="text"]').setValue('Alphab');
        await flushPromises();
        expect(emitted(wrapper)).toHaveLength(1);
        vi.advanceTimersByTime(TEXT_DEBOUNCE_MS);
        await flushPromises();
        expect(emitted(wrapper).at(-1)).toEqual({ ...values(), q: 'Alphab' });
    });

    it('adopts a model changed from outside without emitting it back', async () => {
        vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        const wrapper = mountBar();

        // A pending debounce is dropped, it belongs to the values being replaced.
        await wrapper.find('.c-search-field input').setValue('typed');
        await wrapper.setProps({ modelValue: { ...values(), q: 'outside' } });
        vi.advanceTimersByTime(TEXT_DEBOUNCE_MS);
        await flushPromises();

        expect(emitted(wrapper)).toEqual([]);
        expect(wrapper.find('.c-search-field input').element.value).toBe('outside');
        expect(window.location.search).toBe('?q=outside');
    });

    it('keeps the search field and collapses the other filters behind a funnel toggle', async () => {
        const wrapper = mountBar();
        const items = wrapper.findAll('.c-filter-bar__item');

        expect(items[0].attributes('data-filter-bar-keep')).toBeDefined();
        expect(items.slice(1).every((item) => item.attributes('data-filter-bar-keep') === undefined)).toBe(true);

        // The toggle follows the kept search field.
        const toggle = wrapper.find('.c-filter-bar__toggle');
        expect(toggle.element.previousElementSibling).toBe(items[0].element);
        expect(toggle.attributes('aria-expanded')).toBe('false');
        expect(toggle.attributes('aria-controls')).toBe(wrapper.find('form').attributes('id'));
        expect(toggle.attributes('aria-label')).toBe('Show filters');

        // jsdom has no stylesheet, so the toggle counts as shown - the bar "collapses".
        await toggle.trigger('click');
        expect(wrapper.find('form').classes()).toContain('is-open');
        expect(toggle.attributes('aria-expanded')).toBe('true');
        expect(toggle.attributes('aria-label')).toBe('Hide filters');
        expect(toggle.classes()).toContain('is-active');

        await toggle.trigger('click');
        expect(wrapper.find('form').classes()).not.toContain('is-open');
    });

    it('ignores the toggle while the bar does not collapse', async () => {
        const wrapper = mountBar();
        const toggle = wrapper.find('.c-filter-bar__toggle');
        toggle.element.style.display = 'none';

        await toggle.trigger('click');
        expect(wrapper.find('form').classes()).not.toContain('is-open');
    });

    it('renders no toggle when there is nothing to collapse', () => {
        const wrapper = mountBar({ q: '' }, { props: { filters: [filters[0]] } });

        expect(wrapper.find('.c-filter-bar__toggle').exists()).toBe(false);
    });

    it('reloads every filter with an optionsUrl through reloadOptions()', async () => {
        const wrapper = mountBar();
        await flushPromises();
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);

        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [{ id: 1, name: 'Tools', count: 5 }] }));
        wrapper.vm.reloadOptions();
        await wrapper.vm.$nextTick();
        expect(wrapper.find('.c-select__button').attributes('disabled')).toBeDefined();
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/cats');
        expect(wrapper.findAll('[role="option"]').map((o) => o.text())).toEqual(['Tools (5)']);
    });

    describe('filter types', () => {
        const vueModule = () => globalThis.humhub.modules.vue;

        // A module's filter type control: a button cycling through `open` / `done`.
        const StatusFilter = {
            props: { filter: Object, modelValue: String, inputId: String },
            emits: ['update:modelValue'],
            render() {
                return h('button', {
                    id: this.inputId,
                    type: 'button',
                    class: 'status-filter',
                    onClick: () => this.$emit('update:modelValue', this.modelValue === 'open' ? 'done' : 'open'),
                }, `${this.filter.label}: ${this.modelValue || 'any'}`);
            },
        };
        const typed = (type) => [filters[0], { key: 'state', type, label: 'State' }];
        const mountTyped = (type, options = {}) => mount(FilterBar, {
            props: { filters: typed(type), modelValue: { q: '', state: '' }, ...options.props },
            slots: options.slots,
            global: { components: { TestFilterBarStatus: StatusFilter } },
        });

        it('renders a registered type with its component, which gets the definition, value and id', async () => {
            vueModule().register('TestFilterBarStatus', StatusFilter);
            vueModule().registerFilterType('test.filterbar.status', 'TestFilterBarStatus');
            window.history.replaceState(null, '', '/directory?state=open');
            const wrapper = mountTyped('test.filterbar.status');

            const control = wrapper.find('.c-filter-bar__item--test\\.filterbar\\.status .status-filter');
            expect(control.text()).toBe('State: open');
            expect(control.attributes('id')).toBe('filter-state');

            await control.trigger('click');
            await flushPromises();
            expect(emitted(wrapper).at(-1)).toEqual({ q: '', state: 'done' });
            expect(window.location.search).toBe('?state=done');
            expect(wrapper.find('.status-filter').text()).toBe('State: done');
        });

        it('picks up a type registered after the bar mounted', async () => {
            const wrapper = mountTyped('test.filterbar.late');
            expect(wrapper.find('.status-filter').exists()).toBe(false);

            vueModule().register('TestFilterBarStatus', StatusFilter);
            vueModule().registerFilterType('test.filterbar.late', 'TestFilterBarStatus');
            await wrapper.vm.$nextTick();

            expect(wrapper.find('.status-filter').text()).toBe('State: any');
        });

        it('renders no cell for an unregistered type, but keeps its value in the URL and the model', async () => {
            globalThis.humhubStubs.logCalls.debug.length = 0;
            window.history.replaceState(null, '', '/directory?state=x');
            const wrapper = mountTyped('test.filterbar.unknown');

            expect(wrapper.findAll('.c-filter-bar__item')).toHaveLength(1);
            expect(emitted(wrapper)).toEqual([{ q: '', state: 'x' }]);
            expect(globalThis.humhubStubs.logCalls.debug.some((args) => String(args[0]).includes('test.filterbar.unknown'))).toBe(true);

            await wrapper.find('.c-search-field input').setValue('a');
            await wrapper.vm.setFilter('q', 'a');
            await flushPromises();
            expect(window.location.search).toBe('?state=x&q=a');
        });

        it('lets a filter-<key> slot take precedence over a registered type', () => {
            vueModule().register('TestFilterBarStatus', StatusFilter);
            vueModule().registerFilterType('test.filterbar.slotted', 'TestFilterBarStatus');
            const wrapper = mountTyped('test.filterbar.slotted', {
                slots: { 'filter-state': ({ value }) => h('em', `slot ${value}`) },
            });

            expect(wrapper.find('em').text()).toBe('slot');
            expect(wrapper.find('.status-filter').exists()).toBe(false);
        });
    });

    describe('panel', () => {
        const panelFilters = [
            { key: 'q', type: 'text', label: 'Search' },
            { key: 'sort', type: 'tags', default: 'name', options: [{ value: 'name', label: 'Name' }, { value: 'new', label: 'Newest' }] },
            { key: 'mine', type: 'checkbox', label: 'Mine', placement: 'panel' },
            { key: 'status', type: 'tags', multiple: true, label: 'Status', placement: 'panel', options: [{ value: 'a', label: 'A' }, { value: 'b', label: 'B' }] },
        ];
        const panelValues = () => ({ q: '', sort: 'name', mine: false, status: [] });
        const mountPanel = (modelValue = panelValues(), props = {}) => mount(FilterBar, {
            props: { filters: panelFilters, modelValue, ...props },
            attachTo: document.body,
        });

        it('renders the panel filters in a closed panel behind a "Filters" toggle', () => {
            const wrapper = mountPanel();
            const panel = wrapper.find('.c-filter-bar__panel');
            const toggle = wrapper.find('.c-filter-bar__toggle');

            expect(panel.findAll('.c-filter-bar__item').map((item) => item.classes()).map((c) => c[1])).toEqual(['c-filter-bar__item--checkbox', 'c-filter-bar__item--tags']);
            expect(panel.attributes('role')).toBe('group');
            expect(panel.attributes('aria-label')).toBe('Filters');
            expect(wrapper.find('form').classes()).toContain('has-panel');
            expect(wrapper.find('form').classes()).not.toContain('is-open');

            expect(toggle.classes()).toContain('c-filter-bar__toggle--labeled');
            expect(toggle.text()).toBe('Filters');
            expect(toggle.attributes('aria-controls')).toBe(panel.attributes('id'));
            expect(toggle.attributes('aria-expanded')).toBe('false');
            expect(toggle.attributes('aria-label')).toBeUndefined();
            // The toggle follows the search field, as without a panel.
            expect(toggle.element.previousElementSibling.classList.contains('c-filter-bar__item--text')).toBe(true);
            wrapper.unmount();
        });

        it('opens and closes the panel with the toggle, whatever the width', async () => {
            const wrapper = mountPanel();
            const toggle = wrapper.find('.c-filter-bar__toggle');
            // No funnel breakpoint involved: the toggle is always usable with a panel.
            toggle.element.style.display = 'none';

            await toggle.trigger('click');
            expect(wrapper.find('form').classes()).toContain('is-open');
            expect(toggle.attributes('aria-expanded')).toBe('true');
            expect(toggle.attributes('title')).toBe('Hide filters');

            await toggle.trigger('click');
            expect(wrapper.find('form').classes()).not.toContain('is-open');
            wrapper.unmount();
        });

        it('counts the set panel filters on the toggle', async () => {
            const wrapper = mountPanel();
            expect(wrapper.find('.c-filter-bar__count').exists()).toBe(false);

            await wrapper.find('.c-filter-bar__panel input[type="checkbox"]').setValue(true);
            await wrapper.findAll('.c-filter-bar__panel [role="group"] button')[1].trigger('click');
            // A row filter does not count.
            await wrapper.find('.c-search-field input').setValue('x');

            expect(wrapper.find('.c-filter-bar__count').text()).toBe('2');
            expect(wrapper.find('.c-filter-bar__toggle .visually-hidden').text()).toBe('2 active');
            wrapper.unmount();
        });

        it('opens on load when a panel filter is set, but not for a set row filter', () => {
            window.history.replaceState(null, '', '/directory?mine=1');
            const fromUrl = mountPanel();
            expect(fromUrl.find('form').classes()).toContain('is-open');
            fromUrl.unmount();

            window.history.replaceState(null, '', '/directory');
            expect(mountPanel({ ...panelValues(), status: ['a'] }).find('form').classes()).toContain('is-open');
            expect(mountPanel({ ...panelValues(), q: 'x', sort: 'new' }).find('form').classes()).not.toContain('is-open');
        });

        it('clears the row and the panel filters with the clear X', async () => {
            const wrapper = mountPanel({ ...panelValues(), q: 'x', sort: 'new', mine: true, status: ['a'] });

            await wrapper.find('.c-filter-bar__clear').trigger('click');
            await flushPromises();

            expect(emitted(wrapper)).toEqual([panelValues()]);
            expect(wrapper.find('.c-filter-bar__count').exists()).toBe(false);
            wrapper.unmount();
        });

        it('closes on Escape inside the panel and returns the focus to the toggle', async () => {
            const wrapper = mountPanel({ ...panelValues(), mine: true });
            const checkbox = wrapper.find('.c-filter-bar__panel input[type="checkbox"]');
            checkbox.element.focus();

            await checkbox.trigger('keydown', { key: 'Escape' });

            expect(wrapper.find('form').classes()).not.toContain('is-open');
            expect(document.activeElement).toBe(wrapper.find('.c-filter-bar__toggle').element);
            wrapper.unmount();
        });

        it('keeps the icon-only funnel toggle without a panel', () => {
            const wrapper = mountBar();

            expect(wrapper.find('.c-filter-bar__panel').exists()).toBe(false);
            expect(wrapper.find('.c-filter-bar__toggle').classes()).not.toContain('c-filter-bar__toggle--labeled');
            expect(wrapper.find('.c-filter-bar__toggle').attributes('aria-label')).toBe('Show filters');
        });
    });

    describe('fixed values', () => {
        it('emits fixed values with the others, but neither renders nor URL-syncs them', async () => {
            window.history.replaceState(null, '', '/directory?categoryId=9&spaceId=3');
            const wrapper = mountBar(values(), { props: { fixed: { categoryId: '4', spaceId: 5 } } });

            // A definition whose key is fixed is not rendered, nor read from the URL.
            expect(wrapper.find('#filter-categoryId').exists()).toBe(false);
            expect(emitted(wrapper)).toEqual([{ ...values(), categoryId: '4', spaceId: 5 }]);
            expect(wrapper.find('.c-filter-bar__clear').exists()).toBe(false);

            await wrapper.findAll('[role="group"] button')[1].trigger('click');
            await flushPromises();
            expect(emitted(wrapper).at(-1)).toEqual({ ...values(), status: ['a'], categoryId: '4', spaceId: 5 });
            // Foreign parameters are kept as they are; a fixed key is none of the bar's business.
            expect(window.location.search).toBe('?categoryId=9&spaceId=3&status=a');

            expect(wrapper.vm.setFilter('categoryId', '1')).toBe(false);
        });

        it('keeps fixed values when clearing all filters', async () => {
            const wrapper = mountBar({ ...values(), q: 'x' }, { props: { fixed: { spaceId: 5 } } });

            await wrapper.find('.c-filter-bar__clear').trigger('click');
            await flushPromises();

            expect(emitted(wrapper).at(-1)).toEqual({ ...values(), spaceId: 5 });
        });

        it('applies a changed fixed value at once', async () => {
            vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
            const wrapper = mountBar(values(), { props: { fixed: { spaceId: 5 } } });
            const initial = emitted(wrapper).length;

            await wrapper.setProps({ fixed: { spaceId: 6 } });
            await flushPromises();

            expect(emitted(wrapper)).toHaveLength(initial + 1);
            expect(emitted(wrapper).at(-1).spaceId).toBe(6);
            expect(window.location.search).toBe('');
        });

        it('resets a key removed from fixed to its filter default, or drops it when it is no filter', async () => {
            window.history.replaceState(null, '', '/directory');
            const wrapper = mountBar(values(), { props: { fixed: { categoryId: '4', spaceId: 5 } } });
            const initial = emitted(wrapper).length;

            await wrapper.setProps({ fixed: { spaceId: 5 } });
            await flushPromises();
            expect(emitted(wrapper)).toHaveLength(initial + 1);
            expect(emitted(wrapper).at(-1)).toEqual({ ...values(), spaceId: 5 });
            // The bar owns the filter now - rendered with its default, not the old fixed value.
            expect(wrapper.find('#filter-categoryId').exists()).toBe(true);
            expect(wrapper.find('.c-filter-bar__clear').exists()).toBe(false);
            expect(window.location.search).toBe('');

            await wrapper.setProps({ fixed: {} });
            await flushPromises();
            expect(emitted(wrapper)).toHaveLength(initial + 2);
            expect(emitted(wrapper).at(-1)).toEqual(values());
        });

        it('ignores a new fixed object with the same content and keeps a pending search debounced', async () => {
            vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
            const wrapper = mountBar(values(), { props: { fixed: { spaceId: 5, status: ['a'] } } });
            const initial = emitted(wrapper).length;

            await wrapper.find('.c-search-field input').setValue('ca');
            await wrapper.setProps({ fixed: { status: ['a'], spaceId: '5' } });
            await flushPromises();
            expect(emitted(wrapper)).toHaveLength(initial);

            vi.advanceTimersByTime(TEXT_DEBOUNCE_MS);
            expect(emitted(wrapper)).toHaveLength(initial + 1);
            expect(emitted(wrapper).at(-1).q).toBe('ca');
        });
    });
});

