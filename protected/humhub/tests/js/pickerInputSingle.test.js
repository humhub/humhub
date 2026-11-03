import { afterEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import PickerInput, { SEARCH_DEBOUNCE_MS } from '../../vue/PickerInput.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mounted = [];
const mountPicker = (props) => {
    const wrapper = mount(PickerInput, { props: { label: 'Choice', ...props }, attachTo: document.body });
    mounted.push(wrapper);
    return wrapper;
};
const input = (wrapper) => wrapper.find('input[role="combobox"]');
const key = (wrapper, name) => input(wrapper).trigger('keydown', { key: name });
const optionTexts = (wrapper) => wrapper.findAll('[role="option"] .picker-option-label').map((node) => node.text());
const emitted = (wrapper) => (wrapper.emitted('update:modelValue') || []).map((args) => args[0]);

const sizes = [{ id: '', text: 'Please select' }, { id: 's', text: 'Small' }, { id: 'm', text: 'Medium' }, { id: 'l', text: 'Large' }];

describe('PickerInput — enhanced select', () => {
    afterEach(() => mounted.splice(0).forEach((wrapper) => wrapper.unmount()));

    it('shows the chosen label, the empty option as placeholder and a chevron', () => {
        const wrapper = mountPicker({ options: sizes, modelValue: 'm' });
        expect(input(wrapper).element.value).toBe('Medium');
        expect(wrapper.find('.select-menu-chevron').exists()).toBe(true);
        expect(mountPicker({ options: sizes }).find('input').attributes('placeholder')).toBe('Please select');
    });

    it('marks the picker editing only while the user types (the caret hides otherwise)', async () => {
        const wrapper = mountPicker({ options: sizes, modelValue: 'm', search: 0 });
        expect(wrapper.find('.picker').classes()).not.toContain('is-editing');
        await input(wrapper).setValue('La');
        expect(wrapper.find('.picker').classes()).toContain('is-editing');
        await key(wrapper, 'Enter');
        expect(wrapper.find('.picker').classes()).not.toContain('is-editing');
    });

    it('leaves the chosen label unselected on a click, typing replaces it', async () => {
        const wrapper = mountPicker({ options: sizes, modelValue: 'm', search: 0 });
        await wrapper.find('.picker-field').trigger('click');
        const element = input(wrapper).element;
        expect(element.selectionStart).toBe(element.selectionEnd);

        // The browser inserts the typed text after `beforeinput`: into an emptied input.
        element.dispatchEvent(new InputEvent('beforeinput', { inputType: 'insertText', data: 'L', cancelable: true }));
        expect(element.value).toBe('');
        await input(wrapper).setValue('Lar');
        expect(optionTexts(wrapper)).toEqual(['Large']);
    });

    it('has no search below the threshold: the input is read-only and letters jump', async () => {
        const wrapper = mountPicker({ options: sizes, search: 5 });
        expect(input(wrapper).attributes('readonly')).toBeDefined();

        await key(wrapper, 'l');
        expect(wrapper.find('.is-focused').text()).toBe('Large');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual(['l']);
        expect(input(wrapper).attributes('aria-expanded')).toBe('false');
    });

    it('searches from the threshold on', async () => {
        const wrapper = mountPicker({ options: sizes, search: 3 });
        expect(input(wrapper).attributes('readonly')).toBeUndefined();
        input(wrapper).element.value = 'me';
        await input(wrapper).trigger('input');
        expect(optionTexts(wrapper)).toEqual(['Medium']);
    });

    it('marks the chosen option active and lists it', async () => {
        const wrapper = mountPicker({ options: sizes, modelValue: 's' });
        await key(wrapper, 'ArrowDown');
        expect(optionTexts(wrapper)).toEqual(['Small', 'Medium', 'Large']);
        expect(wrapper.find('[role="option"].active').attributes('aria-selected')).toBe('true');
    });

    it('clears only when allowed', async () => {
        expect(mountPicker({ options: sizes, modelValue: 's', allowClear: false }).find('.select-menu-clear').exists()).toBe(false);
        const wrapper = mountPicker({ options: sizes, modelValue: 's' });
        await wrapper.find('.select-menu-clear').trigger('click');
        expect(emitted(wrapper)).toEqual(['']);
    });

    it('renders option groups as headers', async () => {
        const wrapper = mountPicker({ options: [{ id: 'a', text: 'A', group: 'First' }, { id: 'b', text: 'B', group: 'Second' }] });
        await key(wrapper, 'ArrowDown');
        expect(wrapper.findAll('.dropdown-header').map((node) => node.text())).toEqual(['First', 'Second']);
    });

    it('drops the typed text when the closed control loses the focus', async () => {
        const wrapper = mountPicker({ options: sizes, search: 0, modelValue: 'm' });
        input(wrapper).element.value = 'sm';
        await input(wrapper).trigger('input');
        await key(wrapper, 'Escape');
        expect(input(wrapper).element.value).toBe('sm');

        await input(wrapper).trigger('blur');
        expect(input(wrapper).element.value).toBe('Medium');
    });

    it('keeps a locked value', async () => {
        const wrapper = mountPicker({ options: sizes, modelValue: 's', lockedIds: ['s'] });
        await key(wrapper, 'ArrowDown');
        await key(wrapper, 'End');
        await key(wrapper, 'Enter');
        expect(emitted(wrapper)).toEqual([]);
        expect(input(wrapper).element.value).toBe('Small');
    });

    it('shows the image of the chosen item in the field', () => {
        const wrapper = mountPicker({ options: [{ id: 'r', text: 'Red', image: '#ff0000' }], modelValue: 'r' });
        expect(wrapper.find('.picker-value-image').exists()).toBe(true);
    });
});

describe('PickerInput — single record', () => {
    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
        vi.useRealTimers();
    });

    it('treats maxSelection 1 of a multiple picker as one record, the value still an array', async () => {
        vi.useFakeTimers();
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve([{ id: 7, text: 'Owner Olga' }]));
        const wrapper = mountPicker({ multiple: true, maxSelection: 1, url: '/search', minInput: 1 });

        expect(wrapper.find('.picker').classes()).toContain('is-single');
        input(wrapper).element.value = 'o';
        await input(wrapper).trigger('input');
        vi.advanceTimersByTime(SEARCH_DEBOUNCE_MS);
        await flushPromises();
        await key(wrapper, 'Enter');

        expect(emitted(wrapper)).toEqual([['7']]);
        expect(input(wrapper).element.value).toBe('Owner Olga');
        expect(wrapper.find('.picker-chip').exists()).toBe(false);
    });
});
