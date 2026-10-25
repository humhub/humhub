import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ViewSwitch from '../../vue/ViewSwitch.vue';

const options = [
    { value: 'tiles', icon: 'layout-grid', label: 'Tiles' },
    { value: 'list', icon: 'list', label: 'List' },
];

describe('ViewSwitch', () => {
    it('renders one labelled radio toggle button per option', () => {
        const wrapper = mount(ViewSwitch, { props: { modelValue: 'tiles', options, label: 'View' } });

        expect(wrapper.classes()).toEqual(expect.arrayContaining(['btn-group', 'view-switch']));
        expect(wrapper.attributes('role')).toBe('group');
        expect(wrapper.attributes('aria-label')).toBe('View');
        const inputs = wrapper.findAll('input.btn-check[type="radio"]');
        const labels = wrapper.findAll('label.btn.btn-ghost.btn-icon-only');
        expect(inputs).toHaveLength(2);
        expect(labels).toHaveLength(2);
        expect(inputs[0].attributes('name')).toBe(inputs[1].attributes('name'));
        expect(labels[0].attributes('for')).toBe(inputs[0].attributes('id'));
        expect(labels[0].attributes('title')).toBe('Tiles');
        expect(labels[0].find('.visually-hidden').text()).toBe('Tiles');
        expect(labels[1].find('.visually-hidden').text()).toBe('List');
        expect(labels[0].find('i.ti.ti-layout-grid').exists()).toBe(true);
        expect(labels[1].find('i.ti.ti-list').exists()).toBe(true);
    });

    it('checks the active option, under a group name of its own per instance', () => {
        const wrapper = mount(ViewSwitch, { props: { modelValue: 'list', options } });
        const other = mount(ViewSwitch, { props: { modelValue: 'list', options } });
        const [tiles, list] = wrapper.findAll('input');

        expect(wrapper.attributes('aria-label')).toBeUndefined();
        expect(tiles.element.checked).toBe(false);
        expect(list.element.checked).toBe(true);
        expect(other.find('input').attributes('name')).not.toBe(tiles.attributes('name'));
    });

    it('emits the chosen value', async () => {
        const wrapper = mount(ViewSwitch, { props: { modelValue: 'tiles', options } });
        const [, list] = wrapper.findAll('input');

        await list.setValue(true);
        expect(wrapper.emitted('update:modelValue')).toEqual([['list']]);
    });
});
