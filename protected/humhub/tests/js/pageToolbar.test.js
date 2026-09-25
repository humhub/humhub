import { describe, expect, it } from 'vitest';
import { h } from 'vue';
import { mount } from '@vue/test-utils';
import PageToolbar from '../../vue/PageToolbar.vue';

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
});
