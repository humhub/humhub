import { afterEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import PickerInput, { SEARCH_DEBOUNCE_MS } from '../../vue/PickerInput.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const items = [{ id: '1', text: 'One' }, { id: '2', text: 'Two' }, { id: '3', text: 'Three' }];
const mounted = [];
const mountPicker = (props) => {
    const wrapper = mount(PickerInput, { props: { label: 'Items', multiple: true, options: items, ...props }, attachTo: document.body });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const key = (wrapper, name) => input(wrapper).trigger('keydown', { key: name });
const type = async (wrapper, text) => {
    input(wrapper).element.value = text;
    await input(wrapper).trigger('input');
};
const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);

describe('PickerInput — modes', () => {
    afterEach(() => mounted.splice(0).forEach((wrapper) => wrapper.unmount()));

    it('lists the chosen items as rows below the field when listed', () => {
        const wrapper = mountPicker({ listed: true, modelValue: ['1', '2'] });
        expect(wrapper.find('.picker-chip').exists()).toBe(false);
        expect(wrapper.findAll('.picker-list-item-text').map((node) => node.text())).toEqual(['One', 'Two']);
        expect(wrapper.find('.picker-list-item').attributes('data-value')).toBe('1');
    });

    it('shows the sub text in a listed row', () => {
        const wrapper = mountPicker({ listed: true, options: [{ id: 'u', text: 'Ada', subText: 'Engineer' }], modelValue: ['u'] });
        expect(wrapper.find('.picker-list-item-label').text()).toBe('Ada');
        expect(wrapper.find('.picker-list-item-subtext').text()).toBe('Engineer');
    });

    it('removes a listed row by its button', async () => {
        const wrapper = mountPicker({ listed: true, modelValue: ['1', '2'] });
        await wrapper.find('.picker-list-item-remove').trigger('click');
        expect(emitted(wrapper)).toEqual([['2']]);
    });

    it('keeps locked items first, without remove button, with the locked message', () => {
        const wrapper = mountPicker({ modelValue: ['2', '1'], lockedIds: ['1'], messages: { locked: 'Fixed.' } });
        const chips = wrapper.findAll('.picker-chip');
        expect(chips.map((chip) => chip.attributes('data-value'))).toEqual(['1', '2']);
        expect(chips[0].find('.picker-chip-remove').exists()).toBe(false);
        expect(chips[0].attributes('title')).toBe('Fixed.');
    });

    it('does not remove a locked item by Backspace', async () => {
        const wrapper = mountPicker({ modelValue: ['1'], lockedIds: ['1'] });
        await key(wrapper, 'Backspace');
        expect(emitted(wrapper)).toEqual([]);
    });

    it('lists disabled items dimmed and does not choose them', async () => {
        const wrapper = mountPicker({ disabledIds: ['2'] });
        await key(wrapper, 'ArrowDown');
        const disabled = wrapper.find('[aria-disabled="true"]');
        expect(disabled.text()).toBe('Two');
        await disabled.trigger('click');
        expect(emitted(wrapper)).toEqual([]);
    });

    it('offers a new item as _add:<text> in a picker', async () => {
        vi.useFakeTimers();
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve([]));
        const wrapper = mountPicker({ options: [], url: '/topics', allowNew: true, newPrefix: '_add:', minInput: 2, messages: { add: 'Add "{text}"' } });
        await type(wrapper, 'Roadmap');
        expect(wrapper.find('.is-new .picker-option-label').text()).toBe('Add "Roadmap"');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        vi.useRealTimers();
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual([['_add:Roadmap']]);
        expect(wrapper.find('.picker-chip-label').text()).toBe('Roadmap');
    });

    it('offers the plain text as new value in an enhanced select', async () => {
        const wrapper = mountPicker({ multiple: false, search: 0, allowNew: true });
        await type(wrapper, 'Four');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual(['Four']);
    });

    it('shows a chosen new item with the image of the source, none without', async () => {
        globalThis.humhub.modules.vue.registerPickerSource('test.modes.newImage', () => ({ search: () => Promise.resolve([]), newImage: { type: 'icon', name: 'star' } }));
        const withImage = mountPicker({ options: [], source: 'test.modes.newImage', allowNew: true, minInput: 1 });
        await type(withImage, 'Roadmap');
        await flushPromises();
        await new Promise((resolve) => setTimeout(resolve, SEARCH_DEBOUNCE_MS + 20));
        await flushPromises();
        await key(withImage, 'Enter');
        expect(withImage.find('.picker-chip .picker-chip-image').classes()).toContain('ti-star');

        const without = mountPicker({ allowNew: true });
        await type(without, 'Four');
        await key(without, 'Enter');
        expect(without.find('.picker-chip').text()).toContain('Four');
        expect(without.find('.picker-chip .picker-chip-image').exists()).toBe(false);
    });

    it('offers the new item after the matches, the first match active', async () => {
        const wrapper = mountPicker({ allowNew: true });
        await type(wrapper, 'T');
        const labels = wrapper.findAll('.picker-option-label').map((label) => label.text());
        expect(labels[labels.length - 1]).toContain('T');
        expect(wrapper.findAll('.picker-option').at(-1).classes()).toContain('is-new');
        expect(wrapper.find('.picker-option.is-focused').text()).toBe('Two');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual([['2']]);
    });

    it('offers no new item for a text that is there already', async () => {
        const wrapper = mountPicker({ allowNew: true });
        await type(wrapper, 'two');
        expect(wrapper.find('.is-new').exists()).toBe(false);
    });

    it('disables the whole control', () => {
        const wrapper = mountPicker({ disabled: true, modelValue: ['1'] });
        expect(input(wrapper).attributes('disabled')).toBeDefined();
        expect(wrapper.find('.picker-chip-remove').exists()).toBe(false);
        expect(wrapper.find('.picker').classes()).toContain('is-disabled');
    });

    it('marks the field invalid', () => {
        const wrapper = mountPicker({ invalid: true });
        expect(wrapper.find('.picker-field').classes()).toContain('is-invalid');
        expect(input(wrapper).attributes('aria-invalid')).toBe('true');
    });

    it('limits the input to maxInput characters', () => {
        expect(input(mountPicker({ maxInput: 20 })).attributes('maxlength')).toBe('20');
    });
});
