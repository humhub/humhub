<template>
    <div class="mb-3" :class="[`field-${fieldId}`, { required }]">
        <label v-if="label" :for="fieldId" class="form-label">{{ label }}</label>
        <ColorInput
            ref="color"
            :id="fieldId"
            :name="fieldName"
            :label="label"
            :placeholder="placeholder || '#rrggbb'"
            :disabled="isDisabled"
            :invalid="hasError"
            :described-by="describedBy"
            :model-value="modelValue"
            @update:model-value="onUpdate"
            @change="$emit('change', $event)"
        />
        <div v-if="hint" :id="hintId" class="form-text text-muted">{{ hint }}</div>
        <div v-if="hasError" :id="errorId" class="invalid-feedback d-block">
            <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
        </div>
    </div>
</template>

<script>
import fieldMixin from './form/fieldMixin.js';
import ColorInput from './ColorInput.vue';

/**
 * The form-suite field around `ColorInput` (see its docblock): label, hint and errors as every
 * field of `HumHubForm` (`docs/develop/ui-js-vuejs-forms.md`), the colour as `v-model`.
 *
 * The error block is `d-block`: Bootstrap shows `.invalid-feedback` only after a sibling
 * `.is-invalid`, and the invalid input sits inside the input group, as with `PickerField`.
 *
 * @since 1.20
 */
export default {
    name: 'ColorField',
    components: { ColorInput },
    mixins: [fieldMixin],
    props: {
        modelValue: { type: String, default: '' },
    },
    emits: ['update:modelValue', 'change'],
    methods: {
        onUpdate(value) {
            this.clearOwnError();
            this.$emit('update:modelValue', value);
        },
        focus() {
            this.$refs.color?.focus();
        },
    },
};
</script>
