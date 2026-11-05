import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RichTextField from '../../vue/RichTextField.vue';

// The upload reaches the platform client through `@humhub/vue`, which delegates to the real
// humhub.vue.js module (see support/humhubVueShim.mjs).
await import('../../resources/js/humhub/humhub.vue.js');

const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';

// Stands in for humhub.vue.js's per-app `v-additions` (see coreInterop.test.js).
const additionsDirective = {
    mounted(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
    updated(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
};

// What booting the real `ui.richtext.prosemirror.RichTextEditor` leaves behind: its instance
// cached on its node, with the editor's serialize()/init() and the 'clear' event handler.
const bootFakeEditor = (node) => {
    const fake = { value: '', $: jQuery(node), focus: vi.fn(), backup: vi.fn() };
    fake.editor = {
        serialize: () => fake.value,
        init: vi.fn((markdown) => { fake.value = markdown; }),
        destroy: vi.fn(),
    };
    fake.$.on('clear', () => { fake.value = ''; });
    fake.$.data('humhub-ui-richtexteditor', fake);
    return fake;
};

const field = (props = {}) => mount(RichTextField, {
    props: { attribute: 'message', ...props },
    global: { directives: { additions: additionsDirective } },
    attachTo: document.body,
});

const editorNode = (wrapper) => wrapper.find(RICHTEXT_SELECTOR).element;
const editorOf = (wrapper) => jQuery(editorNode(wrapper)).data('humhub-ui-richtexteditor');

describe('RichTextField', () => {
    beforeEach(() => {
        globalThis.humhubStubs.additions.register('test-richtext-boot', RICHTEXT_SELECTOR, ($match) => {
            $match.each(function () {
                if (!jQuery(this).data('humhub-ui-richtexteditor')) {
                    bootFakeEditor(this);
                }
            });
        });
    });

    afterEach(() => {
        vi.useRealTimers();
        document.body.replaceChildren();
    });

    it('renders the editor\'s mount element with the PHP widget\'s data attributes, no shell', () => {
        const wrapper = field({
            instanceKey: 'c42',
            layout: 'inline',
            placeholder: 'Write…',
            mentioningUrl: '/mentioning?id=42',
            pluginOptions: { maxHeight: '300px' },
            exclude: ['emoji'],
        });
        const node = editorNode(wrapper);

        expect(node.id).toBe('richtext-c42');
        expect(node.classList.contains('ProsemirrorEditor')).toBe(true);
        expect(node.classList.contains('focusMenu')).toBe(true);
        expect(node.hasAttribute('data-ui-init')).toBe(true);
        expect(node.dataset.mentioningUrl).toBe('/mentioning?id=42');
        expect(node.dataset.placeholder).toBe('Write…');
        expect(JSON.parse(node.dataset.pluginOptions)).toEqual({ maxHeight: '300px' });
        // The editor's upload and file handlers are left out unless the field is given them
        expect(JSON.parse(node.dataset.exclude)).toEqual(['emoji', 'upload', 'file_handler']);
        // The editor's input, which keys its draft backup
        expect(wrapper.find('textarea#richtext-c42_input').exists()).toBe(true);
        expect(wrapper.find('form').exists()).toBe(false);
    });

    it('leaves out the resize handle in block layout, as the PHP widget does', () => {
        const node = editorNode(field({ upload: true }));

        expect(node.classList.contains('plainMenu')).toBe(true);
        expect(JSON.parse(node.dataset.exclude)).toEqual(['resizeNav', 'file_handler']);
    });

    it('reads, sets and clears the editor\'s markdown and focuses it', () => {
        const wrapper = field();

        wrapper.vm.setValue('**bold**');
        expect(wrapper.vm.getValue()).toBe('**bold**');

        wrapper.vm.clear();
        expect(wrapper.vm.getValue()).toBe('');

        wrapper.vm.focus();
        expect(editorOf(wrapper).focus).toHaveBeenCalledTimes(1);
    });

    it('backs up the draft on its own timer, and stops it and the editor when it unmounts', () => {
        vi.useFakeTimers();
        const wrapper = field({ instanceKey: 'c42', backupInterval: 2 });
        const editor = editorOf(wrapper);

        vi.advanceTimersByTime(4000);
        expect(editor.backup).toHaveBeenCalledTimes(2);

        wrapper.unmount();
        vi.advanceTimersByTime(4000);
        expect(editor.backup).toHaveBeenCalledTimes(2);
        expect(editor.editor.destroy).toHaveBeenCalledTimes(1);
    });

    it('keeps no backup without an instance key', () => {
        vi.useFakeTimers();
        const wrapper = field();

        vi.advanceTimersByTime(10000);
        expect(editorOf(wrapper).backup).not.toHaveBeenCalled();
    });

    describe('files in the text', () => {
        const uploadOptionOf = (wrapper) => jQuery(editorNode(wrapper)).data('upload');
        const file = () => new File(['x'], 'a.png', { type: 'image/png' });

        it('hands the editor an upload option before it boots, only when given one', () => {
            expect(typeof uploadOptionOf(field({ upload: true }))).toBe('function');
            expect(uploadOptionOf(field())).toBeUndefined();
        });

        it('uploads pasted files through the API and resolves with the uploaded ones', async () => {
            const uploaded = { guid: 'g1', fileName: 'a.png', url: '/f/g1', mimeIcon: 'mime-image' };
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ results: [uploaded], errors: [] }));

            const files = await uploadOptionOf(field({ upload: true }))([file()]);

            expect(globalThis.humhubStubs.client.post.mock.calls[0][0]).toContain('/api/v2/file');
            expect(files).toEqual([uploaded]);
        });

        it('uploads through a function of its own when given one', async () => {
            const upload = vi.fn(() => Promise.resolve(['done']));

            expect(await uploadOptionOf(field({ upload }))([file()])).toEqual(['done']);
            expect(upload).toHaveBeenCalledTimes(1);
        });

        it('opens the picker from the menu, and resolves with nothing when it is cancelled', async () => {
            const upload = vi.fn();
            const wrapper = field({ upload });
            const picker = wrapper.find('input[type="file"]').element;
            const click = vi.spyOn(picker, 'click').mockImplementation(() => {});

            const pending = uploadOptionOf(wrapper)(null, { accept: 'image/*' });
            await wrapper.vm.$nextTick();
            expect(click).toHaveBeenCalledTimes(1);
            expect(picker.getAttribute('accept')).toBe('image/*');
            picker.dispatchEvent(new Event('cancel'));

            expect(await pending).toEqual([]);
            expect(upload).not.toHaveBeenCalled();
        });
    });

    it('offers the file handlers in the editor\'s menu, only when given some', () => {
        const handlers = [{ label: 'New text', icon: 'file-text', attributes: { 'data-action-click': 'x' } }];
        const wrapper = field({ handlers });
        const container = jQuery(editorNode(wrapper)).data('fileHandlers')();

        expect(container.classList.contains('d-none')).toBe(true);
        expect(container.querySelector('a[data-action-process="file-handler"]').textContent).toBe('New text');
        expect(JSON.parse(editorNode(wrapper).dataset.exclude)).not.toContain('file_handler');
        expect(JSON.parse(editorNode(field()).dataset.exclude)).toContain('file_handler');
    });

    it('reports the editor\'s scroll state', () => {
        const wrapper = field();

        jQuery(editorNode(wrapper)).trigger('scrollActive');
        jQuery(editorNode(wrapper)).trigger('scrollInactive');

        expect(wrapper.emitted('scroll-active')).toHaveLength(1);
        expect(wrapper.emitted('scroll-inactive')).toHaveLength(1);
    });
});
