import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import DropdownMenu from '../../vue/DropdownMenu.vue';

describe('DropdownMenu', () => {
    it('renders the toggle/menu structure with the given aria-label', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu' },
        });

        expect(wrapper.find('ul.nav.nav-pills.preferences').exists()).toBe(true);
        expect(wrapper.find('li.nav-item.dropdown').exists()).toBe(true);

        const toggle = wrapper.find('a[data-bs-toggle="dropdown"]');
        expect(toggle.exists()).toBe(true);
        expect(toggle.attributes('aria-label')).toBe('Toggle menu');
        expect(toggle.attributes('aria-haspopup')).toBe('true');
        expect(toggle.attributes('aria-expanded')).toBe('false');
        expect(toggle.attributes('role')).toBe('button');
        expect(toggle.classes()).toEqual(['nav-link', 'dropdown-toggle']);
    });

    it('renders slot content as the menu items', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu' },
            slots: {
                default: '<li><a href="#" class="dropdown-item">Edit</a></li><li><a href="#" class="dropdown-item">Delete</a></li>',
            },
        });

        const items = wrapper.findAll('.dropdown-menu > li');
        expect(items).toHaveLength(2);
        expect(items[0].text()).toBe('Edit');
        expect(items[1].text()).toBe('Delete');
    });

    it('aligns the menu to the end by default', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu' },
        });

        expect(wrapper.find('.dropdown-menu').classes()).toContain('dropdown-menu-end');
    });

    it('drops dropdown-menu-end when alignEnd is false', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu', alignEnd: false },
        });

        expect(wrapper.find('.dropdown-menu').classes()).not.toContain('dropdown-menu-end');
    });

    it('replaces the toggle class entirely when toggleClass is set', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu', toggleClass: 'btn btn-icon-only' },
        });

        const toggle = wrapper.find('a[data-bs-toggle="dropdown"]');
        expect(toggle.classes()).toEqual(['btn', 'btn-icon-only']);
        expect(toggle.classes()).not.toContain('nav-link');
    });
});
