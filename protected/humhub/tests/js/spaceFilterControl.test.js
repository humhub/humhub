import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import SpaceFilterControl from '../../modules/space/vue/SpaceFilterControl.vue';
import { SEARCH_DEBOUNCE_MS } from '../../vue/PickerFilterControl.vue';
import FilterBar from '../../vue/FilterBar.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');
// Registers the component and the `space` filter type, as in production.
await import('../../modules/space/vue/index.js');

const vueModule = globalThis.humhub.modules.vue;

// Items of `GET /api/v2/space` (SpaceSerializer::list(), shortened to what the control reads).
const space = (id, name) => ({
    id,
    guid: `guid-${id}`,
    name,
    url: `/s/${id}`,
    color: '#123456',
    imageUrl: `/img/s${id}.jpg`,
    contentContainerId: 200 + id,
    description: '',
    tags: [],
});
const spaces = [space(2, 'Team Alpha'), space(3, 'Team Beta'), space(8, 'Sales')];

const filter = { key: 'spaceId', type: 'space', label: 'Space', placeholder: 'Any space', multiple: true };

const mounted = [];
const mountControl = (props = {}) => {
    const wrapper = mount(SpaceFilterControl, {
        props: { filter, modelValue: [], inputId: 'filter-spaceId', ...props },
        attachTo: document.body,
    });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const optionTexts = (wrapper) => wrapper.findAll('[role="option"]').map((option) => option.find('.c-space-filter__name').text());
const chipLabels = (wrapper) => wrapper.findAll('.c-picker__chip-label').map((label) => label.text());
const key = (wrapper, name) => input(wrapper).trigger('keydown', { key: name });
const type = async (wrapper, text) => {
    input(wrapper).element.value = text;
    await input(wrapper).trigger('input');
};
const typeAndWait = async (wrapper, text) => {
    await type(wrapper, text);
    vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
    await flushPromises();
};
const choose = async (wrapper, text) => {
    await typeAndWait(wrapper, text);
    await wrapper.find('[role="option"]').trigger('click');
};
const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);
const params = (url) => Object.fromEntries(new URL(url, 'http://localhost').searchParams);
const requests = () => globalThis.humhubStubs.client.get.mock.calls.map(([url]) => params(url));

// An answer per request, resolved when the test says so.
const deferredClient = () => {
    const pending = [];
    globalThis.humhubStubs.client.get = vi.fn((url) => new Promise((resolve, reject) => pending.push({ url, resolve, reject })));
    return pending;
};

