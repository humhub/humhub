import { describe, expect, it, vi } from 'vitest';
import { h } from 'vue';
import { mount } from '@vue/test-utils';
import PageToolbar from '../../vue/PageToolbar.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const actions = [
    { id: 'create', icon: 'plus', label: 'Create Space', url: '/space/create', modal: true },
    { id: 'browse', icon: 'compass', label: 'Browse', url: '/browse', htmlOptions: { 'data-foo': 'bar', class: 'extra' } },
];

describe('PageToolbar', () => {
    it('renders the title as h1 and is labelled by it', () => {
        const wrapper = mount(PageToolbar, { props: { title: 'Files' } });

        expect(wrapper.element.tagName).toBe('SECTION');
        expect(wrapper.classes()).toContain('c-page-toolbar');
        const title = wrapper.find('.c-page-toolbar__header > h1.c-page-toolbar__title');
        expect(title.text()).toBe('Files');
        expect(wrapper.attributes('aria-labelledby')).toBe(title.attributes('id'));
    });

    it('renders the title with titleTag, under an id of its own per instance', () => {
        const first = mount(PageToolbar, { props: { title: 'A', titleTag: 'h2' } });
        const second = mount(PageToolbar, { props: { title: 'B' } });

        expect(first.find('h2.c-page-toolbar__title').text()).toBe('A');
        expect(first.find('h1').exists()).toBe(false);
        expect(first.find('h2').attributes('id')).not.toBe(second.find('h1').attributes('id'));
    });

    it('puts the actions right of the title and the default slot below the header', () => {
        const wrapper = mount(PageToolbar, {
            props: { title: 'Files' },
            slots: {
                actions: () => h('button', { class: 'add' }, '+'),
                default: () => h('form', { class: 'c-filter-bar' }),
            },
        });

        const header = wrapper.find('.c-page-toolbar__header');
        expect(header.find('.c-page-toolbar__actions .add').exists()).toBe(true);
        expect(header.element.nextElementSibling).toBe(wrapper.find('.c-filter-bar').element);
    });

    it('renders the header for actions without a title, unlabelled', () => {
        const wrapper = mount(PageToolbar, { slots: { actions: () => h('button', { class: 'add' }) } });

        expect(wrapper.find('.c-page-toolbar__header .add').exists()).toBe(true);
        expect(wrapper.find('.c-page-toolbar__title').exists()).toBe(false);
        expect(wrapper.attributes('aria-labelledby')).toBeUndefined();
    });

    it('renders no header without title and actions', () => {
        const wrapper = mount(PageToolbar, { slots: { default: () => h('p', { class: 'content' }) } });

        expect(wrapper.find('.c-page-toolbar__header').exists()).toBe(false);
        expect(wrapper.find('.content').exists()).toBe(true);
    });

    it('renders the actions prop as icon buttons before the actions slot', () => {
        const wrapper = mount(PageToolbar, {
            props: { title: 'Spaces', actions },
            slots: { actions: () => h('button', { class: 'custom' }) },
        });

        const children = wrapper.find('.c-page-toolbar__actions').element.children;
        expect(children).toHaveLength(3);
        expect(children[2].classList.contains('custom')).toBe(true);

        const [create, browse] = wrapper.findAll('.c-page-toolbar__actions a');
        expect(create.classes()).toEqual(expect.arrayContaining(['btn', 'btn-secondary', 'c-icon-button']));
        expect(create.attributes('href')).toBe('/space/create');
        expect(create.attributes('aria-label')).toBe('Create Space');
        expect(create.attributes('title')).toBe('Create Space');
        expect(create.find('i.ti.ti-plus').attributes('aria-hidden')).toBe('true');
        expect(browse.attributes('href')).toBe('/browse');
        expect(browse.attributes('data-foo')).toBe('bar');
        expect(browse.classes()).toEqual(expect.arrayContaining(['extra', 'c-icon-button']));
        expect(browse.find('i.ti.ti-compass').exists()).toBe(true);
    });

    it('renders the variant of an action as its button class', () => {
        const wrapper = mount(PageToolbar, {
            props: {
                actions: [
                    { id: 'plain', icon: 'plus', label: 'Plain', url: '#' },
                    { id: 'accent', icon: 'plus', label: 'Accent', url: '#', variant: 'accent' },
                    { id: 'primary', icon: 'plus', label: 'Primary', url: '#', variant: 'primary', htmlOptions: { class: 'extra' } },
                    { id: 'unknown', icon: 'plus', label: 'Unknown', url: '#', variant: 'danger' },
                ],
            },
        });
        const variant = (id) => wrapper.find(`[data-action-id="${id}"]`).classes().filter((name) => /^btn-/.test(name));

        expect(variant('plain')).toEqual(['btn-secondary']);
        expect(variant('accent')).toEqual(['btn-accent']);
        expect(variant('primary')).toEqual(['btn-primary']);
        expect(wrapper.find('[data-action-id="primary"]').classes()).toEqual(expect.arrayContaining(['btn', 'c-icon-button', 'extra']));
        expect(variant('unknown')).toEqual(['btn-secondary']);
    });

    it('renders the header for the actions prop alone', () => {
        const wrapper = mount(PageToolbar, { props: { actions } });

        expect(wrapper.findAll('.c-page-toolbar__header .c-icon-button')).toHaveLength(2);
    });

    it('opens a modal action through the bridge and follows a plain one as a link', async () => {
        const load = vi.spyOn(globalThis.humhubStubs.modal.global, 'load');
        const wrapper = mount(PageToolbar, { props: { actions }, attachTo: document.body });
        const [create, browse] = wrapper.findAll('.c-page-toolbar__actions a');

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
