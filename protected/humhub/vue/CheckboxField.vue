<template>
    <div class="mb-3" :class="[`field-${fieldId}`, { required }]">
        <div class="form-check">
            <input
                ref="input"
                :id="fieldId"
                :name="fieldName"
                type="checkbox"
                class="form-check-input"
                :class="{ 'is-invalid': hasError }"
                value="1"
                :disabled="isDisabled"
                :aria-required="required ? 'true' : null"
                :aria-invalid="hasError ? 'true' : null"
                :aria-describedby="describedBy"
                v-model="internalValue"
            >
            <label v-if="label" :for="fieldId" class="form-check-label">{{ label }}</label>
            <div v-if="hasError" :id="errorId" class="invalid-feedback">
                <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
            </div>
            <div v-if="hint" :id="hintId" class="form-text text-muted">{{ hint }}</div>
        </div>
    </div>
</template>

<script>
/**
 * A checkbox input, part of the `HumHubForm` suite — see `HumHubForm.vue`'s own
 * docblock for the suite overview and `TextField.vue`'s own docblock for the shared
 * markup-parity approach.
 *
 * ## Markup parity with `yii\bootstrap5\ActiveField::checkbox()`
 *
 * Checkboxes use a DIFFERENT reference template than `TextField`/`TextareaField`/
 * `SelectField`: `ActiveField::$checkTemplate` is
 * `<div class="form-check">{input}{label}{error}{hint}</div>` — input, label,
 * ERROR, then hint (hint and error are swapped relative to the default template),
 * with `checkOptions`' `form-check-input`/`form-check-label` classes instead of
 * `form-control`/`form-label`. That inner `.form-check` div is itself still wrapped
 * in the same outer `mb-3 field-<id>[ required]` container every field gets
 * (`ActiveField::render()` always calls `begin()`/`end()` around the template,
 * regardless of which template is active).
 *
 * @since 1.19
 */
import fieldMixin from './form/fieldMixin.js';

export default {
    mixins: [fieldMixin],
    props: {
        modelValue: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    computed: {
        internalValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
                this.clearOwnError();
            },
        },
    },
    methods: {
        focus() {
            if (this.$refs.input) {
                this.$refs.input.focus();
            }
        },
    },
};
</script>
