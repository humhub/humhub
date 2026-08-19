import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RichTextOutput from '../../modules/comment/vue/components/RichTextOutput.vue';
import LegacyFormWrapper from '../../modules/comment/vue/components/LegacyFormWrapper.vue';

// v-additions is registered per Vue *app* by humhub.vue.js's island mounter
// (`app.directive('additions', { mounted, updated })`), not globally on the
// Vue runtime — a bare @vue/test-utils mount() of an internal component
// (never itself an island root) needs its own stand-in. This mirrors that
// registration verbatim: both hooks call `additions.applyTo($(el))` via the
// same `ui.additions` module humhub.vue.js requires, which
// protected/humhub/tests/js/support/setup.mjs resolves to the spyable
// `humhubStubs.additions` stub — so spying on it here observes exactly what
// production wiring would call.
const additionsDirective = {
    mounted(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
    updated(el) {
        globalThis.humhubStubs.additions.applyTo(jQuery(el));
    },
};

const mountWithAdditions = (component, props) => mount(component, {
    props,
    global: { directives: { additions: additionsDirective } },
});

describe('RichTextOutput', () => {
    it('renders the output html when set', () => {
        const wrapper = mountWithAdditions(RichTextOutput, {
            output: '<div class="richtext-output" data-ui-markdown>**hi**</div>',
        });

        expect(wrapper.find('.richtext-output').exists()).toBe(true);
        expect(wrapper.html()).toContain('data-ui-markdown');
    });

    it('renders nothing when output is null', () => {
        const wrapper = mountWithAdditions(RichTextOutput, { output: null });

        expect(wrapper.find('div').exists()).toBe(false);
    });

    it('applies ui.additions to the rendered envelope on mount', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        const wrapper = mountWithAdditions(RichTextOutput, {
            output: '<div class="richtext-output">hi</div>',
        });

        expect(applyTo).toHaveBeenCalledTimes(1);
        expect(applyTo.mock.calls[0][0][0]).toBe(wrapper.element);

        applyTo.mockRestore();
    });

    it('does not apply ui.additions when there is nothing rendered', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        mountWithAdditions(RichTextOutput, { output: null });

        expect(applyTo).not.toHaveBeenCalled();

        applyTo.mockRestore();
    });

    it('re-applies ui.additions after the output prop changes (updated hook)', async () => {
        const wrapper = mountWithAdditions(RichTextOutput, {
            output: '<div class="richtext-output">before</div>',
        });

        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');
        await wrapper.setProps({ output: '<div class="richtext-output">after</div>' });

        expect(applyTo).toHaveBeenCalledTimes(1);
        expect(wrapper.text()).toBe('after');

        applyTo.mockRestore();
    });
});

