import { describe, expect, it } from 'vitest';
import { h } from 'vue';
import { mount } from '@vue/test-utils';
import PathBar from '../../vue/PathBar.vue';

// PathBar reads `i18n` from `@humhub/vue` for the back button's and nav landmark's labels -
// needs the real humhub.vue.js module registered so the @humhub/vue shim (see
// support/humhubVueShim.mjs) has something to delegate to, mirroring dropZone.test.js's /
// selectionMenu.test.js's own setup for the same reason.
await import('../../resources/js/humhub/humhub.vue.js');

const path = [
    { id: 3, title: 'Brand', url: '/f?fid=3' },
    { id: 7, title: 'Logos', url: '/f?fid=7' },
];

const bar = (props = {}, slots = {}) => mount(PathBar, {
    props: { rootLabel: 'All files', rootUrl: '/f', path, ...props },
    slots,
    // Attached to the live document so a real (unprevented) click on an anchor bubbles as far
    // as `document`, where the modified-click test can intercept it - see dropZone.test.js for
    // the same reason.
    attachTo: document.body,
});

const drag = { dataTransfer: { types: ['text/plain'] } };

describe('PathBar', () => {
    it('renders the root, the ancestors as links and the current level as text', () => {
        const wrapper = bar();

        const root = wrapper.find('a.c-path-bar__root');
        expect(root.attributes('href')).toBe('/f');
        expect(root.attributes('aria-label')).toBe('All files');
        expect(root.find('.ti-folders').exists()).toBe(true);
        expect(wrapper.find('nav.c-path-bar__path').attributes('aria-label')).toBe('Breadcrumb');
        const link = wrapper.find('a.c-path-bar__link');
        expect(wrapper.findAll('a.c-path-bar__link').map((a) => a.text())).toEqual(['Brand']);
        expect(link.attributes('title')).toBe('Brand');
        const current = wrapper.find('.c-path-bar__current');
        expect(current.text()).toBe('Logos');
        expect(current.attributes('aria-current')).toBe('page');
        expect(current.attributes('title')).toBe('Logos');
        expect(wrapper.findAll('.ti-chevron-right')).toHaveLength(2);
    });

    it('marks the root as current at the top level, without a back button', () => {
        const wrapper = bar({ path: [] });

        expect(wrapper.find('a.c-path-bar__root').attributes('aria-current')).toBe('page');
        expect(wrapper.find('.c-path-bar__back').exists()).toBe(false);
    });

    it('navigates on a plain click and leaves modified clicks to the browser', async () => {
        const wrapper = bar();

        await wrapper.find('a.c-path-bar__link').trigger('click');
        await wrapper.find('a.c-path-bar__root').trigger('click');

        // The component must not call preventDefault() on a modified click, so jsdom tries to
        // follow the real href - block that at the document so it does not log "Not implemented:
        // navigation" noise; the component's own behavior (no preventDefault, no emit) is
        // unaffected and still asserted below.
        const block = (e) => e.preventDefault();
        document.addEventListener('click', block);
        await wrapper.find('a.c-path-bar__link').trigger('click', { ctrlKey: true });
        document.removeEventListener('click', block);

        expect(wrapper.emitted('navigate')).toEqual([[3], [null]]);
    });

    it('goes back to the parent level', async () => {
        const wrapper = bar();

        await wrapper.find('.c-path-bar__back').trigger('click');
        expect(wrapper.emitted('navigate')).toEqual([[3]]);

        const oneDeep = bar({ path: [path[0]] });
        await oneDeep.find('.c-path-bar__back').trigger('click');
        expect(oneDeep.emitted('navigate')).toEqual([[null]]);
    });

    it('renders the end slot', () => {
        const wrapper = bar({}, { end: () => h('button', { class: 'selection' }) });

        expect(wrapper.find('.c-path-bar > .selection').exists()).toBe(true);
    });

    describe('drop targets', () => {
        it('takes a drop on an ancestor it may drop on', async () => {
            const wrapper = bar({ canDrop: (id) => id === 3 });
            const brand = wrapper.findAll('.c-path-bar__crumb')[1];

            await brand.trigger('dragover', drag);
            await brand.trigger('drop', drag);

            expect(wrapper.emitted('drag-over')).toHaveLength(1);
            expect(wrapper.emitted('drag-over')[0][0]).toBe(3);
            expect(wrapper.emitted('drag-over')[0][1]).toBeInstanceOf(Event);
            expect(wrapper.emitted('drop-on')[0][0]).toBe(3);
        });

        it('refuses what canDrop refuses, and never the current level', async () => {
            const wrapper = bar({ canDrop: () => true });
            const [, , logos] = wrapper.findAll('.c-path-bar__crumb');

            await logos.trigger('dragover', drag);
            await logos.trigger('drop', drag);
            expect(wrapper.emitted('drop-on')).toBeUndefined();

            const refusing = bar({ canDrop: () => false });
            await refusing.findAll('.c-path-bar__crumb')[1].trigger('drop', drag);
            expect(refusing.emitted('drop-on')).toBeUndefined();
        });

        it('offers the root as a target below the top level', async () => {
            const wrapper = bar({ canDrop: () => true });

            await wrapper.findAll('.c-path-bar__crumb')[0].trigger('drop', drag);
            expect(wrapper.emitted('drop-on')[0][0]).toBe(null);
        });

        it('emits drag-leave only when the pointer leaves the crumb', async () => {
            const wrapper = bar({ canDrop: () => true });
            const brand = wrapper.findAll('.c-path-bar__crumb')[1];
            const link = brand.find('a').element;

            await brand.trigger('dragleave', { relatedTarget: link });
            expect(wrapper.emitted('drag-leave')).toBeUndefined();

            await brand.trigger('dragleave', { relatedTarget: document.body });
            expect(wrapper.emitted('drag-leave')).toHaveLength(1);
            expect(wrapper.emitted('drag-leave')[0][0]).toBe(3);
            expect(wrapper.emitted('drag-leave')[0][1]).toBeInstanceOf(Event);
        });

        it('highlights the target named by dropTargetId', () => {
            const wrapper = bar({ dropTargetId: 3 });

            expect(wrapper.findAll('.c-path-bar__crumb')[1].classes()).toContain('is-drop-target');
            expect(bar().findAll('.is-drop-target')).toHaveLength(0);

            // `null` (the root) must stay distinct from `undefined` (no target, the prop's
            // default) - a Vue prop typed to coerce `null` to the default would break this.
            const rootTargeted = bar({ dropTargetId: null });
            expect(rootTargeted.findAll('.c-path-bar__crumb')[0].classes()).toContain('is-drop-target');
        });
    });
});
