<template>
    <form @submit.prevent="onSubmit">
        <div v-if="unownedErrorMessages.length" class="alert alert-danger error-summary">
            <ul>
                <li v-for="(message, index) in unownedErrorMessages" :key="index">{{ message }}</li>
            </ul>
        </div>
        <slot />
    </form>
</template>

<script>
/**
 * Root component of the native Vue form suite (`HumHubForm` + `TextField`/
 * `TextareaField`/`CheckboxField`/`SelectField`/`RichTextField`/`SubmitButton`, all
 * top-level in `protected/humhub/vue/` and therefore auto-registered — see
 * `vue.build.mjs`'s header comment for the convention). See
 * `docs/develop/ui-js-vuejs-forms.md` for the full component/props reference,
 * error-contract writeup and a worked example; this docblock covers the mechanics.
 *
 * ## What this component owns — and what it deliberately does not
 *
 * `HumHubForm` renders a bare `<form>` (no `action`, submission is always
 * intercepted via `@submit.prevent`) and provides a shared context — `modelName`,
 * `errors`, `busy`, plus the mutator methods below — to every field nested anywhere
 * inside it (via `provide()`/`inject`, resolved by `form/fieldMixin.js`, not by
 * prop-drilling). Fields never need a direct reference to their form.
 *
 * It does **not** perform the actual HTTP request. Emitting `submit` and leaving the
 * `client.post(...)` call to the consumer is deliberate: target endpoints, payload
 * shapes and success handling vary too much module to module to usefully centralize
 * (an edit form posts to a different URL than a create form; a module might attach
 * extra fields the JSON payload needs that aren't modeled as `HumHubForm` fields at
 * all) — this mirrors how `CommentForm.vue` already worked before this suite existed
 * and continues to work now that it's built on it (see its own docblock): the
 * consumer's `submit` handler builds and posts the payload itself, then calls
 * `setErrors()` on a 422 or `clearErrors()` on success.
 *
 * ## Props
 *
 * - `modelName` (String, default `''`) — the Yii model name a field's
 *   `Model[attribute]` `name`/`id` convention is built from (e.g. `'Comment'`).
 *   Empty means "no model" — fields fall back to the bare `attribute` as their name
 *   (see `form/fieldMixin.js`).
 * - `busy` (Boolean, default `false`) — a convenience prop reflected into the
 *   context so every nested field/`SubmitButton` disables together. `HumHubForm`
 *   itself never sets this — the consumer toggles it around its own `client.post()`
 *   call (see `CommentForm.vue` for the reference usage: `:busy="busy"`).
 *
 * ## Public API
 *
 * Plain Options API methods — already reachable through a template `ref` with no
 * extra `expose()` step (Options API, unlike `<script setup>`, exposes every
 * method/prop/data property on the public instance by default):
 *
 * - `setErrors(payload)` — replaces the current error map. Accepts the raw Yii 422
 *   body (`{attribute: [messages]}`) directly, and defensively unwraps two envelope
 *   shapes around it: `{errors: {...}}` and `{error: {errors: {...}}}` — the exact
 *   three shapes `CommentForm.vue` already handled by hand before this suite existed
 *   (see its own docblock's "Error shapes" section for where the second and third
 *   come from: a response whose `Content-Type` wasn't sniffed as JSON). Once those
 *   three shapes have had their chance to unwrap it, whatever remains must itself be a
 *   plain, non-array object — anything else (a bare string/number, an array, `null`)
 *   leaves the error map simply cleared instead of being copied into it verbatim, which
 *   would otherwise hand every consumer (`fieldMixin.js`'s `errorMessages`, the
 *   form-level summary below) a non-array value where they assume one.
 * - `clearErrors()` — empties the error map (call before a fresh submit attempt).
 * - `focusFirstError()` — focuses the first-registered field (template/registration
 *   order, not object-key order of `errors` — the server's 422 payload has no
 *   guaranteed key order) whose `attribute` currently has an error. A no-op if no
 *   registered field is in error, or the erroring field's own component exposes no
 *   `focus()` method.
 *
 * ## Form-level fallback for unowned errors
 *
 * A 422 payload can legitimately carry an attribute no `TextField`/`TextareaField`/
 * etc. inside this form ever registered for — e.g. a module hooks
 * `Model::EVENT_BEFORE_VALIDATE` to add a rule against an attribute the form's
 * author never modeled as a field at all. Before this section existed, such an
 * error landed in `this.errors` (so `focusFirstError()` and `hasError` bookkeeping
 * both saw it) but was never rendered anywhere — a submit that failed validation for
 * exactly that attribute looked, from the user's seat, like it silently did nothing.
 * `unownedErrorMessages` (computed) collects the messages of every attribute currently
 * in `errors` that has no entry in `this._fields`, and the template renders them in a
 * `.alert.alert-danger.error-summary` block (mirroring the canonical classes/structure
 * of `yii\bootstrap5\ActiveForm::$errorSummaryCssClass` +
 * `yii\helpers\BaseHtml::errorSummary()`'s own `<ul><li>` markup) right before the
 * default slot. An attribute WITH a registered field is excluded here even while it
 * has an error, so its message keeps rendering exactly once — inline via that field's
 * own `invalid-feedback`, never duplicated into this summary too.
 *
 * ## Reactivity note (Options API `provide()`)
 *
 * Every provided value that can change after mount (`modelName`, `busy`, `errors`)
 * is wrapped in `computed()` — the documented Vue 3 Options API pattern for a LIVE
 * provide/inject binding (a provided plain primitive/reference would otherwise be a
 * one-time snapshot; see the Vue docs on `provide`/`inject` reactivity). Consumers
 * (`form/fieldMixin.js`) unwrap `.value` themselves — see its own `formModelName`/
 * `formErrors`/`formBusy` computeds.
 *
 * `errors` itself is also always MUTATED in place (`setErrors()`/`clearErrors()`/
 * `clearError()` never reassign `this.errors` to a new object) rather than replaced —
 * belt-and-suspenders together with the `computed()` wrapper above, and what lets
 * `this.errors` be read directly wherever object identity matters.
 *
 * ## A note on legacy-citizen fields and nested `<form>`
 *
 * `RichTextField` (see its own docblock) embeds `LegacyFormWrapper`, which `v-html`s
 * a server-rendered shell that is ITSELF a `<form>` (see
 * `humhub\widgets\VueFormShell`). Nesting that inside `HumHubForm`'s own `<form>`
 * therefore puts a `<form>` inside a `<form>` — invalid per the HTML5 content model.
 * That the inner `<form>` actually survives is NOT, as an earlier version of this
 * note claimed, some general property of `v-html`/fragment parsing being independent
 * of the live document — it depends on one narrow, fragile timing fact (verified in
 * Chromium): the fragment-parsing algorithm `innerHTML`/`v-html` runs seeds its
 * internal "form element pointer" from the context element's ANCESTORS, and at the
 * moment Vue's mount patch first assigns `LegacyFormWrapper`'s shell markup to its
 * root element, that element is still DETACHED and PARENTLESS — Vue builds a
 * component's DOM off-document and only inserts the whole finished subtree into the
 * live document in one step afterwards. A parentless element has no ancestors, so the
 * parser never sees this outer `<form>` and lets the inner `<form>` start tag through
 * untouched. A LATER re-render that reassigns that same element's `innerHTML` while it
 * is already attached inside this outer `<form>` would have the parser find the outer
 * form as an ancestor instead and silently DROP the inner `<form>` start tag — its
 * attributes lost, its children hoisted straight into `LegacyFormWrapper`'s own root,
 * no thrown error — see `LegacyFormWrapper.vue`'s own `checkFormPresence()` for the
 * safety net that detects exactly this failure mode. The PRACTICAL consequence
 * (documented on `CommentForm.vue` itself, which is the one component that hits
 * this): any element physically inside that inner shell — including a `SubmitButton`
 * `Teleport`ed into it — resolves its native form-submission activation against the
 * INNER (legacy) form, never against this component's own outer one. `HumHubForm`'s
 * own `submit` emission is therefore effectively dormant for such a field; the
 * consumer wires its submit handler directly on the `SubmitButton` instead
 * (`@click`), exactly as it already did before this suite existed. Every *native*
 * field (`TextField` and friends) has no such inner form, so `submit` fires normally
 * for them (including native Enter-to-submit inside a `TextField`).
 *
 * @since 1.19
 */
