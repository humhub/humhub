<template>
    <div class="mb-3" :class="[`field-${fieldId}`, { required }]">
        <label v-if="label" :for="fieldId" class="form-label">{{ label }}</label>
        <select
            ref="input"
            :id="fieldId"
            :name="fieldName"
            class="form-select"
            :class="{ 'is-invalid': hasError }"
            :disabled="isDisabled"
            :aria-required="required ? 'true' : null"
            :aria-invalid="hasError ? 'true' : null"
            :aria-describedby="describedBy"
            v-model="internalValue"
        >
            <option v-if="prompt !== null" value="">{{ prompt }}</option>
            <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
        <div v-if="hint" :id="hintId" class="form-text text-muted">{{ hint }}</div>
        <div v-if="hasError" :id="errorId" class="invalid-feedback">
            <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
        </div>
    </div>
</template>

<script>
/**
 * A `<select>` dropdown, part of the `HumHubForm` suite — see `HumHubForm.vue`'s own
 * docblock for the suite overview and `TextField.vue`'s own docblock for the shared
 * markup-parity approach (same container/template/error handling; only the input
 * element and its class differ).
 *
 * ## Markup parity with `yii\bootstrap5\ActiveField::dropDownList()`
 *
 * `form-select` (not `form-control`) is Bootstrap5's own dedicated select class —
 * see `ActiveField::dropDownList()`, which explicitly adds it. Otherwise identical
 * to `TextField`'s reference markup (default `{label}{input}{hint}{error}`
 * template, same container).
 *
 * `v-model` (rather than a manual `:value`/`@change` pair) is used deliberately for
 * the underlying `<select>`, not just for consistency with the other fields: Vue's
 * compiler special-cases `<select v-model>` to write the selected `<option>` AFTER
 * the `v-for`-rendered options themselves have patched, which a plain `:value`
 * binding does not reliably guarantee (a real risk here, since `options` is a prop
 * that can change independently of `modelValue`).
 *
 * **Props.** `options` (Array of `{value, label}`, default `[]`); `prompt` (String,
 * default `null`) — when given, renders an additional, always-first, empty-value
 * `<option>` (`ActiveField::dropDownList()`'s own `prompt` option, e.g. "Please
 * select ...").
 *
 * @since 1.19
 */
import fieldMixin from './form/fieldMixin.js';

export default {
    mixins: [fieldMixin],
    props: {
        modelValue: { type: [String, Number], default: '' },
        options: { type: Array, default: () => [] },
        prompt: { type: String, default: null },
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
