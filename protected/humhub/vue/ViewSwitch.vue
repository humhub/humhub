<template>
    <div class="btn-group view-switch" role="group" :aria-label="label">
        <template v-for="option in options" :key="option.value">
            <input
                :id="`${uid}-${option.value}`"
                type="radio"
                class="btn-check"
                :name="uid"
                :value="option.value"
                :checked="option.value === modelValue"
                autocomplete="off"
                @change="choose(option.value)"
            >
            <label
                class="btn btn-ghost btn-icon-only"
                :for="`${uid}-${option.value}`"
                :title="option.label"
            ><i :class="'ti ti-' + option.icon" aria-hidden="true"></i><span class="visually-hidden">{{ option.label }}</span></label>
        </template>
    </div>
</template>

<script>
let uid = 0;

/**
 * A switch between views of the same content (`<view-switch>`), e.g. tiles and a list:
 * Bootstrap's radio toggle buttons (`.btn-check` + `label.btn.btn-ghost.btn-icon-only`) in a
 * `.btn-group`, the active one checked. Arrow keys move between the options, as in any radio
 * group.
 *
 * - `v-model` (`modelValue`): the value of the active option. Choosing the active option again
 *   emits nothing.
 * - `options`: `[{ value, icon, label }]` — `icon` a Tabler name, `label` the option's
 *   accessible name (visually hidden) and tooltip.
 * - `label`: accessible name of the group.
 * - Styling: Bootstrap's; `.view-switch` in `resources/scss/_item-browser.scss` only spaces it.
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
    data() {
        return {
            uid: `view-switch-${++uid}`,
        };
    },
    methods: {
        choose(value) {
            if (value !== this.modelValue) {
                this.$emit('update:modelValue', value);
            }
        },
    },
};
</script>