import { computed } from 'vue';
import { FORM_CONTEXT_KEY } from './form/formContext.js';

export default {
    props: {
        modelName: { type: String, default: '' },
        busy: { type: Boolean, default: false },
    },
    emits: ['submit'],
    data() {
        return {
            // Mutated in place, never reassigned — see the class docblock's
            // "Reactivity note" section.
            errors: {},
        };
    },
    provide() {
        return {
            [FORM_CONTEXT_KEY]: {
                modelName: computed(() => this.modelName),
                busy: computed(() => this.busy),
                errors: computed(() => this.errors),
                clearError: this.clearError,
                registerField: this.registerField,
                unregisterField: this.unregisterField,
            },
        };
    },
    created() {
        // Plain array, NOT reactive `data()` — registration order only matters for
        // `focusFirstError()`'s own iteration, never rendered/watched.
        this._fields = [];
    },
    computed: {
        // Messages for attributes that currently have an error but no registered field to
        // show it on — see the class docblock's "Form-level fallback for unowned errors"
        // section. Excludes every attribute `this._fields` (registerField()/unregisterField(),
        // see `form/fieldMixin.js`) knows about, so a field's own inline `invalid-feedback`
        // never gets a duplicate here.
        unownedErrorMessages() {
            const ownedAttributes = new Set(this._fields.map((field) => field.attribute));
            const messages = [];
            Object.keys(this.errors).forEach((attribute) => {
                if (ownedAttributes.has(attribute)) {
                    return;
                }
                const attributeMessages = this.errors[attribute];
                if (Array.isArray(attributeMessages)) {
                    messages.push(...attributeMessages);
                }
            });
            return messages;
        },
    },
    methods: {
        onSubmit() {
            this.$emit('submit');
        },
        setErrors(payload) {
            const source = payload || {};
            let unwrapped = source;
            if (source.errors && typeof source.errors === 'object') {
                unwrapped = source.errors;
            } else if (source.error && source.error.errors && typeof source.error.errors === 'object') {
                unwrapped = source.error.errors;
            }

            this.clearErrors();

            // Guard AFTER the three envelope shapes above have had their chance to unwrap
            // `unwrapped` into the actual `{attribute: [messages]}` map — a caller passing
            // something that isn't one even once unwrapped (a bare string, a number, an
            // array, `null`) leaves the form simply cleared rather than corrupting `errors`
            // with non-array-of-strings values `Object.assign` would otherwise copy in
            // verbatim (every consumer, e.g. `fieldMixin.js`'s `errorMessages`, assumes an
            // array per attribute).
            if (!unwrapped || typeof unwrapped !== 'object' || Array.isArray(unwrapped)) {
                return;
            }

            Object.assign(this.errors, unwrapped);
        },
        clearErrors() {
            Object.keys(this.errors).forEach((attribute) => {
                delete this.errors[attribute];
            });
        },
        clearError(attribute) {
            if (Object.prototype.hasOwnProperty.call(this.errors, attribute)) {
                delete this.errors[attribute];
            }
        },
        registerField(attribute, instance) {
            this._fields.push({ attribute, instance });
        },
        unregisterField(attribute, instance) {
            const index = this._fields.findIndex((entry) => entry.instance === instance);
            if (index !== -1) {
                this._fields.splice(index, 1);
            }
        },
        focusFirstError() {
            const entry = this._fields.find((field) => {
                const messages = this.errors[field.attribute];
                return Array.isArray(messages) && messages.length > 0;
            });
            if (entry && typeof entry.instance.focus === 'function') {
                entry.instance.focus();
            }
        },
    },
};
</script>
