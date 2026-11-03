import { afterEach, describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import PickerInput, { RENDER_STEP } from '../../vue/PickerInput.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const fruits = [
    { id: 'a', text: 'Apple' },
    { id: 'b', text: 'Banana', image: '#ffcc00' },
    { id: 'c', text: 'Cherry', subText: 'Red' },
];

const mounted = [];
const mountPicker = (props = {}) => {
    const wrapper = mount(PickerInput, { props: { label: 'Fruits', multiple: true, options: fruits, ...props }, attachTo: document.body });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const key = (wrapper, name) => input(wrapper).trigger('keydown', { key: name });
const type = async (wrapper, text) => {
    input(wrapper).element.value = text;
    await input(wrapper).trigger('input');
};
const optionTexts = (wrapper) => wrapper.findAll('[role="option"] .picker-option-label').map((node) => node.text());
const chipTexts = (wrapper) => wrapper.findAll('.picker-chip-label').map((node) => node.text());
const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);

describe('PickerInput — multiple, static', () => {
    afterEach(() => mounted.splice(0).forEach((wrapper) => wrapper.unmount()));

    it('is a closed combobox named by its label', () => {
        const field = input(mountPicker());
        expect(field.attributes('aria-expanded')).toBe('false');
        expect(field.attributes('aria-label')).toBe('Fruits');
        expect(field.attributes('aria-autocomplete')).toBe('list');
    });

    it('lists every option on ArrowDown and filters by the typed text', async () => {
        const wrapper = mountPicker();
        await key(wrapper, 'ArrowDown');
        expect(optionTexts(wrapper)).toEqual(['Apple', 'Banana', 'Cherry']);

        await type(wrapper, 'an');
        expect(optionTexts(wrapper)).toEqual(['Banana']);
        expect(wrapper.find('mark').text()).toBe('an');
    });

    it('shows sub text and image of an option', async () => {
        const wrapper = mountPicker();
        await key(wrapper, 'ArrowDown');
        expect(wrapper.find('.picker-option-subtext').text()).toBe('Red');
        expect(wrapper.find('[role="option"]:nth-child(2) .picker-color').exists()).toBe(true);
    });

    it('chooses with Enter, shows a chip and offers the item no more', async () => {
        const wrapper = mountPicker();
        await type(wrapper, 'che');
        await key(wrapper, 'Enter');

        expect(emitted(wrapper)).toEqual([['c']]);
        expect(chipTexts(wrapper)).toEqual(['Cherry']);
        expect(wrapper.find('.picker-chip').attributes('data-value')).toBe('c');
        await key(wrapper, 'ArrowDown');
        expect(optionTexts(wrapper)).toEqual(['Apple', 'Banana']);
    });

    it('takes its value from modelValue and the labels from the options', async () => {
        const wrapper = mountPicker({ modelValue: ['b', 'a'] });
        expect(chipTexts(wrapper)).toEqual(['Banana', 'Apple']);

        await wrapper.setProps({ modelValue: ['a'] });
        expect(chipTexts(wrapper)).toEqual(['Apple']);
    });

    it('removes a chip by its button and the last one by Backspace', async () => {
        const wrapper = mountPicker({ modelValue: ['a', 'b'] });
        await wrapper.find('.picker-chip-remove').trigger('click');
        expect(emitted(wrapper).at(-1)).toEqual(['b']);

        await key(wrapper, 'Backspace');
        expect(emitted(wrapper).at(-1)).toEqual([]);
    });

    it('names the remove buttons', () => {
        const wrapper = mountPicker({ modelValue: ['a'] });
        expect(wrapper.find('.picker-chip-remove').attributes('aria-label')).toBe('Remove Apple');
    });

    it('stops offering at maxSelection and says so', async () => {
        const wrapper = mountPicker({ modelValue: ['a', 'b'], maxSelection: 2, messages: { maximumSelected: 'Only {n}.' } });
        await key(wrapper, 'ArrowDown');
        expect(optionTexts(wrapper)).toEqual([]);
        expect(wrapper.find('.select-menu-feedback').text()).toBe('Only 2.');
    });

    it('moves the active option with the arrow keys, Home and End', async () => {
        const wrapper = mountPicker();
        await key(wrapper, 'ArrowDown');
        expect(wrapper.find('.is-focused').text()).toContain('Apple');
        await key(wrapper, 'End');
        expect(wrapper.find('.is-focused').text()).toContain('Cherry');
        await key(wrapper, 'ArrowUp');
        expect(wrapper.find('.is-focused').text()).toContain('Banana');
        expect(input(wrapper).attributes('aria-activedescendant')).toBe(wrapper.find('.is-focused').attributes('id'));
    });

    it('renders a long list in steps: further rows on scroll and when the active one is beyond', async () => {
        const wrapper = mountPicker({ options: Array.from({ length: 450 }, (_, index) => ({ id: `o${index}`, text: `Option ${index}` })) });
        await key(wrapper, 'ArrowDown');
        expect(wrapper.findAll('[role="option"]')).toHaveLength(RENDER_STEP);

        const listbox = wrapper.find('[role="listbox"]').element;
        Object.defineProperties(listbox, { scrollHeight: { configurable: true, value: 1000 }, clientHeight: { configurable: true, value: 300 }, scrollTop: { configurable: true, writable: true, value: 650 } });
        await wrapper.find('[role="listbox"]').trigger('scroll');
        expect(wrapper.findAll('[role="option"]')).toHaveLength(RENDER_STEP * 2);

        await key(wrapper, 'End');
        expect(wrapper.findAll('[role="option"]')).toHaveLength(450);
        expect(wrapper.find('.is-focused').text()).toBe('Option 449');
    });

    it('has no spell check or autocorrection on its input', () => {
        const attributes = input(mountPicker()).attributes();
        expect(attributes.spellcheck).toBe('false');
        expect(attributes.autocapitalize).toBe('off');
    });

    it('jumps a page with PageDown and PageUp, within the list', async () => {
        const wrapper = mountPicker({ options: Array.from({ length: 25 }, (_, index) => ({ id: `o${index}`, text: `Option ${index}` })) });
        await key(wrapper, 'ArrowDown');
        await key(wrapper, 'PageDown');
        expect(wrapper.find('.is-focused').text()).toBe('Option 10');
        await key(wrapper, 'PageDown');
        await key(wrapper, 'PageDown');
        expect(wrapper.find('.is-focused').text()).toBe('Option 24');
        await key(wrapper, 'PageUp');
        expect(wrapper.find('.is-focused').text()).toBe('Option 14');
        await key(wrapper, 'PageUp');
        await key(wrapper, 'PageUp');
        expect(wrapper.find('.is-focused').text()).toBe('Option 0');
    });

    it('closes on Escape, then drops the typed text on a second Escape', async () => {
        const wrapper = mountPicker();
        await type(wrapper, 'ap');
        await key(wrapper, 'Escape');
        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
        expect(input(wrapper).element.value).toBe('ap');
        await key(wrapper, 'Escape');
        expect(input(wrapper).element.value).toBe('');
    });

    it('does not submit the form on Enter in the closed list, but opens it', async () => {
        const wrapper = mountPicker();
        const event = new KeyboardEvent('keydown', { key: 'Enter', cancelable: true });
        input(wrapper).element.dispatchEvent(event);
        await wrapper.vm.$nextTick();
        expect(event.defaultPrevented).toBe(true);
        expect(input(wrapper).attributes('aria-expanded')).toBe('true');
        expect(emitted(wrapper)).toEqual([]);
    });

    it('closes on a click outside', async () => {
        const wrapper = mountPicker();
        await key(wrapper, 'ArrowDown');
        document.body.dispatchEvent(new Event('pointerdown', { bubbles: true }));
        await wrapper.vm.$nextTick();
        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
    });

    it('renders hidden inputs for a name, an empty one for no choice', async () => {
        const wrapper = mountPicker({ name: 'Model[fruits]', modelValue: ['a', 'c'] });
        expect(wrapper.findAll('input[type="hidden"]').map((node) => [node.attributes('name'), node.element.value]))
            .toEqual([['Model[fruits][]', 'a'], ['Model[fruits][]', 'c']]);

        await wrapper.setProps({ modelValue: [] });
        expect(wrapper.findAll('input[type="hidden"]').map((node) => [node.attributes('name'), node.element.value]))
            .toEqual([['Model[fruits]', '']]);
    });

    it('clears every removable item with the clear button', async () => {
        const wrapper = mountPicker({ modelValue: ['a', 'b'], lockedIds: ['a'] });
        await wrapper.find('.select-menu-clear').trigger('click');
        expect(emitted(wrapper).at(-1)).toEqual(['a']);
    });

    it('offers a new item of a static picker as _add:<text>', async () => {
        const wrapper = mountPicker({ allowNew: true, newPrefix: '_add:' });
        await type(wrapper, 'Date');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual([['_add:Date']]);
    });

    it('offers a new item without a prefix as plain text, also in a multiple picker', async () => {
        const wrapper = mountPicker({ multiple: true, allowNew: true });
        await type(wrapper, 'Date');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual([['Date']]);
    });

    it('keeps locked items first when modelValue changes', async () => {
        const wrapper = mountPicker({ lockedIds: ['a'] });
        await wrapper.setProps({ modelValue: ['b', 'a'] });
        expect(chipTexts(wrapper)).toEqual(['Apple', 'Banana']);
    });

    it('counts maxResults over the items not chosen yet', async () => {
        const wrapper = mountPicker({ maxResults: 2, modelValue: ['a'] });
        await key(wrapper, 'ArrowDown');
        expect(optionTexts(wrapper)).toEqual(['Banana', 'Cherry']);
    });
});
