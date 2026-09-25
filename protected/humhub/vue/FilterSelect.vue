<template>
    <div
        ref="root"
        class="c-select"
        :class="{ 'is-open': open, 'has-selection': hasSelection, 'is-disabled': isDisabled, 'is-loading': loading }"
    >
        <button
            :id="buttonId"
            ref="button"
            type="button"
            class="c-select__button"
            aria-haspopup="listbox"
            :aria-expanded="open ? 'true' : 'false'"
            :aria-controls="listboxId"
            :aria-label="accessibleName"
            :aria-busy="loading ? 'true' : null"
            :disabled="isDisabled"
            @click="toggle"
            @keydown="onButtonKeydown"
        >
            <span class="c-select__value">{{ displayLabel }}</span>
        </button>
        <span v-if="loading" class="spinner-border spinner-border-sm c-select__spinner" role="status" :aria-label="loadingLabel"></span>
        <i v-else class="ti ti-chevron-up c-select__chevron" aria-hidden="true"></i>
        <button
            type="button"
            class="c-select__clear"
            :disabled="!hasSelection || isDisabled"
            :aria-hidden="hasSelection ? null : 'true'"
            :aria-label="clearLabel"
            :title="clearLabel"
            @click="clear"
        ><i class="ti ti-x" aria-hidden="true"></i></button>
        <ul
            :id="listboxId"
            ref="listbox"
            class="c-select__drawer"
            :class="{ 'is-open': open }"
            role="listbox"
            tabindex="-1"
            :aria-label="accessibleName"
            :aria-activedescendant="open && activeIndex >= 0 ? optionId(activeIndex) : null"
            @keydown="onListKeydown"
        >
            <li
                v-for="(option, index) in choices"
                :id="optionId(index)"
                :key="option.value"
                class="c-select__option"
                :class="{ 'is-selected': option.value === modelValue, 'is-active': index === activeIndex }"
                role="option"
                :aria-selected="option.value === modelValue ? 'true' : 'false'"
                @click="select(option)"
                @mousemove="activeIndex = index"
            >{{ option.label }}</li>
            <li v-if="!choices.length" class="c-select__feedback" role="presentation">{{ emptyLabel }}</li>
        </ul>
    </div>
</template>

<script>
import { i18n } from '@humhub/vue';

let uid = 0;

/**
 * A single-choice dropdown for filter toolbars (`<filter-select>`), after the design system's
 * `c-select`: the placeholder is the "nothing chosen" (all) state; choosing an option shows its
 * label and swaps the chevron for a clear X, which resets the value to `''`.
 *
 * - `v-model` (`modelValue`): the chosen option's `value`, `''` for none. A value that is not
 *   (yet) among the options — e.g. one read from the page URL before remote options arrived —
 *   is shown as is.
 * - Props: `options` (`[{ value, label }]`; an option with the empty value is not listed),
 *   `placeholder` (else the empty option's label, else `label` — the field's name in the
 *   placeholder colour reads as "no choice"), `label` (accessible name, defaults to the
 *   placeholder), `disabled`, `loading` (disabled, with a spinner in place of the chevron),
 *   `id` (of the toggle button, for an external `<label for>`).
 * - Emits `update:modelValue` with the chosen value, or `''` when cleared.
 * - Keyboard and ARIA: the toggle is a button with `aria-haspopup="listbox"`/`aria-expanded`;
 *   Enter, Space, ArrowDown and ArrowUp open it and move focus into the listbox, where the arrow
 *   keys, Home/End and the first letter of a label move the active option
 *   (`aria-activedescendant`), Enter/Space choose it, Escape closes it — focus returns to the
 *   toggle either way — and Tab closes it and moves on. A click outside closes it too.
 * - Styling: `.c-select` in `resources/scss/_page-toolbar.scss` (states `is-open`,
 *   `has-selection`, `is-disabled`).
 *
 * @since 1.20
 */
