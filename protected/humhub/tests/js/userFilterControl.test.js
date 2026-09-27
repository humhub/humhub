import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import UserFilterControl, { SEARCH_DEBOUNCE_MS } from '../../modules/user/vue/UserFilterControl.vue';
import FilterBar from '../../vue/FilterBar.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');
// Registers the component and the `user` filter type, as in production.
await import('../../modules/user/vue/index.js');

const vueModule = globalThis.humhub.modules.vue;

// Items of `GET /api/v2/user` (UserSerializer::list(), shortened to what the control reads).
const person = (id, displayName) => ({
    id,
    guid: `guid-${id}`,
    displayName,
    url: `/u/${id}`,
    imageUrl: `/img/${id}.jpg`,
    contentContainerId: 100 + id,
    title: null,
    tags: [],
});
const people = [person(2, 'Peter Tester'), person(3, 'Sara Tester'), person(8, 'Sam User')];

const filter = { key: 'userId', type: 'user', label: 'Author', placeholder: 'Any author' };

const mounted = [];
const mountControl = (props = {}) => {
    const wrapper = mount(UserFilterControl, {
        props: { filter, modelValue: '', inputId: 'filter-userId', ...props },
        attachTo: document.body,
    });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const optionTexts = (wrapper) => wrapper.findAll('[role="option"]').map((option) => option.find('.c-user-filter__name').text());
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
const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);
const params = (url) => Object.fromEntries(new URL(url, 'http://localhost').searchParams);

// An answer per request, resolved when the test says so.
const deferredClient = () => {
    const pending = [];
    globalThis.humhubStubs.client.get = vi.fn((url) => new Promise((resolve, reject) => pending.push({ url, resolve, reject })));
    return pending;
};

