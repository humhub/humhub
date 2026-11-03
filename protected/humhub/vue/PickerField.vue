<template>
    <div class="mb-3" :class="[`field-${fieldId}`, { required }]">
        <label v-if="label" :for="fieldId" class="form-label">{{ label }}</label>
        <PickerInput
            ref="picker"
            v-bind="$attrs"
            :id="fieldId"
            :name="fieldName"
            :label="label || ''"
            :placeholder="placeholder || ''"
            :required="required"
            :disabled="isDisabled"
            :invalid="hasError"
            :described-by="describedBy"
            :model-value="modelValue"
            @update:model-value="onUpdate"
        />
        <div v-if="hint" :id="hintId" class="form-text text-muted">{{ hint }}</div>
        <div v-if="hasError" :id="errorId" class="invalid-feedback d-block">
            <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
        </div>
    </div>
</template>

<script>
import fieldMixin from './form/fieldMixin.js';
import PickerInput from './PickerInput.vue';

/**
 * The `HumHubForm` suite field of a picker (`<picker-field>`): the suite's container, label,
 * hint and errors (`form/fieldMixin.js`) around a `PickerInput`, which gets every other
 * attribute (`multiple`, `source`, `options`, …) — see docs/develop/ui-js-vuejs-picker.md and
 * docs/develop/ui-js-vuejs-forms.md. `.invalid-feedback` is `d-block`: the invalid
 * `.picker-field` is not its previous sibling, so Bootstrap's sibling rule cannot show it.
 *
 * @since 1.20
 */
export default {
    name: 'PickerField',
    components: { PickerInput },
    mixins: [fieldMixin],
    inheritAttrs: false,
    props: {
        modelValue: { type: [String, Number, Array], default: '' },
    },
    emits: ['update:modelValue'],
    methods: {
        onUpdate(value) {
            this.clearOwnError();
            this.$emit('update:modelValue', value);
        },
        focus() {
            this.$refs.picker?.$refs.input?.focus();
        },
    },
};
</script>
