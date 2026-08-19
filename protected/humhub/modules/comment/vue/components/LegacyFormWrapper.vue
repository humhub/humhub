<template>
    <div v-html="processedShell" v-additions></div>
</template>

<script>
/**
 * Hosts the server-rendered comment form SHELL (RichTextField + UploadButton
 * + FilePreview markup, see comment/widgets/views/form.php) inside a Vue
 * island and exposes a small, clean API to the surrounding Vue form
 * (CommentForm, P2-4/5) so it never has to touch jQuery/legacy widgets
 * itself.
 *
 * ## Unique-id contract (binding for P2-6)
 *
 * A comment page can host many instances of this shell at once (the main
 * form, one open reply form per commented-on entry, an edit form) that are
 * all clones of the SAME server-rendered template (`formShellHtml` in the
 * initial props) — the shell is fetched/rendered once, not once per form.
 * form.php's markup bakes element ids into itself (the RichTextField's
 * `id`/`id + '_input'`, the UploadButton input's `id`, and CSS-id-selector
 * references to those ids in `data-upload-drop-zone` / `data-upload-preview`
 * / `data-upload-progress` / `data-action-target` attributes) — if two
 * instances rendered the same ids verbatim, the second would silently steal
 * DOM lookups (`document.getElementById`, `$('#...')`) from the first.
 *
 * The contract: P2-6 emits the shell with the literal token `__VUEFORM__`
 * everywhere an id is declared OR referenced (`id`, `for`, and any `data-*`
 * attribute value that embeds an id, including CSS-id-selector fragments
 * like `#comment_create_form___VUEFORM__`) — not just in `id="..."`
 * attributes themselves. This wrapper replaces every occurrence of that
 * token with a unique-per-instance id (a module-scope counter, not
 * `Math.random()`, so vue.build's output stays deterministic) before
 * binding the result via `v-html`.
 *
 * ## Widget interop
 *
 * `v-additions` boots the legacy widgets declared in the shell
 * (`data-ui-widget`/`data-ui-init`) the same way any other legacy-enhanced
 * fragment gets booted (pjax nav, modal open, stream reload). Once booted, a
 * widget instance caches itself on its own root DOM node via jQuery
 * `.data(<ComponentClass.component>, instance)` (see
 * `Component.prototype.init` in humhub.action.js) — NOT under a fixed
 * `'humhub-widget'` key: that key only exists as `Widget.componentData` in
 * humhub.ui.widget.js, which is dead/unused (nothing ever reads it; the
 * actual cache key is `this.static('component')`, i.e. each widget class's
 * own `.component` static, e.g. `RichTextEditor.component =
 * 'humhub-ui-richtexteditor'`, `Upload.component = 'humhub-file-upload'`).
 * This wrapper reads those two real keys directly off the shell's richtext
 * (`.humhub-ui-richtext`) and upload (`.main_comment_upload`) root nodes —
 * looser coupling than `require('ui.widget')`/`require('file')`, and
 * correct where the plan's originally-assumed `'humhub-widget'` key was not.
 *
 * ## Editor API relied on (`ui.richtext.prosemirror.RichTextEditor`)
 *  - `editor.serialize()` — current markdown; the exact call the legacy
 *    focusout handler uses to sync the hidden textarea before native submit.
 *  - `editor.init(markdown)` — (re)initializes the editor with new content;
 *    used post-boot by legacy code itself (`toggleCommentHandler` in
 *    humhub.comment.js re-primes the mention text on an already-booted
 *    editor this same way), so it is the documented `setValue()` mechanism
 *    here too — no need for the `initialValue`-prop-before-boot fallback the
 *    plan flagged as a possibility.
 *  - `focus()` — prototype method, focuses the ProseMirror view.
 *  - `$.trigger('clear')` — the widget's own `'clear'` DOM event handler
 *    calls `editor.clear()`, blanks the hidden textarea and resets the
 *    backup; this is the exact mechanism `humhub.comment.js`'s
 *    `Form.prototype.submit` uses after a successful post.
 *
 * ## Upload API relied on (`file.Upload`)
 *  - `reset()` — clears the fileCount, removes this instance's hidden guid
 *    inputs from its form, and resets the file preview list.
 *  - `options.uploadSubmitName` — the resolved hidden-input `name` attached
 *    files are posted under (`Model[attribute][]`, e.g.
 *    `Comment[fileList][]`); used to collect attached guids without
 *    hardcoding the model/attribute naming.
 *
 * ## Teardown
 * No `unmounted()` teardown is implemented: neither widget exposes a
 * destroy/dispose method (grep confirms none exists in either resource
 * file), and no legacy code path ever calls one — `Widget.prototype.replace`
 * itself just swaps the DOM and re-observes. A widget instance's only
 * reference is the jQuery data cached on its own root node; once Vue removes
 * that node from the DOM (unmount) and nothing else references it, it is
 * eligible for garbage collection like any other jQuery-data-bound element
 * today. The Vue island runtime's MutationObserver (humhub.vue.js
 * `module.init`) only concerns itself with whole-island cleanup, which is
 * orthogonal to this inner, non-island component.
 */

const FORM_TOKEN = '__VUEFORM__';
const RICHTEXT_SELECTOR = '.humhub-ui-richtext';
const RICHTEXT_COMPONENT_DATA = 'humhub-ui-richtexteditor';
const UPLOAD_SELECTOR = '.main_comment_upload';
const UPLOAD_COMPONENT_DATA = 'humhub-file-upload';

let instanceCounter = 0;

export default {
    props: {
        shellHtml: { type: String, required: true },
    },
    data() {
        return {
            // Module-scope counter (not Math.random()) so builds/output stay
            // deterministic; unique per mounted instance on the page.
            instanceId: 'vueform-' + (++instanceCounter),
        };
    },
    computed: {
        processedShell() {
            return this.shellHtml.split(FORM_TOKEN).join(this.instanceId);
        },
    },
    methods: {
        getEditorInstance() {
            const node = this.$el.querySelector(RICHTEXT_SELECTOR);
            return node ? jQuery(node).data(RICHTEXT_COMPONENT_DATA) : null;
        },
        getUploadInstance() {
            const node = this.$el.querySelector(UPLOAD_SELECTOR);
            return node ? jQuery(node).data(UPLOAD_COMPONENT_DATA) : null;
        },
        /** @returns {string} the current markdown value of the richtext editor. */
        getValue() {
            const editor = this.getEditorInstance();
            return editor ? editor.editor.serialize() : '';
        },
        /** Prefills the editor with markdown (e.g. for edit mode). */
        setValue(markdown) {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.editor.init(markdown || '');
            }
        },
        /** Empties the editor and resets the upload preview/file inputs. */
        clear() {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.$.trigger('clear');
            }
            const upload = this.getUploadInstance();
            if (upload) {
                upload.reset();
            }
        },
        /** Focuses the richtext editor (e.g. on reply). */
        focus() {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.focus();
            }
        },
        /** @returns {string[]} guids of files currently attached via the upload widget. */
        getFileGuids() {
            const upload = this.getUploadInstance();
            if (!upload) {
                return [];
            }
            const name = upload.options.uploadSubmitName;
            return jQuery(this.$el).find('input[name="' + name + '"]').map(function () {
                return this.value;
            }).get();
        },
    },
};
</script>
