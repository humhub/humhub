<template>
    <div
        ref="root"
        class="c-select c-picker"
        :class="{ 'is-open': open, 'has-selection': hasSelection, 'is-disabled': disabled, 'is-loading': loading, 'is-multiple': multiple }"
    >
        <div class="c-picker__field" @click="onFieldClick">
            <span v-for="chip in chips" :key="chip.value" class="c-picker__chip">
                <span class="c-picker__chip-label">{{ chip.label }}</span>
                <button
                    type="button"
                    class="c-picker__chip-remove"
                    :disabled="disabled"
                    :aria-label="removeLabel(chip.label)"
                    :title="removeLabel(chip.label)"
                    @click.stop="remove(chip.value)"
                ><i class="ti ti-x" aria-hidden="true"></i></button>
            </span>
            <input
                :id="inputId"
                ref="input"
                type="text"
                class="c-picker__input"
                role="combobox"
                autocomplete="off"
                aria-autocomplete="list"
                :aria-expanded="open ? 'true' : 'false'"
                :aria-controls="listboxId"
                :aria-activedescendant="activeDescendant"
                :aria-label="accessibleName"
                :aria-busy="loading ? 'true' : null"
                :placeholder="inputPlaceholder"
                :value="inputText"
                :disabled="disabled"
                @input="onInput"
                @keydown="onKeydown"
                @focus="onFocus"
            >
        </div>
        <span v-if="loading" class="spinner-border spinner-border-sm c-select__spinner" aria-hidden="true"></span>
        <i v-else class="ti ti-chevron-up c-select__chevron" aria-hidden="true"></i>
        <button
            type="button"
            class="c-select__clear"
            :disabled="!hasSelection || disabled"
            :aria-hidden="hasSelection ? null : 'true'"
            :aria-label="clearLabel"
            :title="clearLabel"
            @click.stop="clear"
        ><i class="ti ti-x" aria-hidden="true"></i></button>
        <ul
            :id="listboxId"
            ref="listbox"
            class="c-select__drawer"
            :class="{ 'is-open': open }"
            role="listbox"
            :aria-label="accessibleName"
            :aria-multiselectable="multiple ? 'true' : null"
            :aria-busy="loading ? 'true' : null"
        >
            <li
                v-for="(option, index) in choices"
                :id="optionId(index)"
                :key="option.custom ? `custom:${option.value}` : option.value"
                class="c-select__option"
                :class="{ 'is-selected': !option.custom && isChosen(option.value), 'is-active': index === activeIndex, 'is-custom': option.custom }"
                role="option"
                :aria-selected="!option.custom && isChosen(option.value) ? 'true' : 'false'"
                @pointerdown.prevent
                @click="choose(option)"
                @mousemove="activeIndex = index"
            >
                <span class="c-picker__option-label">{{ option.custom ? customLabel(option.value) : option.label }}</span>
                <span v-if="option.count !== null" class="c-picker__option-count">{{ option.count }}</span>
            </li>
            <li v-if="feedback" class="c-select__feedback" role="presentation">{{ feedback }}</li>
        </ul>
        <span class="visually-hidden" role="status">{{ open ? statusText : '' }}</span>
    </div>
</template>

<script>
import { client, i18n, log } from '@humhub/vue';

export const SEARCH_DEBOUNCE_MS = 250;

let uid = 0;

const toValues = (value) => {
    if (Array.isArray(value)) {
        return value.map(String).filter((entry) => entry !== '');
    }
    return value === null || value === undefined || value === '' ? [] : [String(value)];
};

const normalize = (option) => ({
    value: String(option.value),
    label: String(option.label ?? option.value),
    count: option.count !== undefined && option.count !== null ? Number(option.count) : null,
});

const contains = (label, term) => term === '' || label.toLocaleLowerCase().includes(term.toLocaleLowerCase());

