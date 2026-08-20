import { FORM_CONTEXT_KEY, toInputId } from './formContext.js';

/**
 * Shared behavior for every `HumHubForm` field component (`TextField`,
 * `TextareaField`, `CheckboxField`, `SelectField`, `RichTextField`) — internal
 * building block, not itself a registered component (see `formContext.js`'s own
 * docblock on why living in a subdirectory of `vue/` matters).
 *
 * Provides, via injection from the nearest ancestor `HumHubForm` (see its own
 * docblock for the provided shape):
 *  - `fieldName`/`fieldId` — the Yii-convention `Model[attribute]` name and its
 *    matching `model-attribute` id (`toInputId()`, mirroring
 *    `Html::getInputIdByName()`), falling back to the bare `attribute` when this
 *    field is not nested in a `HumHubForm` with a `modelName` (or not nested in one
 *    at all — see below).
 *  - `hasError`/`errorMessages` — this attribute's current server-side validation
 *    errors, `[]`/`false` when there are none.
 *  - `isDisabled` — this field's own `disabled` prop OR the form's `busy` state.
 *  - `clearOwnError()` — clears this attribute's error via the injected
 *    `clearError()`; every concrete field's own `internalValue` setter calls this on
 *    every write (see e.g. `TextField.vue`), matching the "clear that attribute's
 *    error on input" contract documented in `HumHubForm.vue`.
 *  - Registers/unregisters itself (by `attribute` + component instance) with the
 *    form on mount/unmount, so `HumHubForm.focusFirstError()` can find and focus
 *    this field's `input` ref — see each field's own `focus()` method.
 *
 * **Standalone degradation.** A field mounted OUTSIDE any `HumHubForm` (`humhubForm`
 * injects as `null` — e.g. a field rendered on its own in a unit test, or a
 * hypothetical future standalone-field use case) still renders correctly: no model
 * prefix on `name`, never busy, never in error, and register/unregister become
 * no-ops. This is a deliberate, documented degradation, not a misconfiguration guard.
 *
 * @since 1.19
 */
export default {
    inject: {
        humhubForm: { from: FORM_CONTEXT_KEY, default: null },
    },
    props: {
        attribute: { type: String, required: true },
        label: { type: String, default: null },
        hint: { type: String, default: null },
        placeholder: { type: String, default: null },
        // Visual marker only (a "required" wrapper class + aria-required on the
        // input) — see each field's own docblock. Validation stays server-side (Yii
        // model rules are the single source of truth); this prop never blocks
        // submission client-side.
        required: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
    },
    computed: {
        formModelName() {
            return this.humhubForm ? this.humhubForm.modelName.value : '';
        },
        formErrors() {
            return this.humhubForm ? this.humhubForm.errors.value : {};
        },
        formBusy() {
            return this.humhubForm ? this.humhubForm.busy.value : false;
        },
        fieldName() {
            return this.formModelName ? `${this.formModelName}[${this.attribute}]` : this.attribute;
        },
        fieldId() {
            return toInputId(this.fieldName);
        },
        hintId() {
            return this.hint ? `${this.fieldId}-hint` : null;
        },
        errorId() {
            return this.hasError ? `${this.fieldId}-error` : null;
        },
        describedBy() {
            return [this.hintId, this.errorId].filter(Boolean).join(' ') || null;
        },
        errorMessages() {
            const messages = this.formErrors[this.attribute];
            return Array.isArray(messages) ? messages : [];
        },
        hasError() {
            return this.errorMessages.length > 0;
        },
        isDisabled() {
            return this.disabled || this.formBusy;
        },
    },
    methods: {
        clearOwnError() {
            if (this.hasError && this.humhubForm) {
                this.humhubForm.clearError(this.attribute);
            }
        },
    },
    mounted() {
        if (this.humhubForm) {
            this.humhubForm.registerField(this.attribute, this);
        }
    },
    beforeUnmount() {
        if (this.humhubForm) {
            this.humhubForm.unregisterField(this.attribute, this);
        }
    },
};
