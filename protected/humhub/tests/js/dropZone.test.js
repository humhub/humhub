import { describe, expect, it } from 'vitest';
import { h } from 'vue';
import { mount } from '@vue/test-utils';
import DropZone from '../../vue/DropZone.vue';

// DropZone reads `i18n` from `@humhub/vue` for its default overlay labels - needs the real
// humhub.vue.js module registered so the @humhub/vue shim (see support/humhubVueShim.mjs) has
// something to delegate to, mirroring userImage.test.js's / selectionMenu.test.js's own setup
// for the same reason.
await import('../../resources/js/humhub/humhub.vue.js');

const files = [new File(['x'], 'a.txt')];
const fileDrag = { dataTransfer: { types: ['Files'], files, dropEffect: 'none' } };
const textDrag = { dataTransfer: { types: ['text/plain'], files: [], dropEffect: 'none' } };

const zone = (props = {}) => mount(DropZone, {
    props,
    slots: { default: () => h('div', { class: 'inner' }, [h('span', { class: 'child' })]) },
    attachTo: document.body,
});

describe('DropZone', () => {
    it('shows the overlay while files are dragged over it', async () => {
        const wrapper = zone({ label: 'Drop to upload' });

        await wrapper.trigger('dragenter', fileDrag);
        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(true);
        expect(wrapper.find('.c-drop-zone__label').text()).toBe('Drop to upload');
        expect(wrapper.find('.ti-upload').exists()).toBe(true);

        await wrapper.trigger('dragleave', fileDrag);
        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(false);
    });

    it('keeps the overlay while the drag moves between its children', async () => {
        const wrapper = zone();

        await wrapper.trigger('dragenter', fileDrag);
        await wrapper.find('.child').trigger('dragenter', fileDrag);
        await wrapper.trigger('dragleave', fileDrag);
        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(true);

        await wrapper.find('.child').trigger('dragleave', fileDrag);
        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(false);
    });

    it('resets when the drag leaves the zone, even if a child never reported leaving', async () => {
        const wrapper = zone();

        await wrapper.trigger('dragenter', fileDrag);
        await wrapper.find('.child').trigger('dragenter', fileDrag);
        await wrapper.trigger('dragleave', { ...fileDrag, relatedTarget: document.body });

        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(false);
    });

    it('ignores drags that carry no files', async () => {
        const wrapper = zone();

        await wrapper.trigger('dragenter', textDrag);
        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(false);
    });

    it('emits the dropped files and clears the overlay', async () => {
        const wrapper = zone();

        await wrapper.trigger('dragenter', fileDrag);
        await wrapper.trigger('drop', fileDrag);

        expect(wrapper.emitted('drop')[0][0]).toBe(files);
        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(false);
    });

    it('refuses a drop it does not accept, visibly', async () => {
        const wrapper = zone({ accept: false, refusedLabel: 'Not allowed here' });

        await wrapper.trigger('dragenter', fileDrag);
        expect(wrapper.find('.c-drop-zone__overlay').classes()).toContain('is-refused');
        expect(wrapper.find('.ti-ban').exists()).toBe(true);
        expect(wrapper.find('.c-drop-zone__label').text()).toBe('Not allowed here');

        await wrapper.trigger('drop', fileDrag);
        expect(wrapper.emitted('drop')).toBeUndefined();
    });

    // A child handling a drop itself (a folder tile) stops it; the overlay must clear anyway.
    it('clears the overlay when a child stops the drop, without emitting', async () => {
        const wrapper = zone();
        wrapper.find('.child').element.addEventListener('drop', (event) => event.stopPropagation());

        await wrapper.trigger('dragenter', fileDrag);
        await wrapper.find('.child').trigger('drop', fileDrag);

        expect(wrapper.find('.c-drop-zone__overlay').exists()).toBe(false);
        expect(wrapper.emitted('drop')).toBeUndefined();
    });
});
