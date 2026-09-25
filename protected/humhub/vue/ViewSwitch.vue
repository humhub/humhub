<template>
    <div class="c-view-switch" role="group" :aria-label="label">
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            class="btn c-icon-button c-icon-button--ghost c-view-switch__button"
            :class="{ 'is-active': option.value === modelValue }"
            :aria-pressed="option.value === modelValue ? 'true' : 'false'"
            :aria-label="option.label"
            :title="option.label"
            @click="choose(option.value)"
        ><i :class="'ti ti-' + option.icon" aria-hidden="true"></i></button>
    </div>
</template>

<script>
/**
 * A switch between views of the same content (`<view-switch>`), e.g. tiles and a list: a group
 * of icon buttons (`.btn.c-icon-button`, for a `PageToolbar`'s `actions` slot), the active one
 * pressed.
 *
 * - `v-model` (`modelValue`): the value of the active option. Choosing the active option again
 *   emits nothing.
 * - `options`: `[{ value, icon, label }]` — `icon` a Tabler name, `label` the button's
 *   accessible name and tooltip.
 * - `label`: accessible name of the group.
 * - Styling: `.c-view-switch` in `resources/scss/_item-browser.scss`.
 *
 * @since 1.20
 */
export default {
    name: 'ViewSwitch',
    props: {
        modelValue: { type: String, required: true },
        options: { type: Array, required: true },
        label: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    methods: {
        choose(value) {
            if (value !== this.modelValue) {
                this.$emit('update:modelValue', value);
            }
        },
    },
};
</script>
