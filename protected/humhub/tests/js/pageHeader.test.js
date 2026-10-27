import { describe, expect, it, vi } from 'vitest';
import { h } from 'vue';
import { mount } from '@vue/test-utils';
import PageHeader from '../../vue/PageHeader.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const actions = [
    { id: 'create', icon: 'plus', label: 'Create Space', url: '/space/create', modal: true, primary: true },
    { id: 'browse', icon: 'compass', label: 'Browse', url: '/browse', htmlOptions: { 'data-foo': 'bar', class: 'extra' } },
];

describe('PageHeader', () => {
    it('renders the title as h1 and is labelled by it', () => {
        const wrapper = mount(PageHeader, { props: { title: 'Files' } });

        expect(wrapper.element.tagName).toBe('SECTION');
        expect(wrapper.classes()).toContain('page-header');
        const title = wrapper.find('.page-header-bar > h1.page-header-title');
        expect(title.text()).toBe('Files');
        expect(wrapper.attributes('aria-labelledby')).toBe(title.attributes('id'));
    });

    it('renders the title with titleTag, under an id of its own per instance', () => {
        const first = mount(PageHeader, { props: { title: 'A', titleTag: 'h2' } });
        const second = mount(PageHeader, { props: { title: 'B' } });

        expect(first.find('h2.page-header-title').text()).toBe('A');
        expect(first.find('h1').exists()).toBe(false);
        expect(first.find('h2').attributes('id')).not.toBe(second.find('h1').attributes('id'));
    });

    it('puts the actions right of the title and the default slot into the body', () => {
        const wrapper = mount(PageHeader, {
            props: { title: 'Files' },
            slots: {
                actions: () => h('button', { class: 'add' }, '+'),
                default: () => h('form', { class: 'filter-bar-body' }),
            },
        });

        const bar = wrapper.find('.page-header-bar');
        expect(bar.find('.page-header-actions .add').exists()).toBe(true);
        expect(bar.element.nextElementSibling).toBe(wrapper.find('.page-header-body').element);
        expect(wrapper.find('.page-header-body > .filter-bar-body').exists()).toBe(true);
    });

    it('renders the bar for actions without a title, unlabelled', () => {
        const wrapper = mount(PageHeader, { slots: { actions: () => h('button', { class: 'add' }) } });

        expect(wrapper.find('.page-header-bar .add').exists()).toBe(true);
        expect(wrapper.find('.page-header-title').exists()).toBe(false);
        expect(wrapper.attributes('aria-labelledby')).toBeUndefined();
    });

    it('renders neither bar nor body without title, actions and content', () => {
        const empty = mount(PageHeader);
        expect(empty.find('.page-header-bar').exists()).toBe(false);
        expect(empty.find('.page-header-body').exists()).toBe(false);

        const content = mount(PageHeader, { slots: { default: () => h('p', { class: 'content' }) } });
        expect(content.find('.page-header-bar').exists()).toBe(false);
        expect(content.find('.page-header-body .content').exists()).toBe(true);
    });

    it('renders the back arrow before the title only with a backUrl', () => {
        expect(mount(PageHeader, { props: { title: 'Users' } }).find('.page-header-back').exists()).toBe(false);

        const wrapper = mount(PageHeader, { props: { title: 'Users', backUrl: '/admin' } });
        const back = wrapper.find('.page-header-bar > a.page-header-back');
        expect(back.attributes('href')).toBe('/admin');
        expect(back.classes()).toEqual(expect.arrayContaining(['btn', 'btn-ghost', 'btn-icon-only']));
        expect(back.attributes('aria-label')).toBeTruthy();
        expect(back.element.nextElementSibling).toBe(wrapper.find('.page-header-title').element);
    });

    it('renders the tools, then the slot, then the primary action', () => {
        const wrapper = mount(PageHeader, {
            props: { title: 'Spaces', actions },
            slots: { actions: () => h('button', { class: 'custom' }) },
        });

        const children = [...wrapper.find('.page-header-actions').element.children];
        expect(children.map((child) => child.dataset.actionId || child.className)).toEqual(['browse', 'custom', 'create']);

        const create = wrapper.find('[data-action-id="create"]');
        expect(create.classes()).toEqual(expect.arrayContaining(['btn', 'btn-accent', 'btn-icon-only']));
        expect(create.attributes('href')).toBe('/space/create');
        expect(create.attributes('aria-label')).toBe('Create Space');
        expect(create.attributes('title')).toBe('Create Space');
        expect(create.find('i.ti.ti-plus').attributes('aria-hidden')).toBe('true');

        const browse = wrapper.find('[data-action-id="browse"]');
        expect(browse.classes()).toEqual(expect.arrayContaining(['btn', 'btn-secondary', 'btn-icon-only', 'extra']));
        expect(browse.attributes('data-foo')).toBe('bar');
        expect(browse.find('i.ti.ti-compass').exists()).toBe(true);
    });

    it('shows one primary action; a further one is a tool', () => {
        const wrapper = mount(PageHeader, {
            props: {
                actions: [
                    { id: 'first', icon: 'plus', label: 'First', url: '#', primary: true },
                    { id: 'second', icon: 'upload', label: 'Second', url: '#', primary: true },
                    { id: 'tool', icon: 'settings', label: 'Tool', url: '#' },
                ],
            },
        });
        const variant = (id) => wrapper.find(`[data-action-id="${id}"]`).classes().filter((name) => /^btn-(?!icon)/.test(name));

        expect(variant('first')).toEqual(['btn-accent']);
        expect(variant('second')).toEqual(['btn-secondary']);
        expect(variant('tool')).toEqual(['btn-secondary']);
        const ids = wrapper.findAll('.page-header-actions > a').map((a) => a.attributes('data-action-id'));
        expect(ids).toEqual(['second', 'tool', 'first']);
    });

    it('renders the menu as a ⋮ dropdown after the primary action', () => {
        const wrapper = mount(PageHeader, {
            props: {
                actions,
                menu: [{ id: 'export', icon: 'download', label: 'Export', url: '/export' }],
            },
            // registered by the island runtime on the real app; the ⋮ menu is a DropdownMenu
            global: { directives: { additions: {} } },
        });

        const actionsEl = wrapper.find('.page-header-actions');
        expect(actionsEl.element.lastElementChild.classList.contains('page-header-actions-menu')).toBe(true);
        const toggle = actionsEl.find('.page-header-actions-menu [data-bs-toggle="dropdown"]');
        expect(toggle.classes()).toEqual(expect.arrayContaining(['btn', 'btn-ghost', 'btn-icon-only']));
        expect(toggle.attributes('aria-label')).toBeTruthy();
        expect(actionsEl.find('.dropdown-menu a.dropdown-item[href="/export"]').text()).toBe('Export');
        expect(actionsEl.find('a[data-action-id="export"]').exists()).toBe(false);
    });

    it('renders no ⋮ menu without menu entries', () => {
        const wrapper = mount(PageHeader, { props: { actions } });

        expect(wrapper.find('.page-header-actions-menu').exists()).toBe(false);
        expect(wrapper.findAll('.page-header-bar .btn-icon-only')).toHaveLength(2);
    });

    it('opens a modal action through the bridge and follows a plain one as a link', async () => {
        const load = vi.spyOn(globalThis.humhubStubs.modal.global, 'load');
        const wrapper = mount(PageHeader, { props: { actions }, attachTo: document.body });
        const create = wrapper.find('[data-action-id="create"]');
        const browse = wrapper.find('[data-action-id="browse"]');

        const modalClick = new MouseEvent('click', { bubbles: true, cancelable: true });
        create.element.dispatchEvent(modalClick);
        expect(modalClick.defaultPrevented).toBe(true);
        expect(load).toHaveBeenCalledWith('/space/create');

        load.mockClear();
        const linkClick = new MouseEvent('click', { bubbles: true, cancelable: true });
        browse.element.addEventListener('click', (event) => {
            expect(event.defaultPrevented).toBe(false);
            event.preventDefault();
        });
        browse.element.dispatchEvent(linkClick);
        expect(load).not.toHaveBeenCalled();
        load.mockRestore();
        wrapper.unmount();
    });
});
