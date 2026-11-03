import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import PickerInput, { SEARCH_DEBOUNCE_MS } from '../../vue/PickerInput.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mounted = [];
const mountPicker = (props = {}) => {
    const wrapper = mount(PickerInput, { props: { label: 'People', multiple: true, url: '/search', ...props }, attachTo: document.body });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const type = async (wrapper, text) => {
    input(wrapper).element.value = text;
    await input(wrapper).trigger('input');
};
const optionTexts = (wrapper) => wrapper.findAll('[role="option"] .picker-option-label').map((node) => node.text());
const feedback = (wrapper) => wrapper.find('.select-menu-feedback').text();
const people = [{ id: 1, text: 'Anna', subText: 'PM', image: '/a.jpg' }, { id: 2, text: 'Andreas' }, { id: 3, text: 'Bob' }];

describe('PickerInput — remote sources', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        globalThis.humhubStubs.client.get = vi.fn((url) => {
            const keyword = new URL(url, 'http://localhost').searchParams.get('keyword') || '';
            return Promise.resolve(people.filter((person) => person.text.toLowerCase().includes(keyword.toLowerCase())));
        });
    });

    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
        vi.useRealTimers();
    });

    it('asks for minInput characters before searching', async () => {
        const wrapper = mountPicker({ minInput: 3, messages: { inputTooShort: 'At least {n}.' } });
        await type(wrapper, 'an');
        expect(feedback(wrapper)).toBe('At least 3.');
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('searches debounced and lists the answer', async () => {
        const wrapper = mountPicker({ minInput: 2 });
        await type(wrapper, 'an');
        expect(feedback(wrapper)).toBe('Loading...');

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/search?keyword=an');
        expect(optionTexts(wrapper)).toEqual(['Anna', 'Andreas']);
    });

    it('chooses the existing item when Enter on the new item comes before the answer', async () => {
        const wrapper = mountPicker({ minInput: 2, allowNew: true, newPrefix: '_add:' });
        await type(wrapper, 'Bob');
        expect(wrapper.find('.is-new').exists()).toBe(true);
        await input(wrapper).trigger('keydown', { key: 'Enter' });
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();

        expect(wrapper.emitted('update:modelValue').map((args) => args[0])).toEqual([['3']]);
    });

    it('chooses the new item once the answer holds none of that name', async () => {
        const wrapper = mountPicker({ minInput: 2, allowNew: true, newPrefix: '_add:' });
        await type(wrapper, 'Roadmap');
        await input(wrapper).trigger('keydown', { key: 'Enter' });

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();

        expect(wrapper.emitted('update:modelValue').map((args) => args[0])).toEqual([['_add:Roadmap']]);
    });

    it('chooses the new item when the search fails, on a waiting Enter and on a later one', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('offline')));
        const wrapper = mountPicker({ minInput: 2, allowNew: true, newPrefix: '_add:' });
        await type(wrapper, 'Roadmap');
        await input(wrapper).trigger('keydown', { key: 'Enter' });

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(wrapper.emitted('update:modelValue').map((args) => args[0])).toEqual([['_add:Roadmap']]);

        await type(wrapper, 'Docs');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        await input(wrapper).trigger('keydown', { key: 'Enter' });
        expect(wrapper.emitted('update:modelValue').map((args) => args[0]).at(-1)).toEqual(['_add:Roadmap', '_add:Docs']);
    });

    it('drops an answer to an older term', async () => {
        const pending = [];
        globalThis.humhubStubs.client.get = vi.fn(() => new Promise((resolve) => pending.push(resolve)));
        const wrapper = mountPicker({ minInput: 1 });

        await type(wrapper, 'a');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await type(wrapper, 'b');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);

        pending[1]([{ id: 3, text: 'Bob' }]);
        pending[0]([{ id: 1, text: 'Anna' }]);
        await flushPromises();

        expect(optionTexts(wrapper)).toEqual(['Bob']);
    });

    it('lists at most maxResults items, 8 by default', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(Array.from({ length: 12 }, (unused, i) => ({ id: i + 1, text: `Person ${i + 1}` }))));
        const wrapper = mountPicker({ minInput: 1 });
        await type(wrapper, 'p');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(optionTexts(wrapper)).toHaveLength(8);
    });

    it('shows the default results before anything is typed', async () => {
        const wrapper = mountPicker({ minInput: 3, defaultResults: [{ id: 9, text: 'Default Dora' }] });
        await input(wrapper).trigger('keydown', { key: 'ArrowDown' });
        expect(optionTexts(wrapper)).toEqual(['Default Dora']);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('suggests the first `suggested` items of an empty search', async () => {
        const wrapper = mountPicker({ minInput: 3, suggested: 2 });
        await input(wrapper).trigger('focus');
        await flushPromises();
        await input(wrapper).trigger('keydown', { key: 'ArrowDown' });
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/search?keyword=');
        expect(optionTexts(wrapper)).toEqual(['Anna', 'Andreas']);
    });

    it('says when nothing matched and when the request failed', async () => {
        const wrapper = mountPicker({ minInput: 1 });
        await type(wrapper, 'zzz');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(feedback(wrapper)).toBe('No results found!');

        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('down')));
        await type(wrapper, 'yyy');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(feedback(wrapper)).toBe('The results could not be loaded.');
    });

    it('keeps sub text and image of a chosen remote item', async () => {
        const wrapper = mountPicker({ minInput: 1 });
        await type(wrapper, 'ann');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        await input(wrapper).trigger('keydown', { key: 'Enter' });

        expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual(['1']);
        expect(wrapper.find('.picker-chip img').attributes('src')).toBe('/a.jpg');
    });

    it('uses a named source with its item key and parameters', async () => {
        const search = vi.fn(() => Promise.resolve([{ id: 'g5', text: 'Named' }]));
        globalThis.humhub.modules.vue.registerPickerSource('test.input.named', ({ itemKey, params }) => {
            expect(itemKey).toBe('guid');
            expect(params).toEqual({ spaceId: 4 });
            return { search };
        });
        const wrapper = mountPicker({ url: null, source: 'test.input.named', itemKey: 'guid', sourceParams: { spaceId: 4 }, minInput: 1 });
        await type(wrapper, 'n');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(search).toHaveBeenCalledWith('n');
        expect(optionTexts(wrapper)).toEqual(['Named']);
    });

    it('lists the suggestions again after a closed search, not the answer to the old text', async () => {
        const wrapper = mountPicker({ minInput: 1, suggested: 2 });
        await input(wrapper).trigger('focus');
        await flushPromises();
        await type(wrapper, 'ann');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(optionTexts(wrapper)).toEqual(['Anna']);

        await input(wrapper).trigger('keydown', { key: 'Tab' });
        await input(wrapper).trigger('focus');
        await flushPromises();
        await input(wrapper).trigger('keydown', { key: 'ArrowDown' });
        expect(optionTexts(wrapper)).toEqual(['Anna', 'Andreas']);
    });

    it('lists no answer to the old text while a new one loads', async () => {
        const wrapper = mountPicker({ minInput: 1 });
        await type(wrapper, 'an');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(optionTexts(wrapper)).toEqual(['Anna', 'Andreas']);

        await type(wrapper, 'bo');
        expect(optionTexts(wrapper)).toEqual([]);
        expect(feedback(wrapper)).toBe('Loading...');
        await input(wrapper).trigger('keydown', { key: 'Enter' });
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();

        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(optionTexts(wrapper)).toEqual(['Bob']);
    });

    it('counts maxResults over the items not chosen yet', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve(Array.from({ length: 12 }, (unused, i) => ({ id: i + 1, text: `Person ${i + 1}` }))));
        const wrapper = mountPicker({ minInput: 1, maxResults: 3, modelValue: ['1'] });
        await type(wrapper, 'p');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        expect(optionTexts(wrapper)).toEqual(['Person 2', 'Person 3', 'Person 4']);
    });
});
