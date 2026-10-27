import { describe, expect, it } from 'vitest';
import { defineComponent, h, ref } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import ContentCard from '../../vue/ContentCard.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

describe('ContentCard', () => {
    it('renders a section labelled by its h2 title, with the content as body', () => {
        const wrapper = mount(ContentCard, { props: { title: 'Users' }, slots: { default: () => h('p', { class: 'content' }) } });

        expect(wrapper.element.tagName).toBe('SECTION');
        expect(wrapper.classes()).toEqual(expect.arrayContaining(['card', 'content-card']));
        const title = wrapper.find('.card-header > .card-heading > h2.card-title');
        expect(title.text()).toBe('Users');
        expect(wrapper.attributes('aria-labelledby')).toBe(title.attributes('id'));
        expect(wrapper.find('.card-body > .content').exists()).toBe(true);
        expect(wrapper.find('.card-footer').exists()).toBe(false);
    });

    it('renders the description below the title', () => {
        const wrapper = mount(ContentCard, { props: { title: 'General', description: 'Basic settings.' } });

        expect(wrapper.find('.card-heading > .card-subtitle').text()).toBe('Basic settings.');
    });

    it('is a plain div without header when it has neither title nor actions', () => {
        const wrapper = mount(ContentCard, { slots: { default: () => h('p') } });

        expect(wrapper.element.tagName).toBe('DIV');
        expect(wrapper.find('.card-header').exists()).toBe(false);
        expect(wrapper.attributes('aria-labelledby')).toBeUndefined();
    });

    it('renders header content and the footer from their slots', () => {
        const wrapper = mount(ContentCard, {
            slots: {
                header: () => h('nav', { class: 'path' }),
                footer: () => h('span', { class: 'pager' }),
            },
        });

        expect(wrapper.find('.card-header > .path').exists()).toBe(true);
        expect(wrapper.find('.card-footer > .pager').exists()).toBe(true);
    });

    it('renders the header once header content is passed later', async () => {
        const shown = ref(false);
        const Host = defineComponent({
            setup: () => () => h(ContentCard, null, shown.value
                ? { header: () => h('nav', { class: 'views' }), default: () => h('p') }
                : { default: () => h('p') }),
        });
        const wrapper = mount(Host);
        expect(wrapper.find('.card-header').exists()).toBe(false);

        shown.value = true;
        await flushPromises();
        expect(wrapper.find('.card-header > .views').exists()).toBe(true);

        shown.value = false;
        await flushPromises();
        expect(wrapper.find('.card-header').exists()).toBe(false);
    });

    it('renders the actions with ghost tools and the primary action last', () => {
        const wrapper = mount(ContentCard, {
            props: {
                title: 'Users',
                actions: [
                    { id: 'add', icon: 'plus', label: 'Add user', url: '/add', primary: true },
                    { id: 'columns', icon: 'columns', label: 'Columns', url: '#' },
                ],
            },
        });

        const actions = wrapper.find('.card-header > .card-header-actions');
        expect(actions.findAll('a').map((a) => a.attributes('data-action-id'))).toEqual(['columns', 'add']);
        expect(actions.find('[data-action-id="columns"]').classes()).toEqual(expect.arrayContaining(['btn', 'btn-ghost', 'btn-icon-only']));
        expect(actions.find('[data-action-id="add"]').classes()).toEqual(expect.arrayContaining(['btn', 'btn-accent', 'btn-icon-only']));
    });
});
