<template>
    <div class="mb-3" :class="[`field-${fieldId}`, { required }]">
        <label v-if="label" :for="fieldId" class="form-label">{{ label }}</label>
        <textarea
            ref="input"
            :id="fieldId"
            :name="fieldName"
            class="form-control"
            :class="{ 'is-invalid': hasError }"
            :placeholder="placeholder"
            :disabled="isDisabled"
            :rows="rows"
            :aria-required="required ? 'true' : null"
            :aria-invalid="hasError ? 'true' : null"
            :aria-describedby="describedBy"
            v-model="internalValue"
        ></textarea>
        <div v-if="hint" :id="hintId" class="form-text text-muted">{{ hint }}</div>
        <div v-if="hasError" :id="errorId" class="invalid-feedback">
            <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
        </div>
    </div>
</template>

<script>
/**
 * A multi-line text input, part of the `HumHubForm` suite — see `HumHubForm.vue`'s
 * own docblock for the suite overview and `TextField.vue`'s own docblock for the
 * full markup-parity writeup this component shares verbatim (`yii\widgets\
 * ActiveField::$template`'s default `{label}{input}{hint}{error}` order, the
 * `mb-3 field-<id>[ required]` container, `form-control`/`is-invalid`, the
 * all-messages-not-just-first deviation, ...) — the only difference is the `<input>`
 * tag itself, `Html::activeTextarea()`'s equivalent.
 *
 * @since 1.20
 */
import fieldMixin from './form/fieldMixin.js';

export default {
    mixins: [fieldMixin],
    props: {
        modelValue: { type: String, default: '' },
        rows: { type: Number, default: 4 },
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