describe('LegacyFormWrapper', () => {
    // Synthetic stand-in for the P2-6 contract: comment/widgets/views/form.php
    // rendering RichTextField + UploadButton + FilePreview with every id
    // declaration/reference replaced by the literal token `__VUEFORM__` —
    // including CSS-id-selector fragments embedded in `data-upload-*`
    // attributes, not just literal `id="..."` attributes.
    const buildShell = () => `
        <div id="comment_create_form___VUEFORM__" class="comment_create content_create" data-ui-widget="comment.Form">
            <form id="w0___VUEFORM__" action="/comment/comment/post" method="post">
                <input type="hidden" name="contentId" value="1">
                <div class="richtext-create-input-group input-group">
                    <div id="newCommentForm___VUEFORM__"
                         class="atwho-input form-control humhub-ui-richtext ProsemirrorEditor focusMenu"
                         data-ui-widget="ui.richtext.prosemirror.RichTextEditor" data-ui-init="1">Hello</div>
                    <textarea id="newCommentForm___VUEFORM___input" name="Comment[message]" style="display:none;"></textarea>
                    <div class="richtext-create-buttons">
                        <span class="btn btn-light fileinput-button" data-action-target="#comment_create_upload___VUEFORM__">
                            <input type="file" id="comment_create_upload___VUEFORM__" class="main_comment_upload" multiple
                                   data-ui-widget="file.Upload" data-ui-init="1"
                                   data-upload-drop-zone="#comment_create_form___VUEFORM__"
                                   data-upload-preview="#comment_create_upload_preview___VUEFORM__"
                                   data-upload-progress="#comment_create_upload_progress___VUEFORM__"
                                   data-upload-submit-name="Comment[fileList][]">
                            <input type="hidden" name="Comment[fileList][]" value="guid-1">
                            <input type="hidden" name="Comment[fileList][]" value="guid-2">
                        </span>
                    </div>
                </div>
                <div id="comment_create_upload_progress___VUEFORM__" style="display:none;"></div>
                <div id="comment_create_upload_preview___VUEFORM__"></div>
            </form>
        </div>
    `;

    describe('token replacement', () => {
        it('replaces every occurrence of __VUEFORM__ with the same unique id', () => {
            const wrapper = mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });

            expect(wrapper.html()).not.toContain('__VUEFORM__');

            // The component's own template root (`<div v-html v-additions>`)
            // never carries an id itself — the shell's root `.comment_create`
            // div is a v-html'd CHILD of it.
            const rootId = wrapper.find('.comment_create').attributes('id');
            expect(rootId).toMatch(/^comment_create_form_vueform-\d+$/);
            const suffix = rootId.slice('comment_create_form_'.length);

            expect(wrapper.find('form').attributes('id')).toBe('w0_' + suffix);
            expect(wrapper.find('.humhub-ui-richtext').attributes('id')).toBe('newCommentForm_' + suffix);
            expect(wrapper.find('textarea').attributes('id')).toBe('newCommentForm_' + suffix + '_input');
            expect(wrapper.find('input[type="file"]').attributes('id')).toBe('comment_create_upload_' + suffix);
            expect(wrapper.find('input[type="file"]').attributes('data-upload-drop-zone')).toBe('#' + rootId);
            expect(wrapper.find('input[type="file"]').attributes('data-upload-preview'))
                .toBe('#comment_create_upload_preview_' + suffix);
            expect(wrapper.find('input[type="file"]').attributes('data-upload-progress'))
                .toBe('#comment_create_upload_progress_' + suffix);
        });

        it('gives two mounted instances different ids', () => {
            const shellHtml = buildShell();
            const wrapperA = mountWithAdditions(LegacyFormWrapper, { shellHtml });
            const wrapperB = mountWithAdditions(LegacyFormWrapper, { shellHtml });

            expect(wrapperA.find('.comment_create').attributes('id'))
                .not.toBe(wrapperB.find('.comment_create').attributes('id'));
            expect(wrapperA.find('.humhub-ui-richtext').attributes('id'))
                .not.toBe(wrapperB.find('.humhub-ui-richtext').attributes('id'));
        });
    });

    it('applies ui.additions to the shell on mount', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        const wrapper = mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });

        expect(applyTo).toHaveBeenCalledTimes(1);
        expect(applyTo.mock.calls[0][0][0]).toBe(wrapper.element);

        applyTo.mockRestore();
    });

    describe('editor + upload interop', () => {
        // Fakes mirror the REAL widget API surfaces this wrapper relies on:
        //  - ui.richtext.prosemirror.RichTextEditor: `.editor.serialize()`,
        //    `.editor.init(markdown)`, `.focus()`, and the `.$` node the
        //    widget's own 'clear' DOM-event handler is bound to.
        //  - file.Upload: `.options.uploadSubmitName` and `.reset()`.
        // Attached the same way the real widgets attach themselves once
        // v-additions boots them — jQuery `.data(<ComponentClass.component>,
        // instance)` on their own root node (see humhub.action.js
        // Component.prototype.init) — NOT under a fixed 'humhub-widget' key;
        // that key is `Widget.componentData` in humhub.ui.widget.js, which is
        // dead code nothing ever reads (the real cache key is each widget
        // class's own `.component` static).
        let wrapper;
        let fakeEditor;
        let fakeUpload;

        beforeEach(() => {
            wrapper = mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });

            const $richtext = jQuery(wrapper.find('.humhub-ui-richtext').element);
            fakeEditor = {
                editor: {
                    serialize: vi.fn(() => 'current **markdown**'),
                    init: vi.fn(),
                },
                $: $richtext,
                focus: vi.fn(),
            };
            $richtext.data('humhub-ui-richtexteditor', fakeEditor);

            fakeUpload = {
                options: { uploadSubmitName: 'Comment[fileList][]' },
                reset: vi.fn(),
            };
            jQuery(wrapper.find('input[type="file"]').element).data('humhub-file-upload', fakeUpload);
        });

        it('getValue() reads the current markdown via editor.serialize()', () => {
            expect(wrapper.vm.getValue()).toBe('current **markdown**');
        });

        it('returns an empty string from getValue() when the editor widget is not booted yet', () => {
            const freshWrapper = mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });
            expect(freshWrapper.vm.getValue()).toBe('');
        });

        it('setValue() re-primes the editor via editor.init(), the same call legacy reply-mention priming uses', () => {
            wrapper.vm.setValue('new **value**');

            expect(fakeEditor.editor.init).toHaveBeenCalledWith('new **value**');
        });

        it('focus() delegates to the editor widget focus()', () => {
            wrapper.vm.focus();

            expect(fakeEditor.focus).toHaveBeenCalledTimes(1);
        });

        it('clear() triggers the editor\'s "clear" event and resets the upload widget', () => {
            const clearHandler = vi.fn();
            fakeEditor.$.on('clear', clearHandler);

            wrapper.vm.clear();

            expect(clearHandler).toHaveBeenCalledTimes(1);
            expect(fakeUpload.reset).toHaveBeenCalledTimes(1);
        });

        it('getFileGuids() reads guids from hidden inputs named after uploadSubmitName', () => {
            expect(wrapper.vm.getFileGuids()).toEqual(['guid-1', 'guid-2']);
        });

        it('getFileGuids() returns an empty array when the upload widget is not booted yet', () => {
            const freshWrapper = mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });
            expect(freshWrapper.vm.getFileGuids()).toEqual([]);
        });
    });
});