/**
 * A searchable picker for filter toolbars (`<filter-picker>`, the `picker` filter type of
 * `FilterBar`), after the WAI-ARIA combobox pattern: a text input (`role="combobox"`) with a
 * listbox of suggestions; with `multiple` the chosen values stand as removable chips before it.
 *
 * - `v-model` (`modelValue`): the chosen value (`''` for none), with `multiple` the array of
 *   chosen values (`[]` for none).
 * - Suggestions: the static `options` (`[{ value, label, count? }]`; an option with the empty
 *   value is not listed), matched against the typed text in the browser, followed by those of
 *   `optionsUrl` (an endpoint answering `{results: [{id, name, count?}]}`): on focus (and when
 *   opened) its first page — which values and how many the server decides —, and while typing
 *   `optionsUrl` + `q=<typed text>` after `SEARCH_DEBOUNCE_MS`, an answer to an older text
 *   being dropped. Every answer is kept per text until `reloadKey` changes. A `count` is shown
 *   beside the label. With `multiple` the chosen values are no suggestions.
 * - Labels: a chosen value shows the label of the option it came from; a value that was never
 *   among the loaded options (read from the page URL) shows as is until an answer carries it.
 * - Single mode behaves like a searchable select: the input shows the chosen label and, once
 *   typed into, the search; choosing closes the listbox. With `multiple` it stays open after a
 *   choice to add the next one.
 * - `allowCustom` (single mode only): the typed text itself can be chosen — for a filter the
 *   server matches by a part of the text. While a text is typed that is none of the
 *   suggestions' values, a "Use “…”" entry leads the listbox (active right after typing), and
 *   Enter applies the typed text whenever no suggestion is active (also with the listbox closed).
 * - Props: `options`, `optionsUrl`, `multiple`, `allowCustom`, `placeholder` (else `label` — the field's name
 *   reads as "no choice"), `label` (accessible name, defaults to the placeholder), `disabled`,
 *   `id` (of the input, for an external `<label for>`), `reloadKey` (a change drops the loaded
 *   suggestions, e.g. `FilterBar`'s `reloadOptions()`).
 * - Emits `update:modelValue` with the new value (single) or the new array (`multiple`).
 * - Keyboard: typing, ArrowDown and ArrowUp open the listbox; ArrowDown/ArrowUp, Home/End move
 *   the active option (`aria-activedescendant`, focus stays in the input), Enter chooses it,
 *   Escape closes the listbox (and, when closed, drops the typed text), Tab closes it and moves
 *   on, Backspace in an empty input removes the last chip (`multiple`). A click outside closes it.
 *   The clear X resets the value (`''` / `[]`).
 * - Styling: `.c-select` (drawer, options, clear X, chevron, spinner) and `.c-picker` (the field
 *   with the chips and the input) in `resources/scss/_page-toolbar.scss`.
 *
 * @since 1.20
 */
