import { describe, expect, it, vi } from 'vitest';
import { config, mount } from '@vue/test-utils';
import SelectionMenu from '../../vue/SelectionMenu.vue';

// SelectionMenu reads `i18n` from `@humhub/vue` for the toggle's accessible name - needs the
// real humhub.vue.js module registered so the @humhub/vue shim (see
// support/humhubVueShim.mjs) has something to delegate to, mirroring userImage.test.js's own
// setup for the same reason.
await import('../../resources/js/humhub/humhub.vue.js');

// `v-additions` is registered by the island runtime on the real app; SelectionMenu always
// renders a DropdownMenu (once selected), whose compiled render function resolves the
// directive for every render regardless of whether an entry uses the `html` escape hatch - see
// dropdownMenu.test.js for the same file-wide stub.
config.global.directives = {
    ...config.global.directives,
    additions: {
        mounted(el) {
            globalThis.humhubStubs.additions.applyTo(jQuery(el));
        },
        updated(el) {
            globalThis.humhubStubs.additions.applyTo(jQuery(el));
        },
    },
};

const entries = (onMove) => [
    { id: 'move', label: 'Move', icon: 'arrows-left-right', sortOrder: 10, onClick: onMove },
    { id: 'divider', divider: true, sortOrder: 90 },
    { id: 'clear', label: 'Clear Selection', icon: 'x', sortOrder: 100, onClick: () => {} },
];

describe('SelectionMenu', () => {
    it('renders nothing without a selection', () => {
        const wrapper = mount(SelectionMenu, { props: { count: 0, menuId: 'test.selection', entries: entries() } });

        expect(wrapper.find('.c-selection-menu').exists()).toBe(false);
    });

    it('renders a labelled menu toggle for a selection', () => {
        const wrapper = mount(SelectionMenu, { props: { count: 3, menuId: 'test.selection', entries: entries() } });
        const toggle = wrapper.find('.c-selection-menu [data-bs-toggle="dropdown"]');

        expect(toggle.exists()).toBe(true);
        expect(toggle.attributes('aria-label')).toBe('Actions for 3 selected');
        expect(toggle.find('i.ti.ti-dots-vertical').exists()).toBe(true);
    });

    it('lists the entries and hands them the context', async () => {
        const onMove = vi.fn();
        const wrapper = mount(SelectionMenu, {
            props: {
                count: 2, menuId: 'test.selection', entries: entries(onMove), context: { keys: ['a', 'b'] },
            },
        });

        const items = wrapper.findAll('.dropdown-menu .dropdown-item');
        expect(items.map((item) => item.text())).toEqual(['Move', 'Clear Selection']);
        expect(wrapper.find('.dropdown-menu hr.dropdown-divider').exists()).toBe(true);

        await items[0].trigger('click');
        expect(onMove).toHaveBeenCalledWith({ keys: ['a', 'b'] });
    });

    it('merges entries registered for its menuId', async () => {
        globalThis.humhub.modules.vue.registerMenuEntry('test.selection.extra', {
            id: 'extra', label: 'Extra', sortOrder: 50, onClick: () => {},
        });
        const wrapper = mount(SelectionMenu, {
            props: { count: 1, menuId: 'test.selection.extra', entries: entries() },
        });

        expect(wrapper.findAll('.dropdown-item').map((item) => item.text())).toEqual(['Move', 'Extra', 'Clear Selection']);
    });
});
