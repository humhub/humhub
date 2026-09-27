import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import FilterPicker, { SEARCH_DEBOUNCE_MS } from '../../vue/FilterPicker.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const staticOptions = [
    { value: '', label: 'All' },
    { value: 'php', label: 'PHP' },
    { value: 'js', label: 'JavaScript' },
    { value: 'go', label: 'Go' },
];

const mounted = [];
const mountPicker = (props = {}) => {
    const wrapper = mount(FilterPicker, {
        props: { label: 'Tags', ...props },
        attachTo: document.body,
    });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const optionTexts = (wrapper) => wrapper.findAll('[role="option"]').map((option) => option.find('.c-picker__option-label').text());
const key = (wrapper, name) => input(wrapper).trigger('keydown', { key: name });
const type = async (wrapper, text) => {
    input(wrapper).element.value = text;
    await input(wrapper).trigger('input');
};
const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);

// An answer per requested URL, resolved when the test says so.
const deferredClient = () => {
    const pending = [];
    globalThis.humhubStubs.client.get = vi.fn((url) => new Promise((resolve, reject) => pending.push({ url, resolve, reject })));
    return pending;
};

describe('FilterPicker', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.get = vi.fn((url) => {
            const q = new URL(url, 'http://localhost').searchParams.get('q');
            const all = [{ id: 'Berlin', name: 'Berlin', count: 3 }, { id: 'Hamburg', name: 'Hamburg', count: 2 }, { id: 'Bern', name: 'Bern', count: 1 }];
            return Promise.resolve({ results: q ? all.filter((result) => result.name.toLowerCase().includes(q.toLowerCase())) : all });
        });
    });

    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
        vi.useRealTimers();
    });

    it('is a combobox with a listbox, closed at first, the label as placeholder', () => {
        const wrapper = mountPicker({ options: staticOptions, id: 'filter-tag' });
        const field = input(wrapper);

        expect(field.attributes('id')).toBe('filter-tag');
        expect(field.attributes('aria-expanded')).toBe('false');
        expect(field.attributes('aria-autocomplete')).toBe('list');
        expect(field.attributes('aria-label')).toBe('Tags');
        expect(field.attributes('placeholder')).toBe('Tags');
        expect(field.attributes('aria-controls')).toBe(wrapper.find('[role="listbox"]').attributes('id'));
        expect(wrapper.classes()).not.toContain('is-open');
        expect(wrapper.find('.c-select__clear').attributes('disabled')).toBeDefined();
    });

    it('opens on a click with the static options, without the empty one', async () => {
        const wrapper = mountPicker({ options: staticOptions });

        await wrapper.find('.c-picker__field').trigger('click');

        expect(wrapper.classes()).toContain('is-open');
        expect(input(wrapper).attributes('aria-expanded')).toBe('true');
        expect(optionTexts(wrapper)).toEqual(['PHP', 'JavaScript', 'Go']);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('filters static options in the browser without an optionsUrl', async () => {
        const wrapper = mountPicker({ options: staticOptions });

        await type(wrapper, 'java');
        expect(optionTexts(wrapper)).toEqual(['JavaScript']);

        await type(wrapper, 'rust');
        expect(optionTexts(wrapper)).toEqual([]);
        expect(wrapper.find('.c-select__feedback').text()).toBe('No results found!');
        expect(wrapper.find('[role="status"]').text()).toBe('No results found!');
    });

    it('loads the first page of the optionsUrl on focus and shows it on opening', async () => {
        const wrapper = mountPicker({ optionsUrl: '/api/v2/user/tags' });

        await input(wrapper).trigger('focus');
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/user/tags');
        await flushPromises();

        await wrapper.find('.c-picker__field').trigger('click');
        expect(optionTexts(wrapper)).toEqual(['Berlin', 'Hamburg', 'Bern']);
        expect(wrapper.findAll('.c-picker__option-count').map((count) => count.text())).toEqual(['3', '2', '1']);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
    });

    it('shows the loading state until the first page is there', async () => {
        const pending = deferredClient();
        const wrapper = mountPicker({ optionsUrl: '/api/v2/user/tags' });

        await wrapper.find('.c-picker__field').trigger('click');
        expect(wrapper.classes()).toContain('is-loading');
        expect(wrapper.find('.c-select__spinner').exists()).toBe(true);
        expect(wrapper.find('[role="listbox"]').attributes('aria-busy')).toBe('true');
        expect(wrapper.find('.c-select__feedback').text()).toBe('Loading...');

        pending[0].resolve({ results: [] });
        await flushPromises();
        expect(wrapper.classes()).not.toContain('is-loading');
        expect(wrapper.find('.c-select__feedback').text()).toBe('No options');
    });

    it('searches the optionsUrl with q while typing, debounced', async () => {
        vi.useFakeTimers();
        const wrapper = mountPicker({ optionsUrl: '/api/v2/user/field-values?field=city' });

        await type(wrapper, 'Be');
        await type(wrapper, 'Ber');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
        expect(wrapper.classes()).toContain('is-loading');

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/user/field-values?field=city&q=Ber');
        expect(optionTexts(wrapper)).toEqual(['Berlin', 'Bern']);
        expect(wrapper.classes()).not.toContain('is-loading');
    });

    it('drops an answer to an older text', async () => {
        vi.useFakeTimers();
        const pending = deferredClient();
        const wrapper = mountPicker({ optionsUrl: '/api/v2/user/tags' });

        await type(wrapper, 'ha');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await type(wrapper, 'be');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        expect(pending.map((request) => request.url)).toEqual(['/api/v2/user/tags?q=ha', '/api/v2/user/tags?q=be']);

        pending[1].resolve({ results: [{ id: 'Bern', name: 'Bern' }] });
        await flushPromises();
        pending[0].resolve({ results: [{ id: 'Hamburg', name: 'Hamburg' }] });
        await flushPromises();

        expect(optionTexts(wrapper)).toEqual(['Bern']);
        expect(wrapper.classes()).not.toContain('is-loading');
    });

    it('answers a text typed before from what it loaded, and reloads after reloadKey changed', async () => {
        const wrapper = mountPicker({ optionsUrl: '/api/v2/user/tags' });

        await wrapper.find('.c-picker__field').trigger('click');
        await flushPromises();
        await type(wrapper, 'x');
        await type(wrapper, '');
        await flushPromises();
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(1);
        expect(optionTexts(wrapper)).toEqual(['Berlin', 'Hamburg', 'Bern']);

        await wrapper.setProps({ reloadKey: 1 });
        await flushPromises();
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledTimes(2);
    });

    it('in single mode chooses one value, shows its label and closes', async () => {
        const wrapper = mountPicker({ options: staticOptions });

        await type(wrapper, 'go');
        await wrapper.findAll('[role="option"]')[0].trigger('click');

        expect(emitted(wrapper)).toEqual(['go']);
        expect(wrapper.classes()).not.toContain('is-open');

        await wrapper.setProps({ modelValue: 'go' });
        expect(input(wrapper).element.value).toBe('Go');
        expect(wrapper.classes()).toContain('has-selection');
        expect(wrapper.find('.c-picker__chip').exists()).toBe(false);

        await wrapper.find('.c-picker__field').trigger('click');
        expect(wrapper.find('[aria-selected="true"]').text()).toBe('Go');
    });

    it('in single mode shows a value it has no label for as is, until an answer carries it', async () => {
        const wrapper = mountPicker({ modelValue: 'Hamburg', optionsUrl: '/api/v2/user/tags', options: [] });
        expect(input(wrapper).element.value).toBe('Hamburg');

        const labelled = mountPicker({ modelValue: 'DE', optionsUrl: '/api/v2/user/field-values?field=country' });
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [{ id: 'DE', name: 'Germany' }] }));
        expect(input(labelled).element.value).toBe('DE');
        await input(labelled).trigger('focus');
        await flushPromises();
        expect(input(labelled).element.value).toBe('Germany');
    });

    it('in multiple mode adds chips, stays open and leaves chosen values out of the suggestions', async () => {
        const wrapper = mountPicker({ multiple: true, modelValue: [], options: staticOptions });

        await wrapper.find('.c-picker__field').trigger('click');
        await wrapper.findAll('[role="option"]')[0].trigger('click');
        expect(emitted(wrapper)).toEqual([['php']]);
        await wrapper.setProps({ modelValue: ['php'] });

        expect(wrapper.classes()).toContain('is-open');
        expect(wrapper.find('[role="listbox"]').attributes('aria-multiselectable')).toBe('true');
        expect(wrapper.findAll('.c-picker__chip-label').map((chip) => chip.text())).toEqual(['PHP']);
        expect(optionTexts(wrapper)).toEqual(['JavaScript', 'Go']);
        // The chips replace the placeholder.
        expect(input(wrapper).attributes('placeholder')).toBe('');

        await type(wrapper, 'ja');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper).at(-1)).toEqual(['php', 'js']);
        expect(input(wrapper).element.value).toBe('');
    });

    it('removes a chip by its button and the last one by Backspace in an empty input', async () => {
        const wrapper = mountPicker({ multiple: true, modelValue: ['php', 'js', 'Rust'], options: staticOptions });

        // A value none of the options has shows as is.
        expect(wrapper.findAll('.c-picker__chip-label').map((chip) => chip.text())).toEqual(['PHP', 'JavaScript', 'Rust']);

        const remove = wrapper.findAll('.c-picker__chip-remove')[1];
        expect(remove.attributes('aria-label')).toBe('Remove JavaScript');
        await remove.trigger('click');
        expect(emitted(wrapper)).toEqual([['php', 'Rust']]);
        expect(document.activeElement).toBe(input(wrapper).element);

        await key(wrapper, 'Backspace');
        expect(emitted(wrapper).at(-1)).toEqual(['php', 'js']);

        await type(wrapper, 'a');
        await key(wrapper, 'Backspace');
        expect(emitted(wrapper)).toHaveLength(2);
    });

    it('moves the active option with the arrow keys, Home and End, and chooses it with Enter', async () => {
        const wrapper = mountPicker({ options: staticOptions });

        await key(wrapper, 'ArrowDown');
        expect(wrapper.classes()).toContain('is-open');
        const options = () => wrapper.findAll('[role="option"]');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(options()[0].attributes('id'));

        await key(wrapper, 'ArrowDown');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(options()[1].attributes('id'));
        await key(wrapper, 'End');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(options()[2].attributes('id'));
        await key(wrapper, 'ArrowDown');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(options()[2].attributes('id'));
        await key(wrapper, 'Home');
        await key(wrapper, 'ArrowUp');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(options()[0].attributes('id'));

        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual(['php']);
    });

    it('opens on ArrowUp at the last option', async () => {
        const wrapper = mountPicker({ options: staticOptions });

        await key(wrapper, 'ArrowUp');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(wrapper.findAll('[role="option"]')[2].attributes('id'));
    });

    it('closes on Escape, then drops the typed text, and keeps Escape from a surrounding panel only while it handles it', async () => {
        const outer = vi.fn();
        document.body.addEventListener('keydown', outer);
        const wrapper = mountPicker({ options: staticOptions });

        await type(wrapper, 'ja');
        await key(wrapper, 'Escape');
        expect(wrapper.classes()).not.toContain('is-open');
        expect(input(wrapper).element.value).toBe('ja');

        await key(wrapper, 'Escape');
        expect(input(wrapper).element.value).toBe('');
        expect(outer).not.toHaveBeenCalled();

        await key(wrapper, 'Escape');
        expect(outer).toHaveBeenCalledTimes(1);
        document.body.removeEventListener('keydown', outer);
    });

    it('closes on Tab and on a click outside', async () => {
        const wrapper = mountPicker({ options: staticOptions });

        await wrapper.find('.c-picker__field').trigger('click');
        await key(wrapper, 'Tab');
        expect(wrapper.classes()).not.toContain('is-open');

        await wrapper.find('.c-picker__field').trigger('click');
        document.body.dispatchEvent(new Event('pointerdown', { bubbles: true }));
        await flushPromises();
        expect(wrapper.classes()).not.toContain('is-open');
    });

    it('clears the value with the clear X', async () => {
        const single = mountPicker({ modelValue: 'php', options: staticOptions });
        const clear = single.find('.c-select__clear');
        expect(clear.attributes('disabled')).toBeUndefined();
        expect(clear.attributes('aria-label')).toBe('Clear selection');
        await clear.trigger('click');
        expect(emitted(single)).toEqual(['']);
        expect(document.activeElement).toBe(input(single).element);

        const multiple = mountPicker({ multiple: true, modelValue: ['php', 'go'], options: staticOptions });
        await multiple.find('.c-select__clear').trigger('click');
        expect(emitted(multiple)).toEqual([[]]);
    });

    it('does nothing while disabled', async () => {
        const wrapper = mountPicker({ options: staticOptions, disabled: true });

        await wrapper.find('.c-picker__field').trigger('click');
        expect(wrapper.classes()).not.toContain('is-open');
        expect(input(wrapper).attributes('disabled')).toBeDefined();
    });

    it('with allowCustom leads with a "Use" entry for the typed text, applied by Enter', async () => {
        const wrapper = mountPicker({ optionsUrl: '/api/v2/user/field-values?field=city', allowCustom: true });

        await type(wrapper, 'Ber');
        await new Promise((resolve) => setTimeout(resolve, SEARCH_DEBOUNCE_MS));
        await flushPromises();

        expect(optionTexts(wrapper)).toEqual(['Use “Ber”', 'Berlin', 'Bern']);
        expect(wrapper.findAll('[role="option"]')[0].classes()).toContain('is-active');

        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual(['Ber']);
        expect(wrapper.classes()).not.toContain('is-open');

        await wrapper.setProps({ modelValue: 'Ber' });
        expect(input(wrapper).element.value).toBe('Ber');
    });

    it('with allowCustom still chooses an active suggestion, and offers no entry for a suggested value', async () => {
        const wrapper = mountPicker({ options: staticOptions, allowCustom: true });

        await type(wrapper, 'Ja');
        expect(optionTexts(wrapper)).toEqual(['Use “Ja”', 'JavaScript']);
        await key(wrapper, 'ArrowDown');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual(['js']);

        await type(wrapper, 'php');
        expect(optionTexts(wrapper)).toEqual(['PHP'], 'the value is a suggestion already');

        await type(wrapper, 'Rust');
        await wrapper.findAll('[role="option"]')[0].trigger('click');
        expect(emitted(wrapper)).toEqual(['js', 'Rust']);
    });

    it('with allowCustom applies the typed text by Enter with the listbox closed', async () => {
        const wrapper = mountPicker({ options: staticOptions, allowCustom: true });

        await type(wrapper, 'Hamburg');
        await key(wrapper, 'Escape');
        expect(wrapper.classes()).not.toContain('is-open');
        await key(wrapper, 'Enter');

        expect(emitted(wrapper)).toEqual(['Hamburg']);
    });

    it('offers no "Use" entry without allowCustom or in multiple mode', async () => {
        const single = mountPicker({ options: staticOptions });
        await type(single, 'Rust');
        await key(single, 'Enter');
        expect(optionTexts(single)).toEqual([]);
        expect(emitted(single)).toEqual([]);

        const multiple = mountPicker({ options: staticOptions, multiple: true, allowCustom: true });
        await type(multiple, 'Rust');
        await key(multiple, 'Enter');
        expect(optionTexts(multiple)).toEqual([]);
        expect(emitted(multiple)).toEqual([]);
    });

    it('logs a failed load and stays usable with the static options', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('down')));
        const wrapper = mountPicker({ options: staticOptions, optionsUrl: '/api/v2/user/tags' });

        await wrapper.find('.c-picker__field').trigger('click');
        await flushPromises();

        expect(optionTexts(wrapper)).toEqual(['PHP', 'JavaScript', 'Go']);
        expect(wrapper.classes()).not.toContain('is-loading');
    });
});