describe('UserFilterControl', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.client.get = vi.fn((url) => {
            const { q, ids } = params(url);
            const results = ids
                ? people.filter((entry) => String(entry.id) === ids)
                : people.filter((entry) => entry.displayName.toLowerCase().includes(String(q).toLowerCase()));
            return Promise.resolve({ results, total: results.length });
        });
    });

    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
        vi.useRealTimers();
    });

    it('is registered as the `user` filter type', () => {
        expect(vueModule.getFilterType('user')).toBe('UserFilterControl');
        expect(vueModule.isRegistered('UserFilterControl')).toBe(true);
    });

    it('is a search field and a closed combobox without a value', () => {
        const wrapper = mountControl();
        const field = input(wrapper);

        expect(wrapper.find('.ti-user').exists()).toBe(true);
        expect(field.attributes('id')).toBe('filter-userId');
        expect(field.attributes('placeholder')).toBe('Any author');
        expect(field.attributes('aria-label')).toBe('Author');
        expect(field.attributes('aria-expanded')).toBe('false');
        expect(field.attributes('aria-autocomplete')).toBe('list');
        expect(field.attributes('aria-controls')).toBe(wrapper.find('[role="listbox"]').attributes('id'));
        expect(wrapper.find('.c-picker__chip').exists()).toBe(false);
    });

    it('takes the label as placeholder without one', () => {
        const wrapper = mountControl({ filter: { key: 'userId', type: 'user', label: 'Author' } });

        expect(input(wrapper).attributes('placeholder')).toBe('Author');
    });

    it('suggests the users matching the typed text after the debounce', async () => {
        const wrapper = mountControl();

        await type(wrapper, 'tester');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        const url = globalThis.humhubStubs.client.get.mock.calls[0][0];
        expect(new URL(url, 'http://localhost').pathname).toBe('/api/v2/user');
        expect(params(url)).toEqual({ purpose: 'picker', q: 'tester', pageSize: '8' });
        expect(input(wrapper).attributes('aria-expanded')).toBe('true');
        expect(optionTexts(wrapper)).toEqual(['Peter Tester', 'Sara Tester']);
        const avatar = wrapper.find('[role="option"] img');
        expect(avatar.attributes('src')).toBe('/img/2.jpg');
        expect(avatar.attributes('style')).toContain('width: 24px');
    });

    it('sends no request for an empty text', async () => {
        const wrapper = mountControl();

        await typeAndWait(wrapper, '   ');

        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
    });

    it('drops an answer to an older text', async () => {
        const pending = deferredClient();
        const wrapper = mountControl();

        await typeAndWait(wrapper, 'sa');
        await typeAndWait(wrapper, 'sara');
        expect(pending).toHaveLength(2);

        pending[1].resolve({ results: [people[1]] });
        await flushPromises();
        pending[0].resolve({ results: [people[1], people[2]] });
        await flushPromises();

        expect(optionTexts(wrapper)).toEqual(['Sara Tester']);
    });

    it('says when nobody matches', async () => {
        const wrapper = mountControl();

        await typeAndWait(wrapper, 'nobody');

        expect(optionTexts(wrapper)).toEqual([]);
        expect(wrapper.find('.c-select__feedback').text()).toBe('No results found!');
        expect(wrapper.find('[role="status"]').text()).toBe('No results found!');
    });

    it('emits the id of the chosen user and shows them as a chip with avatar and name', async () => {
        const wrapper = mountControl();

        await typeAndWait(wrapper, 'sara');
        await wrapper.find('[role="option"]').trigger('click');

        expect(emitted(wrapper)).toEqual(['3']);
        await wrapper.setProps({ modelValue: '3' });

        const chip = wrapper.find('.c-picker__chip');
        expect(chip.find('.c-picker__chip-label').text()).toBe('Sara Tester');
        expect(chip.find('img').attributes('src')).toBe('/img/3.jpg');
        expect(input(wrapper).exists()).toBe(false);
        await flushPromises();
        expect(document.activeElement).toBe(chip.find('.c-picker__chip-remove').element, 'the focus stays in the control');
        expect(chip.find('.c-picker__chip-remove').attributes('id')).toBe('filter-userId');
        // Known from the suggestions: nothing to resolve.
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
    });

    it('clears the value with the chip\'s X and returns the focus to the input', async () => {
        const wrapper = mountControl();
        await typeAndWait(wrapper, 'sara');
        await wrapper.find('[role="option"]').trigger('click');
        await wrapper.setProps({ modelValue: '3' });

        const remove = wrapper.find('.c-picker__chip-remove');
        expect(remove.attributes('aria-label')).toBe('Remove Sara Tester');
        await remove.trigger('click');
        expect(emitted(wrapper).at(-1)).toBe('');

        await wrapper.setProps({ modelValue: '' });
        await flushPromises();
        expect(document.activeElement).toBe(input(wrapper).element);
        expect(input(wrapper).element.value).toBe('');
    });

    it('resolves a value it does not know by its id, disabled meanwhile', async () => {
        const pending = deferredClient();
        const wrapper = mountControl({ modelValue: '8' });

        expect(pending).toHaveLength(1);
        expect(params(pending[0].url)).toEqual({ purpose: 'picker', ids: '8' });
        expect(wrapper.classes()).toContain('is-loading');
        expect(input(wrapper).attributes('disabled')).toBeDefined();
        expect(input(wrapper).attributes('aria-busy')).toBe('true');
        expect(wrapper.find('.c-select__spinner').exists()).toBe(true);

        pending[0].resolve({ results: [people[2]] });
        await flushPromises();

        expect(wrapper.classes()).not.toContain('is-loading');
        expect(wrapper.find('.c-picker__chip-label').text()).toBe('Sam User');
        expect(emitted(wrapper)).toEqual([]);
    });

    it('drops a value that is no user id at once, without a request', async () => {
        for (const value of ['abc', '0', '-3', '1.5', '07']) {
            const wrapper = mountControl({ modelValue: value });
            await flushPromises();

            expect(emitted(wrapper)).toEqual(['']);
            expect(wrapper.classes()).not.toContain('is-loading');
        }
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('activates the first of newer suggestions while open', async () => {
        const wrapper = mountControl();
        await typeAndWait(wrapper, 'tester');
        await key(wrapper, 'ArrowDown');
        expect(wrapper.findAll('[role="option"]')[1].attributes('aria-selected')).toBe('true');

        await typeAndWait(wrapper, 'sa');

        expect(optionTexts(wrapper)).toEqual(['Sara Tester', 'Sam User']);
        const options = wrapper.findAll('[role="option"]');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(options[0].attributes('id'));
        expect(options.map((option) => option.attributes('aria-selected'))).toEqual(['true', 'false']);
    });

    it('keeps the filter\'s name on the chip for screen readers', async () => {
        const wrapper = mountControl({ modelValue: '8' });
        await flushPromises();

        const chip = wrapper.find('.c-picker__chip');
        expect(chip.attributes('role')).toBe('group');
        expect(chip.attributes('aria-label')).toBe('Author');
    });

    it('drops a value naming nobody it may see', async () => {
        const wrapper = mountControl({ modelValue: '5' });
        await flushPromises();

        expect(emitted(wrapper)).toEqual(['']);
    });

    it('stays usable when a request fails', async () => {
        const pending = deferredClient();
        const wrapper = mountControl({ modelValue: '8' });

        pending[0].reject({ status: 500 });
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
        expect(wrapper.classes()).not.toContain('is-loading');
        // The value stays, shown as it is, and can be removed.
        expect(wrapper.find('.c-picker__chip-label').text()).toBe('8');
        await wrapper.find('.c-picker__chip-remove').trigger('click');
        expect(emitted(wrapper)).toEqual(['']);

        await wrapper.setProps({ modelValue: '' });
        await typeAndWait(wrapper, 'sa');
        pending[1].reject({ status: 500 });
        await flushPromises();

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(2);
        expect(input(wrapper).attributes('disabled')).toBeUndefined();
        expect(optionTexts(wrapper)).toEqual([]);
    });

    it('moves through the suggestions with the keyboard and chooses with Enter', async () => {
        const wrapper = mountControl();
        await typeAndWait(wrapper, 'tester');
        const active = () => input(wrapper).attributes('aria-activedescendant');
        const options = () => wrapper.findAll('[role="option"]');

        expect(active()).toBe(options()[0].attributes('id'));
        await key(wrapper, 'ArrowDown');
        expect(active()).toBe(options()[1].attributes('id'));
        expect(options()[1].classes()).toContain('is-active');
        await key(wrapper, 'ArrowDown');
        expect(active()).toBe(options()[1].attributes('id'), 'stays at the last');
        await key(wrapper, 'Home');
        expect(active()).toBe(options()[0].attributes('id'));
        await key(wrapper, 'End');
        expect(active()).toBe(options()[1].attributes('id'));
        await key(wrapper, 'ArrowUp');
        expect(active()).toBe(options()[0].attributes('id'));

        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual(['2']);
    });

    it('closes with Escape, and drops the text with a second one', async () => {
        const wrapper = mountControl();
        await typeAndWait(wrapper, 'sara');

        await key(wrapper, 'Escape');
        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
        expect(input(wrapper).element.value).toBe('sara');

        await key(wrapper, 'ArrowDown');
        expect(input(wrapper).attributes('aria-expanded')).toBe('true', 'ArrowDown opens again');

        await key(wrapper, 'Escape');
        await key(wrapper, 'Escape');
        expect(input(wrapper).element.value).toBe('');
        expect(emitted(wrapper)).toEqual([]);
    });

    it('closes with Tab', async () => {
        const wrapper = mountControl();
        await typeAndWait(wrapper, 'sara');

        await key(wrapper, 'Tab');

        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
    });

    it('is the control of a `user` definition in a filter bar, which applies the chosen id', async () => {
        window.history.replaceState(null, '', '/files');
        const wrapper = mount(FilterBar, {
            props: { filters: [{ key: 'userId', type: 'user', label: 'Author' }], modelValue: {} },
            global: { components: { UserFilterControl } },
            attachTo: document.body,
        });
        mounted.push(wrapper);

        const field = wrapper.find('.c-filter-bar__item--user input[role="combobox"]');
        expect(field.attributes('id')).toBe('filter-userId');

        field.element.value = 'peter';
        await field.trigger('input');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        await wrapper.find('[role="option"]').trigger('click');
        await flushPromises();

        expect(emitted(wrapper).at(-1)).toEqual({ userId: '2' });
        expect(window.location.search).toBe('?userId=2');
        expect(wrapper.find('.c-picker__chip-label').text()).toBe('Peter Tester');
    });
});
