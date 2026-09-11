<template>
    <LegacyFormWrapper ref="wrapper" :shell-html="shellHtml" :instance-key="instanceKey" />
    <div v-if="hasError" :id="errorId" class="invalid-feedback d-block">
        <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
    </div>
</template>

<script>
/**
 * A `HumHubForm` field embedding a legacy richtext-editor-plus-upload shell (see
 * `HumHubForm.vue`'s own docblock for the suite overview) — the "legacy citizen"
 * the suite's owner design explicitly allows for: native components for standard
 * fields, `LegacyFormWrapper` kept as the internal engine for heavy legacy widgets
 * (ProseMirror richtext, upload) until native Vue counterparts exist. See
 * `docs/develop/ui-js-vuejs-forms.md`, "Legacy fields", for the full writeup this
 * docblock summarizes.
 *
 * ## Scope: the richtext editor only
 *
 * A shell used to bake the richtext editor AND the file-upload widget into one
 * server-rendered HTML blob, which is why this field once owned both. Uploads
 * now have a native counterpart (`UploadField.vue`, see
 * `docs/develop/ui-js-vuejs-forms.md`), so a shell carries only what has none —
 * the ProseMirror editor — and the two are separate fields of the same form
 * (`CommentForm.vue` is the reference composition).
 *
 * ## No generic field wrapper markup
 *
 * Unlike `TextField`/`TextareaField`/`CheckboxField`/`SelectField` (see their own
 * docblocks), this component's template is a bare fragment — no `mb-3`/`field-<id>`
 * wrapper div, no rendered `label`/`hint`. The shell's own server-rendered markup
 * (`commentFormShell.php` et al.) already carries its own established spacing and
 * has no slot for an externally-imposed label — adding the generic wrapper here
 * would both be visually redundant and risk a spacing regression against the
 * pre-`HumHubForm` markup (see `CommentForm.vue`'s own migration notes). `label`/
 * `hint`/`required`/`disabled` are still accepted (inherited from `form/
 * fieldMixin.js`, shared by every field) for interface consistency, but `label`/
 * `hint` are not rendered and `disabled`/busy do not reach the editor itself — the
 * legacy widgets it wraps expose no reactive disable hook; `SubmitButton`'s own
 * busy-disable (see its docblock) remains the actual guard against a double
 * submit while a request is in flight. Documented gap, not an oversight.
 *
 * ## API
 *
 * Thin proxies to the wrapped `LegacyFormWrapper` ref — see ITS OWN docblock for
 * the full widget-interop contract (the `__VUEFORM__` token, the widget-instance
 * APIs read off cached jQuery data, the unsaved-changes-guard mechanism, ...) this
 * component does not duplicate: `getValue()`, `setValue(markdown)`, `clear()`,
 * `resetAcknowledge()`, `focus()` (also this field's
 * `HumHubForm.focusFirstError()` entry point), plus `getShellElement()` — the
 * shell's own root DOM node, which `CommentForm.vue` needs directly to resolve its
 * Teleport target and its native-submit-fallback `<form>` listener (see its own
 * docblock).
 *
 * Error rendering (`hasError`/`errorMessages`, from `form/fieldMixin.js`) reuses
 * the exact class list (`invalid-feedback d-block`) `CommentForm.vue` rendered
 * itself before this suite existed — `d-block` (rather than relying on Bootstrap's
 * `.is-invalid ~ .invalid-feedback` sibling-selector CSS, which every OTHER field
 * in this suite can lean on) stays necessary here because there is no sibling
 * `.is-invalid`-carrying input for it to follow: the shell's own inner input
 * elements are legacy-rendered and never gain that class from this suite.
 *
 * @since 1.20
 */
import fieldMixin from './form/fieldMixin.js';

export default {
    mixins: [fieldMixin],
    props: {
        shellHtml: { type: String, required: true },
        // Passed through to LegacyFormWrapper — see ITS "Unique-id contract"
        // docblock section for the uniqueness/stability contract (and why a
        // caller whose shell hosts the backup-enabled richtext editor — i.e.
        // every caller of THIS field — should pass one).
        instanceKey: { type: String, default: null },
    },
    mounted() {
        // Clear-error-on-input parity with the native fields: their
        // `internalValue` setters call `clearOwnError()` on every write (see
        // e.g. TextField.vue), but the legacy ProseMirror editor inside the
        // shell never routes its input through any Vue binding, so a 422
        // message rendered by this field used to stick even while the user
        // was already fixing the value (browser-verified). The editor's
        // contenteditable emits native `input` events that bubble through the
        // shell — one delegated listener on the wrapper root is the whole
        // bridge. Runs alongside fieldMixin's own mounted() (Vue merges mixin
        // and component hooks; both run).
        this.$refs.wrapper.$el.addEventListener('input', this.clearOwnError);
    },
    beforeUnmount() {
        this.$refs.wrapper.$el.removeEventListener('input', this.clearOwnError);
    },
    methods: {
        getValue() {
            return this.$refs.wrapper.getValue();
        },
        setValue(markdown) {
            this.$refs.wrapper.setValue(markdown);
        },
        clear() {
            this.$refs.wrapper.clear();
        },
        resetAcknowledge() {
            this.$refs.wrapper.resetAcknowledge();
        },
        focus() {
            this.$refs.wrapper.focus();
        },
        /** @returns {Element} the shell's own root DOM node (see the class docblock's "API" section). */
        getShellElement() {
            return this.$refs.wrapper.$el;
        },
    },
};
</script>
