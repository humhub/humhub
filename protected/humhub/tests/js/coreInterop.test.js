import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RichTextOutput from '../../vue/RichTextOutput.vue';
import LegacyFormWrapper from '../../vue/LegacyFormWrapper.vue';

// LegacyFormWrapper's checkFormPresence() safety net (see its own docblock, "Nested
// <form> via v-html") reads `log` from `@humhub/vue` - needs the real humhub.vue.js
// module registered so the shim (support/humhubVueShim.mjs) has something to delegate
// to, mirroring humhubForm.test.js's own setup for the same reason.
await import('../../resources/js/humhub/humhub.vue.js');

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

        // humhub.oembed.js's findSnippetByUrl() looks up this fragment via
        // `[data-oembed="' + $.escapeSelector(util.string.escapeHtml(url, true)) + '"]` - the
        // legacy server markup produced that exact escaped string in the DOM attribute (source
        // double-encodes via Html::encode($url) + Html::tag()'s own attribute encoding, which a
        // single HTML-parse round-trip collapses back down to one escape). Binding the RAW url
        // here would silently break the lookup for any url containing one of & < > " '.
        describe('data-oembed carries the HTML-escaped url, matching humhub.oembed.js\'s lookup', () => {
            it('escapes an & in the url (e.g. a query string) before binding data-oembed', () => {
                const rawUrl = 'https://youtube.com/watch?v=abc&t=30s';
                const escapedUrl = 'https://youtube.com/watch?v=abc&amp;t=30s';

                const wrapper = mountWithAdditions(RichTextOutput, {
                    message: '[link](oembed:' + rawUrl + ')',
                    renderOptions: {
                        'ui-richtext': true,
                        oembeds: { [rawUrl]: '<iframe src="https://youtube.com/embed/abc"></iframe>' },
                    },
                });

                const fragment = wrapper.find('.richtext-oembed-container > [data-oembed]');
                expect(fragment.exists()).toBe(true);
                expect(fragment.element.getAttribute('data-oembed')).toBe(escapedUrl);
                expect(fragment.find('iframe').attributes('src')).toBe('https://youtube.com/embed/abc');
            });

            it('escapes quotes and angle brackets in the url the same way', () => {
                const rawUrl = 'https://example.com/a"b<c>d\'e';
                const escapedUrl = 'https://example.com/a&quot;b&lt;c&gt;d&#39;e';

                const wrapper = mountWithAdditions(RichTextOutput, {
                    message: 'weird url',
                    renderOptions: {
                        'ui-richtext': true,
                        oembeds: { [rawUrl]: '<div class="preview">p</div>' },
                    },
                });

                const fragment = wrapper.find('.richtext-oembed-container > [data-oembed]');
                expect(fragment.exists()).toBe(true);
                expect(fragment.element.getAttribute('data-oembed')).toBe(escapedUrl);
                expect(fragment.find('.preview').exists()).toBe(true);
            });
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

    // The legacy richtext DISPLAY addition caches its booted widget instance in jQuery
    // `.data()` on the envelope DOM node itself; widget init is a once-per-node guard, so an
    // in-place text swap on the SAME node (Vue's default behavior for a same-position,
    // same-tag child with no key) would leave that cache in place and the addition would never
    // re-render the new markdown - the user would see raw markdown text. The `:key` on the
    // envelope (see the component's own docblock) forces Vue to destroy the old node and mount
    // a genuinely new one instead.
    it('recreates the envelope element itself (not just its text) when the message changes', async () => {
        const wrapper = mountWithAdditions(RichTextOutput, {
            message: 'first markdown',
            renderOptions: { 'ui-richtext': true },
        });

        const oldEnvelope = wrapper.element.children[0];

        await wrapper.setProps({ message: 'second markdown' });

        const newEnvelope = wrapper.element.children[0];

        expect(newEnvelope).not.toBe(oldEnvelope);
        // The old node was actually removed from the tree, not just superseded in our local
        // reference to it.
        expect(oldEnvelope.parentNode).toBeNull();
        expect(newEnvelope.textContent).toBe('second markdown');
    });

    it('also recreates the envelope element when only renderOptions changes (message unchanged)', async () => {
        const wrapper = mountWithAdditions(RichTextOutput, {
            message: 'same markdown',
            renderOptions: { 'ui-richtext': true, preset: 'a' },
        });

        const oldEnvelope = wrapper.element.children[0];

        await wrapper.setProps({ renderOptions: { 'ui-richtext': true, preset: 'b' } });

        const newEnvelope = wrapper.element.children[0];

        expect(newEnvelope).not.toBe(oldEnvelope);
        expect(newEnvelope.getAttribute('data-preset')).toBe('b');
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
                    <div class="richtext-create-buttons"></div>
                </div>
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

        // The counter fallback above is only unique per page load — features
        // keyed off these ids in storage that outlives the page (the richtext
        // editor's sessionStorage draft backup keys off the hidden input's id)
        // need the caller-supplied instanceKey instead: deterministic, so the
        // SAME logical form gets the SAME ids on every page it is mounted on.
        // See the class docblock's "Unique-id contract" section.
        it('derives a deterministic id from instanceKey, stable across mounts', () => {
            const shellHtml = buildShell();
            const wrapperA = mountWithAdditions(LegacyFormWrapper, { shellHtml, instanceKey: 'c42-e7' });

            expect(wrapperA.find('.comment_create').attributes('id')).toBe('comment_create_form_vueform-c42-e7');
            expect(wrapperA.find('textarea').attributes('id')).toBe('newCommentForm_vueform-c42-e7_input');

            // Stability contract: a remount of the same logical form (e.g. the
            // same content's create form after a pjax navigation) reproduces
            // the exact same ids — that is what keys the draft backup right.
            wrapperA.unmount();
            const wrapperB = mountWithAdditions(LegacyFormWrapper, { shellHtml, instanceKey: 'c42-e7' });
            expect(wrapperB.find('.comment_create').attributes('id')).toBe('comment_create_form_vueform-c42-e7');
        });

        it('sanitizes instanceKey to characters safe inside the shell\'s CSS-id-selector references', () => {
            const wrapper = mountWithAdditions(LegacyFormWrapper, {
                shellHtml: buildShell(),
                instanceKey: 'c42:e/7 x',
            });

            expect(wrapper.find('.comment_create').attributes('id')).toBe('comment_create_form_vueform-c42-e-7-x');
        });
    });

    it('applies ui.additions to the shell on mount', () => {
        const applyTo = vi.spyOn(globalThis.humhubStubs.additions, 'applyTo');

        const wrapper = mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });

        expect(applyTo).toHaveBeenCalledTimes(1);
        expect(applyTo.mock.calls[0][0][0]).toBe(wrapper.element);

        applyTo.mockRestore();
    });

    describe('checkFormPresence() safety net', () => {
        beforeEach(() => {
            globalThis.humhubStubs.logCalls.error.length = 0;
        });

        it('logs nothing when the shell has a <form> and it is present in the DOM', () => {
            mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });
            expect(globalThis.humhubStubs.logCalls.error).toHaveLength(0);
        });

        it('logs an error when the shell was supposed to have a <form> but none is found in the DOM', async () => {
            // Simulates the failure mode itself (a later re-render dropping the inner
            // <form> because $el is by then attached — see the docblock) rather than
            // trying to reproduce the exact browser timing: stub querySelector('form')
            // to report "not found" the way it would if the parser had actually
            // dropped the tag, then force the same re-render check the real 'updated'
            // hook runs.
            const wrapper = mountWithAdditions(LegacyFormWrapper, { shellHtml: buildShell() });
            expect(globalThis.humhubStubs.logCalls.error).toHaveLength(0); // clean on initial mount

            vi.spyOn(wrapper.vm.$el, 'querySelector').mockReturnValue(null);
            wrapper.vm.checkFormPresence();

            expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
            expect(globalThis.humhubStubs.logCalls.error[0][0]).toContain('LegacyFormWrapper');
        });

        it('logs nothing for a shell that never claimed to have a <form> in the first place', () => {
            mountWithAdditions(LegacyFormWrapper, { shellHtml: '<div id="__VUEFORM__">no form here</div>' });
            expect(globalThis.humhubStubs.logCalls.error).toHaveLength(0);
        });
    });

    describe('editor interop', () => {
        // Fakes mirror the REAL widget API surfaces this wrapper relies on:
        //  - ui.richtext.prosemirror.RichTextEditor: `.editor.serialize()`,
        //    `.editor.init(markdown)`, `.focus()`, and the `.$` node the
        //    widget's own 'clear' DOM-event handler is bound to.
        // Attached the same way the real widgets attach themselves once
        // v-additions boots them — jQuery `.data(<ComponentClass.component>,
        // instance)` on their own root node (see humhub.action.js
        // Component.prototype.init) — NOT under a fixed 'humhub-widget' key;
        // that key is `Widget.componentData` in humhub.ui.widget.js, which is
        // dead code nothing ever reads (the real cache key is each widget
        // class's own `.component` static).
        let wrapper;
        let fakeEditor;

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

        it('clear() triggers the editor\'s "clear" event', () => {
            const clearHandler = vi.fn();
            fakeEditor.$.on('clear', clearHandler);

            wrapper.vm.clear();

            expect(clearHandler).toHaveBeenCalledTimes(1);
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

            it('resetAcknowledge() resets the baseline without touching the editor content', () => {
                wrapper.vm.resetAcknowledge();

                expect(jQuery(form).data('state')).toBeNull();
                expect(fakeEditor.editor.init).not.toHaveBeenCalled();
            });
        });

        it('exposes no file API anymore - uploads are a field of their own (UploadField)', () => {
            expect(wrapper.vm.getFileGuids).toBeUndefined();
        });
    });
});