export default {
    name: 'FilterPicker',
    props: {
        modelValue: { type: [String, Array], default: '' },
        options: { type: Array, default: () => [] },
        optionsUrl: { type: String, default: null },
        multiple: { type: Boolean, default: false },
        allowCustom: { type: Boolean, default: false },
        placeholder: { type: String, default: '' },
        label: { type: String, default: '' },
        disabled: { type: Boolean, default: false },
        id: { type: String, default: null },
        reloadKey: { type: [Number, String], default: 0 },
    },
    emits: ['update:modelValue'],
    data() {
        const key = ++uid;
        return {
            open: false,
            activeIndex: -1,
            // The typed text, and (single mode) whether the input shows it instead of the choice.
            query: '',
            editing: false,
            // The last applied answer of `optionsUrl` and the text it answered (`null`: none yet).
            remote: [],
            remoteTerm: null,
            loading: false,
            failed: false,
            // Labels of values once seen among the options, by value.
            labels: {},
            listboxId: `filter-picker-${key}-listbox`,
            fallbackId: `filter-picker-${key}`,
        };
    },
    computed: {
        inputId() {
            return this.id || this.fallbackId;
        },
        values() {
            return this.multiple ? toValues(this.modelValue) : toValues(this.modelValue).slice(0, 1);
        },
        hasSelection() {
            return this.values.length > 0;
        },
        term() {
            return this.query.trim();
        },
        staticOptions() {
            return this.options.filter((option) => String(option.value) !== '').map(normalize);
        },
        suggestions() {
            const seen = new Set(this.multiple ? this.values : []);
            // An answer to another text than the typed one (the search is still pending) is
            // narrowed to the typed text meanwhile.
            const remote = this.remoteTerm === this.term ? this.remote : this.remote.filter((option) => contains(option.label, this.term));
            return [...this.staticOptions.filter((option) => contains(option.label, this.term)), ...remote]
                .filter((option) => {
                    if (seen.has(option.value)) {
                        return false;
                    }
                    seen.add(option.value);
                    return true;
                });
        },
        // The typed text as a choice of its own (`allowCustom`), unless a suggestion carries it.
        customChoice() {
            if (!this.allowCustom || this.multiple || this.term === '' || this.suggestions.some((option) => option.value === this.term)) {
                return null;
            }
            return { value: this.term, label: this.term, count: null, custom: true };
        },
        choices() {
            return this.customChoice ? [this.customChoice, ...this.suggestions] : this.suggestions;
        },
        chips() {
            return this.multiple ? this.values.map((value) => ({ value, label: this.labelOf(value) })) : [];
        },
        inputText() {
            if (this.multiple || this.editing) {
                return this.query;
            }
            return this.hasSelection ? this.labelOf(this.values[0]) : '';
        },
        placeholderText() {
            return this.placeholder || this.label;
        },
        inputPlaceholder() {
            if (this.multiple) {
                return this.hasSelection ? '' : this.placeholderText;
            }
            // While typing over a choice, the choice stays visible as placeholder.
            return this.editing && this.hasSelection ? this.labelOf(this.values[0]) : this.placeholderText;
        },
        activeDescendant() {
            return this.open && this.activeIndex >= 0 && this.activeIndex < this.choices.length ? this.optionId(this.activeIndex) : null;
        },
        accessibleName() {
            return this.label || this.placeholder || null;
        },
        feedback() {
            if (this.suggestions.length) {
                return null;
            }
            if (this.loading) {
                return this.loadingLabel;
            }
            return this.term !== '' ? i18n.t('base', 'No results found!') : i18n.t('base', 'No options');
        },
        statusText() {
            return this.feedback || '';
        },
        clearLabel() {
            return i18n.t('base', 'Clear selection');
        },
        loadingLabel() {
            return i18n.t('base', 'Loading...');
        },
    },
    watch: {
        disabled(disabled) {
            if (disabled) {
                this.close();
            }
        },
        options: {
            immediate: true,
            handler(options) {
                this.remember(options.map(normalize));
            },
        },
        choices(choices) {
            if (this.activeIndex >= choices.length) {
                this.activeIndex = choices.length - 1;
            }
        },
        reloadKey() {
            this.cache.clear();
            this.remote = [];
            this.remoteTerm = null;
            if (this.open) {
                this.load(this.term);
            }
        },
    },
    created() {
        // Answers by typed text (`''`: the first page), a plain Map: nothing renders from it.
        this.cache = new Map();
        this.requestSeq = 0;
        this.searchTimer = null;
    },
    beforeUnmount() {
        clearTimeout(this.searchTimer);
        this.unbindOutside();
    },
    methods: {
        optionId(index) {
            return `${this.listboxId}-${index}`;
        },
        labelOf(value) {
            return this.labels[value] ?? value;
        },
        remember(options) {
            options.forEach((option) => {
                this.labels[option.value] = option.label;
            });
        },
        isChosen(value) {
            return this.values.includes(value);
        },
        customLabel(text) {
            return i18n.t('base', 'Use “{text}”', { text });
        },
        removeLabel(label) {
            return i18n.t('base', 'Remove {label}', { label });
        },
        requestUrl(term) {
            if (term === '') {
                return this.optionsUrl;
            }
            return `${this.optionsUrl}${this.optionsUrl.includes('?') ? '&' : '?'}q=${encodeURIComponent(term)}`;
        },
        // Loads the suggestions for a text; only the answer to the latest request is applied.
        load(term) {
            if (!this.optionsUrl) {
                return;
            }
            clearTimeout(this.searchTimer);
            const seq = ++this.requestSeq;
            if (this.cache.has(term)) {
                this.remote = this.cache.get(term);
                this.remoteTerm = term;
                this.loading = false;
                this.failed = false;
                return;
            }
            this.loading = true;
            client.get(this.requestUrl(term)).then((response) => {
                if (seq !== this.requestSeq) {
                    return;
                }
                const options = (response.results || []).map((result) => normalize({ value: result.id, label: result.name, count: result.count }));
                this.cache.set(term, options);
                this.remember(options);
                this.remote = options;
                this.remoteTerm = term;
                this.failed = false;
            }).catch((response) => {
                if (seq !== this.requestSeq) {
                    return;
                }
                this.failed = true;
                log.error(response);
            }).finally(() => {
                if (seq === this.requestSeq) {
                    this.loading = false;
                }
            });
        },
        // The first page is fetched once the input gets the focus, before anything is typed.
        onFocus() {
            if (this.optionsUrl && this.remoteTerm === null && !this.loading) {
                this.load('');
            }
        },
        onFieldClick() {
            if (this.disabled) {
                return;
            }
            this.$refs.input?.focus();
            this.show(-1);
        },
        onInput(event) {
            this.query = event.target.value;
            this.editing = true;
            this.search();
            this.show(0, false);
            this.activeIndex = 0;
        },
        search() {
            if (!this.optionsUrl) {
                return;
            }
            clearTimeout(this.searchTimer);
            const term = this.term;
            if (term === '' || this.cache.has(term)) {
                this.load(term);
                return;
            }
            // Pending: an answer to what was typed before is stale from now on.
            this.requestSeq++;
            this.loading = true;
            this.searchTimer = setTimeout(() => this.load(term), SEARCH_DEBOUNCE_MS);
        },
        show(activeIndex = -1, load = true) {
            if (this.disabled || this.open) {
                return;
            }
            this.open = true;
            this.activeIndex = activeIndex;
            this.bindOutside();
            if (load && this.optionsUrl && this.remoteTerm !== this.term && !this.loading) {
                this.load(this.term);
            }
        },
        close() {
            clearTimeout(this.searchTimer);
            if (this.loading && this.remoteTerm !== this.term) {
                // A pending search is of no use any more.
                this.requestSeq++;
                this.loading = false;
            }
            this.query = '';
            this.editing = false;
            if (!this.open) {
                return;
            }
            this.open = false;
            this.activeIndex = -1;
            this.unbindOutside();
        },
        choose(option) {
            this.remember([option]);
            if (this.multiple) {
                this.query = '';
                this.search();
                this.$refs.input?.focus();
                if (!this.values.includes(option.value)) {
                    this.$emit('update:modelValue', [...this.values, option.value]);
                }
                return;
            }
            this.close();
            if (option.value !== this.values[0]) {
                this.$emit('update:modelValue', option.value);
            }
        },
        remove(value) {
            this.$refs.input?.focus();
            this.$emit('update:modelValue', this.values.filter((entry) => entry !== value));
        },
        clear() {
            this.query = '';
            this.editing = false;
            this.$refs.input?.focus();
            if (this.hasSelection) {
                this.$emit('update:modelValue', this.multiple ? [] : '');
            }
        },
        moveTo(index) {
            if (index < 0 || !this.choices.length) {
                return;
            }
            this.activeIndex = Math.min(index, this.choices.length - 1);
            this.$nextTick(() => this.scrollActiveIntoView());
        },
        onKeydown(event) {
            const last = this.choices.length - 1;
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    if (!this.open) {
                        this.show(0);
                    } else {
                        this.moveTo(Math.min(this.activeIndex + 1, last));
                    }
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    if (!this.open) {
                        this.show(last);
                    } else {
                        this.moveTo(Math.max(this.activeIndex - 1, 0));
                    }
                    break;
                case 'Home':
                case 'End':
                    if (this.open && this.choices.length) {
                        event.preventDefault();
                        this.moveTo(event.key === 'Home' ? 0 : last);
                    }
                    break;
                case 'Enter':
                    if (this.open || this.customChoice) {
                        event.preventDefault();
                        const active = this.open ? this.choices[this.activeIndex] : null;
                        if (active || this.customChoice) {
                            this.choose(active || this.customChoice);
                        }
                    }
                    break;
                case 'Escape':
                    if (this.open || this.query !== '') {
                        // Handled here: a surrounding panel must not close as well.
                        event.preventDefault();
                        event.stopPropagation();
                        if (this.open) {
                            this.open = false;
                            this.activeIndex = -1;
                            this.unbindOutside();
                        } else {
                            this.close();
                        }
                    }
                    break;
                case 'Backspace':
                    if (this.multiple && this.query === '' && this.hasSelection) {
                        event.preventDefault();
                        this.remove(this.values[this.values.length - 1]);
                    }
                    break;
                case 'Tab':
                    this.close();
                    break;
                default:
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
                    this.close();
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
