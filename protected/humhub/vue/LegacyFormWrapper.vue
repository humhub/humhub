<template>
    <div v-html="processedShell" v-additions></div>
</template>

<script>
/**
 * Hosts a server-rendered legacy widget SHELL (e.g. a rich text editor)
 * inside a Vue island and exposes a
 * small, clean API to the surrounding Vue component so it never has to
 * touch jQuery/legacy widgets itself. Generic core interop component,
 * pairing with the PHP-side `humhub\widgets\VueFormShell` widget that
 * renders the server half of the contract below — the comment form
 * (`CommentForm.vue`, backed by
 * `comment/widgets/views/commentFormShell.php`, itself built on
 * `VueFormShell`) is the reference consumer, but both halves are generic:
 * any module can wrap a deep, not-yet-Vue legacy widget shell this way.
 *
 * ## Unique-id contract
 *
 * A page can host many instances of the SAME shell at once (e.g. the
 * comment section's main form, one open reply form per commented-on entry,
 * and an edit form) that are all clones of the SAME server-rendered
 * template (`shellHtml` prop) — the shell is fetched/rendered once, not
 * once per form. The server-rendered markup bakes element ids into itself
 * (an input's `id`/`id + '_input'`, a button's `id`, and CSS-id-selector
 * references to those ids in `data-*` attributes) — if two instances
 * rendered the same ids verbatim, the second would silently steal DOM
 * lookups (`document.getElementById`, `$('#...')`) from the first.
 *
 * The contract: the server-rendered shell carries the literal token
 * `__VUEFORM__` everywhere an id is declared OR referenced (`id`, `for`,
 * and any `data-*` attribute value that embeds an id, including
 * CSS-id-selector fragments like `#__VUEFORM___comment_create_form`, from
 * `VueFormShell::id('comment_create_form')` — the token is a PREFIX, not a
 * suffix) — not just in `id="..."` attributes themselves. This wrapper
 * replaces every occurrence of that token with a unique-per-instance id
 * before binding the result via `v-html`.
 *
 * That id comes from the `instanceKey` prop when given — sanitized and
 * prefixed to `vueform-<key>` — and from a module-scope counter otherwise
 * (`vueform-<n>`; a counter rather than `Math.random()` so vue.build's
 * output stays deterministic). The two differ in one property that MATTERS
 * beyond DOM-uniqueness: the counter restarts on every page load, so a
 * counter-derived id is only unique WITHIN one page's lifetime — any
 * feature the booted legacy widgets key off that id in storage that
 * OUTLIVES the page silently collides across pages. Browser-verified
 * concrete case: the richtext editor's draft backup
 * (`RichTextEditor.prototype.backup()` in
 * humhub.ui.richtext.prosemirror.js) keys `sessionStorage` entries by the
 * hidden input's id — with counter ids, `vueform-1_..._input` on one page
 * picked up (and merged with) a DIFFERENT page's `vueform-1` draft, and
 * armed phantom "unsaved changes" confirms for content the user never
 * typed there. Callers whose shell hosts such a widget MUST therefore pass
 * an `instanceKey` that is (a) unique among simultaneously mounted
 * instances on the page and (b) stable across page loads for the same
 * logical form — see `CommentForm.vue`'s `formInstanceKey` for the
 * reference scheme (`c<contentId>[-r<parentId>|-e<commentId>]`), which as
 * a bonus makes draft restore survive navigation. Keys are sanitized to
 * `[A-Za-z0-9_-]` so the resulting ids stay safe inside the CSS-id-selector
 * fragments (`#vueform-...`) the shell's `data-*` attributes embed.
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
 * 'humhub-ui-richtexteditor'`).
 * This wrapper reads that real key directly off the shell's richtext root
 * (the `[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]` element —
 * NOT `.humhub-ui-richtext`: browser-verified, that class only lands on the
 * INNER ProseMirror contenteditable, while the instance is cached on the
 * widget root carrying the data-ui-widget attribute) — looser coupling than
 * `require('ui.widget')`.
 *
 * ## Editor API relied on (`ui.richtext.prosemirror.RichTextEditor`)
 *  - `editor.serialize()` — current markdown; the exact call the legacy
 *    focusout handler uses to sync the hidden textarea before native submit.
 *  - `editor.init(markdown)` — (re)initializes the editor with new content;
 *    used post-boot by legacy code itself (`toggleCommentHandler` in
 *    humhub.comment.js re-primes the mention text on an already-booted
 *    editor this same way), so it is the documented `setValue()` mechanism
 *    here too — no separate `initialValue`-prop-before-boot path needed.
 *  - `focus()` — prototype method, focuses the ProseMirror view.
 *  - `$.trigger('clear')` — the widget's own `'clear'` DOM event handler
 *    calls `editor.clear()`, blanks the hidden textarea and resets the
 *    backup; this is the exact mechanism `humhub.comment.js`'s
 *    `Form.prototype.submit` uses after a successful post.
 *
 * File uploads are NOT part of this wrapper (anymore): they have a native
 * counterpart, `UploadField.vue`, and a shell therefore only carries what has
 * none — see that component's docblock and
 * `docs/develop/ui-js-vuejs-forms.md`.
 *
 * ## Unsaved-changes guard (see CommentForm.vue's own docblock section of
 * the same name for the full root-cause writeup, comment-consumer-specific)
 *
 * `ActiveForm::begin(['acknowledge' => true])` (baked into every
 * `VueFormShell`-rendered form by default — see `commentFormShell.php`,
 * today's only consumer) sets `data-ui-addition="acknowledgeForm"` on the
 * shell's `<form>`, which
 * `humhub.client.js` boots into a GLOBAL `beforeunload`/`pjax:beforeSend`
 * guard: it snapshots `$form.serialize()` once at boot
 * (`$form.data('state', snapshot)`) and, on every later navigation attempt,
 * compares the CURRENT serialization against that snapshot
 * (`formStateChanged()`) - a mismatch triggers the "Unsaved changes will be
 * lost" confirm. The only thing that ever clears the snapshot
 * (`resetChanges()`: `$form.data('state', null)`) is a native `submit` event
 * on the form or a click on a `[type=submit]` INSIDE it - this shell has
 * neither (see CommentForm.vue). `resetChanges()` itself is a closure-local
 * function with no exposed API, but its ENTIRE effect is a write to the
 * PUBLIC jQuery `.data()` store the guard also reads from - `resetAcknowledge()`
 * below reproduces it directly instead of trying to reach the private closure.
 *
 * ## Nested `<form>` via `v-html` — and its post-mount safety net
 *
 * The shell markup this component `v-html`s is typically ITSELF a `<form>` (see
 * `humhub\widgets\VueFormShell`), nested inside the host `HumHubForm`'s own outer
 * `<form>` (or, for a caller outside that suite, potentially any other live-document
 * `<form>`) — see `HumHubForm.vue`'s own docblock, "A note on legacy-citizen fields
 * and nested `<form>`", for the full mechanism: it survives only because this
 * component's root element is still detached and parentless the very first time
 * `v-html` parses it. `checkFormPresence()` (called from `mounted()` and `updated()`)
 * is the safety net for the failure mode that same section describes — a LATER
 * re-render reassigning `innerHTML` while `$el` is already attached would have the
 * browser silently drop the inner `<form>` instead, and every method here that
 * resolves `this.$el.querySelector('form')` (`resetAcknowledge()`, `getFileGuids()`)
 * would then just silently no-op rather than throw. `expectsForm` (a plain substring
 * test against the RAW `shellHtml` prop, before token substitution) is a cheap enough
 * proxy for "this shell is supposed to have one" that a shell with no `<form>` at all
 * (a module wrapping some other legacy widget through this same generic component)
 * never trips it.
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
import { log } from '@humhub/vue';

// Mirrors humhub\widgets\VueFormShell::TOKEN (PHP) — keep both literals in sync.
const FORM_TOKEN = '__VUEFORM__';
const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';
const RICHTEXT_COMPONENT_DATA = 'humhub-ui-richtexteditor';

let instanceCounter = 0;

export default {
    props: {
        shellHtml: { type: String, required: true },
        // Deterministic identity for this instance's DOM ids — see the class
        // docblock's "Unique-id contract" section for the uniqueness/stability
        // contract a caller-supplied key must satisfy (and why callers whose
        // shell hosts a backup-enabled richtext editor must pass one).
        instanceKey: { type: String, default: null },
    },
    data() {
        return {
            // From instanceKey when given (stable across page loads); from the
            // module-scope counter otherwise (unique per page load only — and a
            // counter, not Math.random(), so builds/output stay deterministic).
            instanceId: this.instanceKey
                ? 'vueform-' + this.instanceKey.replace(/[^A-Za-z0-9_-]/g, '-')
                : 'vueform-' + (++instanceCounter),
        };
    },
    computed: {
        processedShell() {
            return this.shellHtml.split(FORM_TOKEN).join(this.instanceId);
        },
        // Cheap proxy for "the parsed shell is supposed to contain a <form>" — see the
        // class docblock's "Nested <form> via v-html" section. Tested against the RAW
        // prop rather than `processedShell` since the token substitution never touches
        // the tag itself.
        expectsForm() {
            return /<form[\s>]/i.test(this.shellHtml);
        },
    },
    mounted() {
        this.checkFormPresence();
    },
    updated() {
        this.checkFormPresence();
    },
    methods: {
        /**
         * See the class docblock's "Nested <form> via v-html" section — logs a clear,
         * loud error instead of letting a dropped inner `<form>` fail silently the next
         * time `resetAcknowledge()`/`getFileGuids()` (or `onSubmit`'s own native
         * `'submit'` listener in `CommentForm.vue`) quietly finds nothing to act on.
         */
        checkFormPresence() {
            if (this.expectsForm && !this.$el.querySelector('form')) {
                log.error(
                    'LegacyFormWrapper: the rendered shell was expected to contain a <form> ' +
                    '(the shellHtml prop has one) but none was found in the DOM — the browser\'s ' +
                    'HTML fragment parser may have silently dropped it because this component\'s ' +
                    'root was already attached to the document when its markup was (re-)parsed; ' +
                    'see this component\'s own docblock, "Nested <form> via v-html".',
                );
            }
        },
        getEditorInstance() {
            const node = this.$el.querySelector(RICHTEXT_SELECTOR);
            return node ? jQuery(node).data(RICHTEXT_COMPONENT_DATA) : null;
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
        /** Empties the editor. */
        clear() {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.$.trigger('clear');
            }
            this.resetAcknowledge();
        },
        /**
         * Neutralizes humhub.client.js's acknowledgeForm unsaved-changes baseline for this
         * instance's `<form>` - see the class docblock's "Unsaved-changes guard" section.
         * `.data('state')` is the exact (and only) thing `resetChanges()` itself touches;
         * writing `null` through the same public jQuery `.data()` store makes
         * `formStateChanged()` short-circuit to "unchanged" on its very next check,
         * regardless of what the form's serialized content actually looks like.
         */
        resetAcknowledge() {
            const form = this.$el.querySelector('form');
            if (form) {
                jQuery(form).data('state', null);
            }
        },
        /** Focuses the richtext editor (e.g. on reply). */
        focus() {
            const editor = this.getEditorInstance();
            if (editor) {
                editor.focus();
            }
        },
    },
};
</script>
