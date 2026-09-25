<template>
    <div v-if="filter.type === 'text'" class="c-search-field">
        <i class="ti ti-search c-search-field__icon" aria-hidden="true"></i>
        <input
            :id="inputId"
            type="text"
            class="c-search-field__input"
            autocomplete="off"
            :value="modelValue"
            :placeholder="filter.placeholder || filter.label || ''"
            :aria-label="filter.label || filter.placeholder || null"
            @input="$emit('update:modelValue', $event.target.value)"
        >
    </div>
    <FilterSelect
        v-else-if="filter.type === 'select'"
        :id="inputId"
        :model-value="String(modelValue ?? '')"
        :options="options"
        :placeholder="filter.placeholder || ''"
        :label="filter.label || ''"
        :loading="loading"
        @update:model-value="$emit('update:modelValue', $event)"
    />
    <div v-else-if="filter.type === 'tags'" class="c-filter-tags" role="group" :aria-label="filter.label">
        <span v-if="filter.label" class="c-filter-tags__label" aria-hidden="true">{{ filter.label }}</span>
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            class="c-filter-tags__tag"
            :class="{ active: isTagActive(option.value) }"
            :aria-pressed="isTagActive(option.value) ? 'true' : 'false'"
            @click="toggleTag(option.value)"
        >{{ option.label }}</button>
    </div>
    <div v-else-if="filter.type === 'checkbox'" class="c-filter-check form-check">
        <input
            :id="inputId"
            class="form-check-input"
            type="checkbox"
            :checked="modelValue"
            @change="$emit('update:modelValue', $event.target.checked)"
        >
        <label class="form-check-label" :for="inputId">{{ filter.label }}</label>
    </div>
    <component
        :is="component"
        v-else-if="component"
        :filter="filter"
        :model-value="modelValue"
        :input-id="inputId"
        @update:model-value="$emit('update:modelValue', $event)"
    />
</template>

<script>
import FilterSelect from '../FilterSelect.vue';

/**
 * The control of one filter inside a `FilterBar` cell — internal to the bar, which owns the
 * values, the options (static ones followed by those loaded from `optionsUrl`) and their
 * loading state. Renders the core types itself (`text`, `select`, `tags`, `checkbox`) and any
 * other type through `component`, the name the bar resolved from the filter-type registry
 * (see `registerFilterType()` in humhub.vue.js); with neither it renders nothing.
 *
 * @since 1.20
 */
export default {
    name: 'FilterControl',
    components: { FilterSelect },
    props: {
        filter: { type: Object, required: true },
        modelValue: { type: [String, Array, Boolean, Number], default: '' },
        inputId: { type: String, required: true },
        options: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
        component: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    methods: {
        isTagActive(value) {
            if (this.filter.multiple === true) {
                return value === '' ? this.modelValue.length === 0 : this.modelValue.includes(value);
            }
            return this.modelValue === value;
        },
        toggleTag(value) {
            const current = this.modelValue;
            if (this.filter.multiple !== true) {
                this.$emit('update:modelValue', current === value ? '' : value);
                return;
            }
            if (value === '') {
                this.$emit('update:modelValue', []);
                return;
            }
            this.$emit('update:modelValue', current.includes(value) ? current.filter((v) => v !== value) : [...current, value]);
        },
    },
};
</script>