export default {
    name: 'FilterSelect',
    props: {
        modelValue: { type: String, default: '' },
        options: { type: Array, default: () => [] },
        placeholder: { type: String, default: '' },
        label: { type: String, default: '' },
        disabled: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
        id: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        const key = ++uid;
        return {
            open: false,
            activeIndex: -1,
            listboxId: `filter-select-${key}-listbox`,
            fallbackId: `filter-select-${key}`,
        };
    },
    computed: {
        buttonId() {
            return this.id || this.fallbackId;
        },
        choices() {
            return this.options
                .filter((option) => String(option.value) !== '')
                .map((option) => ({ value: String(option.value), label: String(option.label) }));
        },
        placeholderText() {
            if (this.placeholder) {
                return this.placeholder;
            }
            const empty = this.options.find((option) => String(option.value) === '');
            if (empty) {
                return String(empty.label);
            }
            // The field's name reads as "no choice" in the placeholder colour.
            return this.label;
        },
        selected() {
            return this.choices.find((option) => option.value === this.modelValue) || null;
        },
        hasSelection() {
            return this.modelValue !== '' && this.modelValue !== null && this.modelValue !== undefined;
        },
        displayLabel() {
            if (!this.hasSelection) {
                return this.placeholderText;
            }
            return this.selected ? this.selected.label : this.modelValue;
        },
        accessibleName() {
            return this.label || this.placeholderText || null;
        },
        isDisabled() {
            return this.disabled || this.loading;
        },
        clearLabel() {
            return i18n.t('base', 'Clear selection');
        },
        loadingLabel() {
            return i18n.t('base', 'Loading...');
        },
        emptyLabel() {
            return i18n.t('base', 'No options');
        },
    },
    watch: {
        isDisabled(disabled) {
            if (disabled) {
                this.close(false);
            }
        },
    },
    beforeUnmount() {
        this.unbindOutside();
    },
    methods: {
        optionId(index) {
            return `${this.listboxId}-${index}`;
        },
        toggle() {
            if (this.open) {
                this.close(true);
            } else {
                this.show();
            }
        },
        show(activeIndex) {
            if (this.isDisabled || this.open) {
                return;
            }
            const current = this.choices.findIndex((option) => option.value === this.modelValue);
            this.activeIndex = activeIndex !== undefined ? activeIndex : Math.max(current, 0);
            if (!this.choices.length) {
                this.activeIndex = -1;
            }
            this.open = true;
            this.bindOutside();
            this.$nextTick(() => {
                this.$refs.listbox?.focus();
                this.scrollActiveIntoView();
            });
        },
        close(returnFocus) {
            if (!this.open) {
                return;
            }
            this.open = false;
            this.unbindOutside();
            if (returnFocus) {
                this.$refs.button?.focus();
            }
        },
        select(option) {
            this.close(true);
            if (option.value !== this.modelValue) {
                this.$emit('update:modelValue', option.value);
            }
        },
        clear() {
            this.close(false);
            this.$refs.button?.focus();
            if (this.hasSelection) {
                this.$emit('update:modelValue', '');
            }
        },
        onButtonKeydown(event) {
            if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault();
                const last = this.choices.length - 1;
                this.show(event.key === 'ArrowUp' && !this.hasSelection ? last : undefined);
            }
        },
        onListKeydown(event) {
            const last = this.choices.length - 1;
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.moveTo(Math.min(this.activeIndex + 1, last));
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.moveTo(Math.max(this.activeIndex - 1, 0));
                    break;
                case 'Home':
                    event.preventDefault();
                    this.moveTo(0);
                    break;
                case 'End':
                    event.preventDefault();
                    this.moveTo(last);
                    break;
                case 'Enter':
                case ' ':
                    event.preventDefault();
                    if (this.choices[this.activeIndex]) {
                        this.select(this.choices[this.activeIndex]);
                    } else {
                        this.close(true);
                    }
                    break;
                case 'Escape':
                    event.preventDefault();
                    event.stopPropagation();
                    this.close(true);
                    break;
                case 'Tab':
                    this.close(false);
                    break;
                default:
                    if (event.key.length === 1 && /\S/.test(event.key)) {
                        this.typeAhead(event.key.toLowerCase());
                    }
            }
        },
        moveTo(index) {
            if (index < 0) {
                return;
            }
            this.activeIndex = index;
            this.$nextTick(() => this.scrollActiveIntoView());
        },
        typeAhead(char) {
            const count = this.choices.length;
            for (let step = 1; step <= count; step++) {
                const index = (this.activeIndex + step) % count;
                if (this.choices[index].label.toLowerCase().startsWith(char)) {
                    this.moveTo(index);
                    return;
                }
            }
        },
        scrollActiveIntoView() {
            const node = this.activeIndex >= 0 ? this.$refs.listbox?.children[this.activeIndex] : null;
            if (node && typeof node.scrollIntoView === 'function') {
                node.scrollIntoView({ block: 'nearest' });
            }
        },
        bindOutside() {
            if (this.outsideHandler) {
                return;
            }
            this.outsideHandler = (event) => {
                if (this.$refs.root && !this.$refs.root.contains(event.target)) {
                    this.close(false);
                }
            };
            document.addEventListener('pointerdown', this.outsideHandler, true);
            document.addEventListener('focusin', this.outsideHandler, true);
        },
        unbindOutside() {
            if (!this.outsideHandler) {
                return;
            }
            document.removeEventListener('pointerdown', this.outsideHandler, true);
            document.removeEventListener('focusin', this.outsideHandler, true);
            this.outsideHandler = null;
        },
    },
};
</script>