describe('SpaceFilterControl', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.client.get = vi.fn((url) => {
            const { q, ids } = params(url);
            const results = ids
                ? spaces.filter((entry) => ids.split(',').includes(String(entry.id)))
                : spaces.filter((entry) => entry.name.toLowerCase().includes(String(q).toLowerCase()));
            return Promise.resolve({ results, total: results.length });
        });
    });

    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
        vi.useRealTimers();
    });

    it('is registered as the `space` filter type', () => {
        expect(vueModule.getFilterType('space')).toBe('SpaceFilterControl');
        expect(vueModule.isRegistered('SpaceFilterControl')).toBe(true);
    });

    it('is a search field and a closed combobox without a value', () => {
        const wrapper = mountControl();
        const field = input(wrapper);

        expect(wrapper.find('.ti-users-group').exists()).toBe(true);
        expect(field.attributes('id')).toBe('filter-spaceId');
        expect(field.attributes('placeholder')).toBe('Any space');
        expect(field.attributes('aria-label')).toBe('Space');
        expect(field.attributes('aria-expanded')).toBe('false');
        expect(field.attributes('aria-controls')).toBe(wrapper.find('[role="listbox"]').attributes('id'));
        expect(wrapper.find('.c-picker__chip').exists()).toBe(false);
    });

    it('suggests the member spaces matching the typed text after the debounce', async () => {
        const wrapper = mountControl();

        await type(wrapper, 'team');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        const url = globalThis.humhubStubs.client.get.mock.calls[0][0];
        expect(new URL(url, 'http://localhost').pathname).toBe('/api/v2/space');
        expect(params(url)).toEqual({ purpose: 'picker', scope: 'member', q: 'team', pageSize: '8' });
        expect(input(wrapper).attributes('aria-expanded')).toBe('true');
        expect(optionTexts(wrapper)).toEqual(['Team Alpha', 'Team Beta']);
        const image = wrapper.find('[role="option"] img');
        expect(image.attributes('src')).toBe('/img/s2.jpg');
        expect(image.attributes('style')).toContain('width: 24px');
    });

    it('searches in the scope of the definition\'s props', async () => {
        const wrapper = mountControl({ filter: { ...filter, props: { scope: 'following' } } });

        await typeAndWait(wrapper, 'team');

        expect(requests()).toEqual([{ purpose: 'picker', scope: 'following', q: 'team', pageSize: '8' }]);
    });

    it('sends no request for an empty text', async () => {
        const wrapper = mountControl();

        await typeAndWait(wrapper, '   ');

        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
    });

    it('emits the chosen space\'s id and shows it as a chip with image and name', async () => {
        const wrapper = mountControl();

        await choose(wrapper, 'beta');

        expect(emitted(wrapper)).toEqual([['3']]);
        await wrapper.setProps({ modelValue: ['3'] });

        const chip = wrapper.find('.c-picker__chip');
        expect(chip.find('.c-picker__chip-label').text()).toBe('Team Beta');
        expect(chip.find('img').attributes('src')).toBe('/img/s3.jpg');
        expect(chip.find('.c-picker__chip-remove').attributes('aria-label')).toBe('Remove Team Beta');
        await flushPromises();
        expect(document.activeElement).toBe(input(wrapper).element, 'the focus stays in the search field');
        expect(input(wrapper).element.value).toBe('');
        // Known from the suggestions: nothing to resolve.
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
    });

    it('appends a second choice', async () => {
        const wrapper = mountControl();
        await choose(wrapper, 'beta');
        await wrapper.setProps({ modelValue: ['3'] });

        await choose(wrapper, 'sales');

        expect(emitted(wrapper).at(-1)).toEqual(['3', '8']);
        await wrapper.setProps({ modelValue: ['3', '8'] });
        expect(chipLabels(wrapper)).toEqual(['Team Beta', 'Sales']);
    });

    it('removes one space with its chip\'s X and returns the focus to the input', async () => {
        const wrapper = mountControl();
        await choose(wrapper, 'beta');
        await wrapper.setProps({ modelValue: ['3'] });
        await choose(wrapper, 'sales');
        await wrapper.setProps({ modelValue: ['3', '8'] });

        await wrapper.findAll('.c-picker__chip-remove')[0].trigger('click');

        expect(emitted(wrapper).at(-1)).toEqual(['8']);
        await wrapper.setProps({ modelValue: ['8'] });
        await flushPromises();
        expect(chipLabels(wrapper)).toEqual(['Sales']);
        expect(document.activeElement).toBe(input(wrapper).element);
    });

    it('does not suggest a space chosen already', async () => {
        const wrapper = mountControl();
        await choose(wrapper, 'beta');
        await wrapper.setProps({ modelValue: ['3'] });

        await typeAndWait(wrapper, 'team');

        expect(optionTexts(wrapper)).toEqual(['Team Alpha']);
    });

    it('resolves the spaces of a value it does not know with one request, and drops unknown ones', async () => {
        const pending = deferredClient();
        const wrapper = mountControl({ modelValue: ['8', '5', '3'] });

        expect(pending).toHaveLength(1);
        expect(new URL(pending[0].url, 'http://localhost').pathname).toBe('/api/v2/space');
        // All on one page, however many: the default page (25) must not cut any off.
        expect(params(pending[0].url)).toEqual({ purpose: 'picker', scope: 'all', ids: '8,5,3', pageSize: '3' });
        expect(wrapper.classes()).toContain('is-loading');
        expect(input(wrapper).attributes('disabled')).toBeDefined();

        pending[0].resolve({ results: [spaces[2], spaces[1]] });
        await flushPromises();

        expect(emitted(wrapper)).toEqual([['8', '3']]);
        await wrapper.setProps({ modelValue: ['8', '3'] });
        await flushPromises();

        expect(wrapper.classes()).not.toContain('is-loading');
        expect(chipLabels(wrapper)).toEqual(['Sales', 'Team Beta']);
        expect(pending).toHaveLength(1);
    });

    it('drops what is no space id at once, without a request', async () => {
        const wrapper = mountControl({ modelValue: ['abc', '3', '0'] });
        await flushPromises();

        expect(emitted(wrapper)).toEqual([['3']]);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('drops an answer to an older text', async () => {
        const pending = deferredClient();
        const wrapper = mountControl();

        await typeAndWait(wrapper, 'team');
        await typeAndWait(wrapper, 'team b');
        expect(pending).toHaveLength(2);

        pending[1].resolve({ results: [spaces[1]] });
        await flushPromises();
        pending[0].resolve({ results: [spaces[0], spaces[1]] });
        await flushPromises();

        expect(optionTexts(wrapper)).toEqual(['Team Beta']);
    });

    it('moves through the suggestions with the keyboard, chooses with Enter and closes with Escape', async () => {
        const wrapper = mountControl();
        await typeAndWait(wrapper, 'team');
        const active = () => input(wrapper).attributes('aria-activedescendant');
        const options = () => wrapper.findAll('[role="option"]');

        expect(active()).toBe(options()[0].attributes('id'));
        await key(wrapper, 'ArrowDown');
        expect(active()).toBe(options()[1].attributes('id'));

        await key(wrapper, 'Escape');
        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
        await key(wrapper, 'ArrowDown');
        expect(input(wrapper).attributes('aria-expanded')).toBe('true', 'ArrowDown opens again');
        await key(wrapper, 'ArrowDown');

        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual([['3']]);
    });

    it('stays usable when a request fails', async () => {
        const pending = deferredClient();
        const wrapper = mountControl({ modelValue: ['8'] });

        pending[0].reject({ status: 500 });
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
        expect(wrapper.classes()).not.toContain('is-loading');
        // The value stays, shown as it is, and can be removed.
        expect(chipLabels(wrapper)).toEqual(['8']);
        await wrapper.find('.c-picker__chip-remove').trigger('click');
        expect(emitted(wrapper)).toEqual([[]]);

        await wrapper.setProps({ modelValue: [] });
        await typeAndWait(wrapper, 'sa');
        pending[1].reject({ status: 500 });
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(2);
        expect(input(wrapper).attributes('disabled')).toBeUndefined();
        expect(optionTexts(wrapper)).toEqual([]);
    });

    it('ignores answers arriving after it was unmounted', async () => {
        const pending = deferredClient();
        const wrapper = mountControl({ modelValue: ['8'] });
        await wrapper.setProps({ modelValue: [] });
        await typeAndWait(wrapper, 'team');
        expect(pending).toHaveLength(2);
        const added = vi.spyOn(document, 'addEventListener');

        mounted.splice(mounted.indexOf(wrapper), 1);
        wrapper.unmount();
        pending[1].resolve({ results: [spaces[0]] });
        pending[0].resolve({ results: [] });
        await flushPromises();

        expect(added).not.toHaveBeenCalled();
        expect(emitted(wrapper)).toEqual([]);
        added.mockRestore();
    });

    it('takes a single space without `multiple`', async () => {
        const single = { key: 'spaceId', type: 'space', label: 'Space', multiple: false };
        const wrapper = mountControl({ filter: single, modelValue: '' });

        await choose(wrapper, 'beta');
        expect(emitted(wrapper)).toEqual(['3']);
        await wrapper.setProps({ modelValue: '3' });

        const chip = wrapper.find('.c-picker__chip');
        expect(chip.attributes('role')).toBe('group');
        expect(chip.attributes('aria-label')).toBe('Space');
        expect(chip.find('.c-picker__chip-label').text()).toBe('Team Beta');
        expect(input(wrapper).exists()).toBe(false);
        expect(chip.find('.c-picker__chip-remove').attributes('id')).toBe('filter-spaceId');

        await chip.find('.c-picker__chip-remove').trigger('click');
        expect(emitted(wrapper).at(-1)).toBe('');

        const resolving = mountControl({ filter: single, modelValue: '8' });
        await flushPromises();
        expect(params(globalThis.humhubStubs.client.get.mock.calls.at(-1)[0])).toEqual({ purpose: 'picker', scope: 'all', ids: '8', pageSize: '1' });
        expect(resolving.find('.c-picker__chip-label').text()).toBe('Sales');
    });

    it('is the control of a `space` definition in a filter bar, which applies the chosen ids', async () => {
        window.history.replaceState(null, '', '/files');
        const wrapper = mount(FilterBar, {
            props: { filters: [{ key: 'spaceId', type: 'space', label: 'Space', multiple: true }], modelValue: {} },
            global: { components: { SpaceFilterControl } },
            attachTo: document.body,
        });
        mounted.push(wrapper);

        const field = () => wrapper.find('.c-filter-bar__item--space input[role="combobox"]');
        expect(field().attributes('id')).toBe('filter-spaceId');

        for (const text of ['alpha', 'sales']) {
            field().element.value = text;
            await field().trigger('input');
            vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
            await flushPromises();
            await wrapper.find('[role="option"]').trigger('click');
            await flushPromises();
        }

        expect(emitted(wrapper).at(-1)).toEqual({ spaceId: ['2', '8'] });
        expect(new URLSearchParams(window.location.search).get('spaceId')).toBe('2,8');
        expect(chipLabels(wrapper)).toEqual(['Team Alpha', 'Sales']);
    });
});
