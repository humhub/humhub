import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ViewSwitch from '../../vue/ViewSwitch.vue';

const options = [
    { value: 'tiles', icon: 'layout-grid', label: 'Tiles' },
    { value: 'list', icon: 'list', label: 'List' },
];

describe('ViewSwitch', () => {
    it('renders one labelled icon button per option', () => {
        const wrapper = mount(ViewSwitch, { props: { modelValue: 'tiles', options, label: 'View' } });

        expect(wrapper.attributes('role')).toBe('group');
        expect(wrapper.attributes('aria-label')).toBe('View');
        const buttons = wrapper.findAll('button');
        expect(buttons).toHaveLength(2);
        expect(buttons[0].attributes('aria-label')).toBe('Tiles');
        expect(buttons[0].attributes('title')).toBe('Tiles');
        expect(buttons[1].attributes('aria-label')).toBe('List');
        expect(buttons[0].find('i.ti.ti-layout-grid').exists()).toBe(true);
        expect(buttons[1].find('i.ti.ti-list').exists()).toBe(true);
    });

    it('marks the active option as pressed', () => {
        const wrapper = mount(ViewSwitch, { props: { modelValue: 'list', options } });
        const [tiles, list] = wrapper.findAll('button');

        expect(wrapper.attributes('aria-label')).toBeUndefined();
        expect(tiles.attributes('aria-pressed')).toBe('false');
        expect(list.attributes('aria-pressed')).toBe('true');
        expect(list.classes()).toContain('is-active');
    });

    it('emits the chosen value, and nothing for the active one', async () => {
        const wrapper = mount(ViewSwitch, { props: { modelValue: 'tiles', options } });
        const [tiles, list] = wrapper.findAll('button');

        await tiles.trigger('click');
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();

        await list.trigger('click');
        expect(wrapper.emitted('update:modelValue')).toEqual([['list']]);
    });
});
