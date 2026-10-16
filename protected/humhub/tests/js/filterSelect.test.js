import { afterEach, describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import FilterSelect from '../../vue/FilterSelect.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const options = [
    { value: '', label: 'All' },
    { value: 'installed', label: 'Installed' },
    { value: 'not-installed', label: 'Not Installed' },
    { value: 'update', label: 'Update Available' },
];

const mounted = [];
const mountSelect = (props = {}) => {
    const wrapper = mount(FilterSelect, {
        props: { options, placeholder: 'Status (All)', label: 'Status', ...props },
        attachTo: document.body,
    });
    mounted.push(wrapper);
    return wrapper;
};
const button = (wrapper) => wrapper.find('.c-select__button');
const listbox = (wrapper) => wrapper.find('[role="listbox"]');
const key = (target, name) => target.trigger('keydown', { key: name });

describe('FilterSelect', () => {
    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    });

    it('shows the placeholder as the "all" state and lists the options without the empty one', () => {
        const wrapper = mountSelect();

        expect(button(wrapper).text()).toBe('Status (All)');
        expect(button(wrapper).attributes('aria-haspopup')).toBe('listbox');
        expect(button(wrapper).attributes('aria-expanded')).toBe('false');
        expect(button(wrapper).attributes('aria-label')).toBe('Status');
        expect(wrapper.findAll('[role="option"]').map((o) => o.text())).toEqual(['Installed', 'Not Installed', 'Update Available']);
        expect(wrapper.classes()).not.toContain('has-selection');
        expect(wrapper.find('.c-select__clear').attributes('disabled')).toBeDefined();
    });

    it('falls back to the empty option\'s label as placeholder', () => {
        expect(button(mountSelect({ placeholder: '' })).text()).toBe('All');
    });

    it('falls back to its label as placeholder without a placeholder or an empty option', () => {
        const withoutEmpty = options.filter((option) => option.value !== '');
        const wrapper = mountSelect({ placeholder: '', options: withoutEmpty });

        expect(button(wrapper).text()).toBe('Status');
        expect(wrapper.classes()).not.toContain('has-selection');
    });

    it('opens on click, selects an option and closes, returning focus', async () => {
        const wrapper = mountSelect();

        await button(wrapper).trigger('click');
        expect(wrapper.classes()).toContain('is-open');
        expect(button(wrapper).attributes('aria-expanded')).toBe('true');
        expect(document.activeElement).toBe(listbox(wrapper).element);

        await wrapper.findAll('[role="option"]')[1].trigger('click');
        expect(wrapper.emitted('update:modelValue')).toEqual([['not-installed']]);
        expect(wrapper.classes()).not.toContain('is-open');
        expect(document.activeElement).toBe(button(wrapper).element);
    });

    it('shows the chosen label, marks it selected and clears to an empty value', async () => {
        const wrapper = mountSelect({ modelValue: 'installed' });

        expect(button(wrapper).text()).toBe('Installed');
        expect(wrapper.classes()).toContain('has-selection');
        expect(wrapper.find('[aria-selected="true"]').text()).toBe('Installed');

        const clear = wrapper.find('.c-select__clear');
        expect(clear.attributes('disabled')).toBeUndefined();
        expect(clear.attributes('aria-label')).toBe('Clear selection');
        await clear.trigger('click');
        expect(wrapper.emitted('update:modelValue')).toEqual([['']]);
        expect(document.activeElement).toBe(button(wrapper).element);
    });

    it('shows a value that is not among the options as is', () => {
        expect(button(mountSelect({ modelValue: '42' })).text()).toBe('42');
    });

    it('supports the keyboard: open, move, choose, escape', async () => {
        const wrapper = mountSelect({ modelValue: 'installed' });

        await key(button(wrapper), 'ArrowDown');
        expect(wrapper.classes()).toContain('is-open');
        const options = () => wrapper.findAll('[role="option"]');
        // Opens on the selected option.
        expect(listbox(wrapper).attributes('aria-activedescendant')).toBe(options()[0].attributes('id'));

        await key(listbox(wrapper), 'ArrowDown');
        await key(listbox(wrapper), 'ArrowDown');
        await key(listbox(wrapper), 'ArrowDown'); // stays on the last one
        expect(listbox(wrapper).attributes('aria-activedescendant')).toBe(options()[2].attributes('id'));
        await key(listbox(wrapper), 'Home');
        expect(options()[0].classes()).toContain('is-active');
        await key(listbox(wrapper), 'End');
        await key(listbox(wrapper), 'ArrowUp');
        expect(options()[1].classes()).toContain('is-active');

        await key(listbox(wrapper), 'Enter');
        expect(wrapper.emitted('update:modelValue')).toEqual([['not-installed']]);
        expect(wrapper.classes()).not.toContain('is-open');
        expect(document.activeElement).toBe(button(wrapper).element);

        await key(button(wrapper), 'Enter');
        expect(wrapper.classes()).toContain('is-open');
        await key(listbox(wrapper), 'Escape');
        expect(wrapper.classes()).not.toContain('is-open');
        expect(document.activeElement).toBe(button(wrapper).element);
        expect(wrapper.emitted('update:modelValue')).toHaveLength(1);
    });

    it('jumps to an option by its first letter and does not re-emit the current value', async () => {
        const wrapper = mountSelect({ modelValue: 'installed' });

        await key(button(wrapper), ' ');
        await key(listbox(wrapper), 'u');
        expect(wrapper.findAll('[role="option"]')[2].classes()).toContain('is-active');
        await key(listbox(wrapper), 'i');
        await key(listbox(wrapper), ' ');
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('closes on a click outside and on Tab, without taking focus', async () => {
        const wrapper = mountSelect();
        const outside = document.createElement('button');
        document.body.appendChild(outside);

        await button(wrapper).trigger('click');
        outside.dispatchEvent(new Event('pointerdown', { bubbles: true }));
        await wrapper.vm.$nextTick();
        expect(wrapper.classes()).not.toContain('is-open');

        await button(wrapper).trigger('click');
        await key(listbox(wrapper), 'Tab');
        expect(wrapper.classes()).not.toContain('is-open');
        outside.remove();
    });

    it('is disabled while loading, with a spinner instead of the chevron', async () => {
        const wrapper = mountSelect({ loading: true });

        expect(button(wrapper).attributes('disabled')).toBeDefined();
        expect(button(wrapper).attributes('aria-busy')).toBe('true');
        expect(wrapper.find('.c-select__spinner').exists()).toBe(true);
        expect(wrapper.find('.c-select__chevron').exists()).toBe(false);
        await button(wrapper).trigger('click');
        expect(wrapper.classes()).not.toContain('is-open');

        await wrapper.setProps({ loading: false });
        expect(button(wrapper).attributes('disabled')).toBeUndefined();
        expect(wrapper.find('.c-select__chevron').exists()).toBe(true);
    });

    it('closes when it becomes disabled while open', async () => {
        const wrapper = mountSelect();

        await button(wrapper).trigger('click');
        await wrapper.setProps({ disabled: true });
        expect(wrapper.classes()).not.toContain('is-open');
    });

    it('uses the given id for the toggle button', () => {
        expect(button(mountSelect({ id: 'status-filter' })).attributes('id')).toBe('status-filter');
    });
});
