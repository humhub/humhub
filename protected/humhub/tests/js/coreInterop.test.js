import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RichTextOutput from '../../vue/RichTextOutput.vue';
import LegacyFormWrapper from '../../vue/LegacyFormWrapper.vue';

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
    it('renders nothing when message is null (e.g. a blocked comment)', () => {
        const wrapper = mountWithAdditions(RichTextOutput, { message: null });

        expect(wrapper.find('div').exists()).toBe(false);
    });

    it('renders nothing when message is an empty string', () => {
        const wrapper = mountWithAdditions(RichTextOutput, { message: '' });

        expect(wrapper.find('div').exists()).toBe(false);
    });

    describe('envelope construction (data-* attrs mirroring RichText::output())', () => {
        it('renders the markdown text as a direct child div carrying the render-options as data-* attrs', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: 'hello world',
                renderOptions: {
                    exclude: [],
                    include: [],
                    'plugin-options': [],
                    edit: false,
                    'ui-richtext': true,
                    'ui-widget': 'ui.richtext.prosemirror.RichText',
                    'ui-init': true,
                },
            });

            // Root is RichTextOutput's own element (receives attribute fallthrough from the
            // caller - class/data-ui-markdown/etc, see the "attribute fallthrough" describe
            // block below); the envelope is a direct CHILD, not the root itself, matching
            // today's DOM shape one level up (v-html'd envelope nested inside the caller's
            // own class="comment-message" root) - see commentSection.test.js's own
            // "flattened RichTextOutput wrapper" regression test.
            expect(wrapper.element.children.length).toBe(1);
            const envelope = wrapper.element.children[0];

            expect(envelope.textContent).toBe('hello world');
            expect(envelope.getAttribute('data-exclude')).toBe('[]');
            expect(envelope.getAttribute('data-include')).toBe('[]');
            expect(envelope.getAttribute('data-plugin-options')).toBe('[]');
            expect(envelope.getAttribute('data-ui-widget')).toBe('ui.richtext.prosemirror.RichText');
            // Yii's own Html::renderTagAttributes() convention (boolean true -> valueless
            // attribute, boolean false -> omitted entirely) - v-bind reproduces it natively.
            expect(envelope.hasAttribute('data-ui-richtext')).toBe(true);
            expect(envelope.getAttribute('data-ui-richtext')).toBe('');
            expect(envelope.hasAttribute('data-ui-init')).toBe(true);
            expect(envelope.hasAttribute('data-edit')).toBe(false);
        });

        it('omits an absent preset entirely, matching getData()\'s own conditional inclusion', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: 'hi',
                renderOptions: { 'ui-richtext': true },
            });

            expect(wrapper.element.children[0].hasAttribute('data-preset')).toBe(false);
        });

        it('renders a provided preset as a plain string attribute', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: 'hi',
                renderOptions: { 'ui-richtext': true, preset: 'myPreset' },
            });

            expect(wrapper.element.children[0].getAttribute('data-preset')).toBe('myPreset');
        });

        it('defaults to an empty render-options object when none is provided', () => {
            const wrapper = mountWithAdditions(RichTextOutput, { message: 'hi' });

            const envelope = wrapper.element.children[0];
            expect(envelope.textContent).toBe('hi');
            expect(envelope.attributes.length).toBe(0);
        });
    });

    describe('XSS: message is text, never raw HTML', () => {
        it('renders a literal <script> tag as inert escaped text, never as a real element', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: '<script>window.__pwned = true;</script>',
                renderOptions: { 'ui-richtext': true },
            });

            expect(wrapper.find('script').exists()).toBe(false);
            expect(globalThis.__pwned).toBeUndefined();
            expect(wrapper.element.children[0].textContent).toBe('<script>window.__pwned = true;</script>');
        });

        it('never uses v-html for the message itself (only trusted oembed fragments may)', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: '<img src=x onerror="window.__pwned = true">',
                renderOptions: { 'ui-richtext': true },
            });

            expect(wrapper.find('img').exists()).toBe(false);
            expect(globalThis.__pwned).toBeUndefined();
        });
    });

    describe('oembed previews (options.oembeds)', () => {
        it('renders no oembed container when there are no oembeds', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: 'hi',
                renderOptions: { 'ui-richtext': true },
            });

            expect(wrapper.find('.richtext-oembed-container').exists()).toBe(false);
        });

        it('rebuilds the hidden .richtext-oembed-container sibling from the oembeds map, keyed by url', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: '[https://example.com/v](oembed:https://example.com/v)',
                renderOptions: {
                    'ui-richtext': true,
                    oembeds: { 'https://example.com/v': '<iframe src="https://example.com/embed"></iframe>' },
                },
            });

            const container = wrapper.find('.richtext-oembed-container');
            expect(container.exists()).toBe(true);
            expect(container.attributes('style')).toContain('display: none');

            const fragment = container.find('[data-oembed="https://example.com/v"]');
            expect(fragment.exists()).toBe(true);
            // Trusted, server-fetched oembed markup - the one deliberate v-html use in this
            // component (see its own docblock on the trust boundary).
            expect(fragment.find('iframe').attributes('src')).toBe('https://example.com/embed');

            // Sibling of the envelope, both direct children of RichTextOutput's own root -
            // matches today's DOM shape (both were part of the same server-rendered string).
            expect(wrapper.element.children.length).toBe(2);
        });

        it('renders one fragment per oembed url', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: 'two links',
                renderOptions: {
                    'ui-richtext': true,
                    oembeds: {
                        'https://example.com/a': '<div>a</div>',
                        'https://example.com/b': '<div>b</div>',
                    },
                },
            });

            expect(wrapper.findAll('.richtext-oembed-container > [data-oembed]').length).toBe(2);
        });

        it('does not leak the oembeds map itself onto the envelope as a data-oembeds attribute', () => {
            const wrapper = mountWithAdditions(RichTextOutput, {
                message: 'hi',
                renderOptions: { 'ui-richtext': true, oembeds: { 'https://example.com/v': '<div></div>' } },
            });

            expect(wrapper.element.children[0].hasAttribute('data-oembeds')).toBe(false);
        });
    });

    describe('attribute fallthrough (caller-owned layout/styling)', () => {
        it('lands class/data-ui-markdown/data-ui-show-more on the root, one level above the envelope', () => {
            const wrapper = mount(RichTextOutput, {
                props: { message: 'hi', renderOptions: { 'ui-richtext': true } },
                attrs: { class: 'comment-message', 'data-ui-markdown': '', 'data-ui-show-more': '', 'data-read-more-text': 'Read more...' },
                global: { directives: { additions: additionsDirective } },
            });

            expect(wrapper.classes()).toContain('comment-message');
            expect(wrapper.attributes('data-ui-markdown')).toBe('');
            expect(wrapper.attributes('data-read-more-text')).toBe('Read more...');
            // Not duplicated onto the inner envelope.
            expect(wrapper.element.children[0].hasAttribute('data-ui-markdown')).toBe(false);
        });
    });

    it('applies ui.additions to the rendered envelope on mount', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        const wrapper = mountWithAdditions(RichTextOutput, {
            message: 'hi',
            renderOptions: { 'ui-richtext': true },
        });

        expect(applyTo).toHaveBeenCalledTimes(1);
        expect(applyTo.mock.calls[0][0][0]).toBe(wrapper.element);

        applyTo.mockRestore();
    });

    it('does not apply ui.additions when there is nothing rendered', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        mountWithAdditions(RichTextOutput, { message: null });

        expect(applyTo).not.toHaveBeenCalled();

        applyTo.mockRestore();
    });

    it('re-applies ui.additions after the message prop changes (updated hook)', async () => {
        const wrapper = mountWithAdditions(RichTextOutput, {
            message: 'before',
            renderOptions: { 'ui-richtext': true },
        });

        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');
        await wrapper.setProps({ message: 'after' });

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

        // P2-7 fix: humhub.client.js's acknowledgeForm guard (armed by
        // `ActiveForm::begin(['acknowledge' => true])`, see this component's own
        // "Unsaved-changes guard" docblock section) never learns a submit happened
        // here (no native form submit, no `[type=submit]` click) — clear() must
        // reset its `$form.data('state')` baseline itself, via the same public
        // jQuery `.data()` store `humhub.client.js`'s `formStateChanged()` reads.
        describe('unsaved-changes guard reset', () => {
            let form;

            beforeEach(() => {
                form = wrapper.find('form').element;
                // Simulates onBeforeLoad()'s own baseline capture at boot time.
                jQuery(form).data('state', 'contentId=1&message=old+draft');
            });

            it('clear() also resets the acknowledgeForm baseline', () => {
                wrapper.vm.clear();

                expect(jQuery(form).data('state')).toBeNull();
            });

            it('resetAcknowledge() resets the baseline without touching editor/upload content', () => {
                wrapper.vm.resetAcknowledge();

                expect(jQuery(form).data('state')).toBeNull();
                expect(fakeEditor.editor.init).not.toHaveBeenCalled();
                expect(fakeUpload.reset).not.toHaveBeenCalled();
            });
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
