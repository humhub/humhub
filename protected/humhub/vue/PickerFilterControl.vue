<template>
    <div
        ref="root"
        class="c-select c-picker c-picker-filter"
        :class="[block, { 'is-open': open, 'has-selection': hasValue, 'is-disabled': resolving, 'is-loading': resolving, 'is-multiple': multiple }]"
    >
        <div class="c-picker__field" @click="onFieldClick">
            <template v-if="multiple">
                <i v-if="icon" class="ti c-picker-filter__icon" :class="[icon, `${block}__icon`]" aria-hidden="true"></i>
                <span
                    v-for="chip in chips"
                    :key="chip.id"
                    class="c-picker__chip c-picker-filter__chip"
                    :class="`${block}__chip`"
                    :title="chipTitle(chip)"
                >
                    <span class="c-picker-filter__avatar" :class="`${block}__avatar`" aria-hidden="true">
                        <slot v-if="chip.item" name="chip" :item="chip.item"></slot>
                    </span>
                    <span class="c-picker__chip-label">{{ chip.label }}</span>
                    <button
                        type="button"
                        class="c-picker__chip-remove"
                        :aria-label="removeLabel(chip.label)"
                        :title="removeLabel(chip.label)"
                        @click.stop="remove(chip.id)"
                    ><i class="ti ti-x" aria-hidden="true"></i></button>
                </span>
            </template>
            <span
                v-else-if="hasValue && !resolving"
                class="c-picker__chip c-picker-filter__chip"
                :class="`${block}__chip`"
                :title="chipTitle(chips[0])"
                role="group"
                :aria-label="accessibleName"
            >
                <span class="c-picker-filter__avatar" :class="`${block}__avatar`" aria-hidden="true">
                    <slot v-if="chips[0].item" name="chip" :item="chips[0].item"></slot>
                </span>
                <span class="c-picker__chip-label">{{ chips[0].label }}</span>
                <button
                    :id="inputId"
                    ref="remove"
                    type="button"
                    class="c-picker__chip-remove"
                    :aria-label="removeLabel(chips[0].label)"
                    :title="removeLabel(chips[0].label)"
                    @click.stop="remove(chips[0].id)"
                ><i class="ti ti-x" aria-hidden="true"></i></button>
            </span>
            <i v-if="!multiple && !(hasValue && !resolving) && icon" class="ti c-picker-filter__icon" :class="[icon, `${block}__icon`]" aria-hidden="true"></i>
            <input
                v-if="multiple || !(hasValue && !resolving)"
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
                :aria-busy="resolving || searching ? 'true' : null"
                :placeholder="multiple && hasValue ? '' : placeholder"
                :value="query"
                :disabled="resolving"
                @input="onInput"
                @keydown="onKeydown"
            >
        </div>
        <span v-if="resolving" class="spinner-border spinner-border-sm c-select__spinner" aria-hidden="true"></span>
        <ul
            :id="listboxId"
            ref="listbox"
            class="c-select__drawer"
            :class="{ 'is-open': open }"
            role="listbox"
            :aria-label="accessibleName"
            :aria-busy="searching ? 'true' : null"
        >
            <li
                v-for="(result, index) in results"
                :id="optionId(index)"
                :key="idOf(result.id)"
                class="c-select__option c-picker-filter__option"
                :class="[`${block}__option`, { 'is-active': index === activeIndex }]"
                role="option"
                :aria-selected="index === activeIndex ? 'true' : 'false'"
                @pointerdown.prevent
                @click="choose(result)"
                @mousemove="activeIndex = index"
            >
                <span class="c-picker-filter__avatar" :class="`${block}__avatar`" aria-hidden="true">
                    <slot name="option" :item="result"></slot>
                </span>
                <span class="c-picker-filter__name" :class="`${block}__name`">{{ itemLabel(result) }}</span>
                <span v-if="$slots['option-detail']" class="c-picker-filter__detail" :class="`${block}__detail`">
                    <span class="visually-hidden">, </span>
                    <slot name="option-detail" :item="result"></slot>
                </span>
            </li>
            <li v-if="feedback" class="c-select__feedback" role="presentation">{{ feedback }}</li>
        </ul>
        <span class="visually-hidden" role="status">{{ open ? (feedback || '') : '' }}</span>
    </div>
</template>

<script>
import { i18n, log } from '@humhub/vue';
import { TEXT_DEBOUNCE_MS } from './FilterBar.vue';

/**
 * The typing pause before a search: the one of `FilterBar`'s text filters, so typing into any
 * filter of a bar feels the same.
 */
export const SEARCH_DEBOUNCE_MS = TEXT_DEBOUNCE_MS;

/**
 * The most suggestions one search shows (passed to `search()` as its page size).
 */
export const MAX_SUGGESTIONS = 8;

let uid = 0;

const idOf = (value) => (value === null || value === undefined ? '' : String(value));

// A record id as the server takes it: a positive integer, without a sign or leading zeros.
const ID_PATTERN = /^[1-9]\d*$/;

/**
 * The shared combobox of the filter types whose value is one or several record ids, searched by
 * name — `<picker-filter-control>`, a core component, registered globally like every top-level
 * one of `protected/humhub/vue/`. The user module's `UserFilterControl` (the `user` type), the
 * space module's `SpaceFilterControl` (the `space` type) and the topic module's
 * `TopicFilterControl` (the `topic` type) are built on it, each supplying the requests, the label
 * of an item and its image.
 *
 * A module builds a filter type of its own the same way: a top-level component of its `vue/`
 * (say `TopicFilterControl.vue`) that takes the filter-type contract (`filter`, `modelValue`,
 * `inputId`, emitting `update:modelValue`), renders `<PickerFilterControl>` by name — resolved
 * from the global registry, never imported, so the core stays in the core bundle — passing
 * those through with its `search(text, pageSize)`, `resolve(ids)`, `itemLabel`, `icon` and
 * `block` (and `multiple` from the definition), fills the `option`/`chip` slots with the image
 * of an item, and is registered as the type in the module's `vue/index.js`
 * (`registerFilterType('topic', 'TopicFilterControl')`); the PHP side is a filter whose
 * definition has that `type`.
 *
 * After the WAI-ARIA combobox pattern, styled as a `FilterPicker` (`.c-select`, `.c-picker`,
 * `.c-picker-filter` in `_page-toolbar.scss`, plus the control's own `block` class and its
 * `<block>__icon|chip|avatar|option|name|detail` element classes):
 *
 * - Props: `filter` (the definition: `placeholder`, else `label`, as placeholder; `label` as
 *   accessible name), `modelValue` (an id as a string, `''` for none; with `multiple` an array
 *   of them, `[]` for none — the values `FilterBar` reads from the URL), `inputId` (the id of
 *   the focusable control), `multiple`, `search(text, pageSize)` and `resolve(ids)` (each a
 *   `Promise` of the items, `{ id, … }`; `resolve()` must answer every id it knows — page size
 *   `ids.length`), `itemLabel(item)`, `itemTitle(item)` (optional: a chip's tooltip, none when
 *   it answers nothing), `icon` (a Tabler class leading the field),
 *   `block`. Emits `update:modelValue` with the new value (ids as strings).
 * - Search: a typed text (trimmed, not empty) is searched after `SEARCH_DEBOUNCE_MS`; only the
 *   answer to the latest text is shown, its first suggestion active; with `multiple` the chosen
 *   items are not suggested again. A suggestion is the `option` slot (an image), the label and
 *   the `option-detail` slot (after the label in a `.c-picker-filter__detail`, behind a
 *   visually hidden comma, so part of the option's accessible name — the container of a
 *   topic, say).
 * - Without `multiple`: a search field without a value, a chip (the `chip` slot, the label, a
 *   remove X — the focusable control then — which empties the value and returns the focus to the
 *   search field) with one, a `group` named after the filter's `label`. With `multiple`: a chip
 *   per chosen item followed by the search field; choosing appends, an X removes that one and
 *   returns the focus to the search field.
 * - Ids the control has not seen among its suggestions (from the page URL) are resolved with one
 *   `resolve()` of all of them — the field is disabled with a spinner meanwhile —; ids the
 *   answer does not name are dropped (the remaining ones are emitted), what is no id at all (a
 *   positive integer) at once, without a request.
 * - Keyboard: ArrowDown/ArrowUp open the listbox and, like Home/End, move the active suggestion
 *   (`aria-activedescendant`, `aria-selected`; the focus stays in the input), Enter chooses it,
 *   Escape closes the listbox and, pressed again, drops the typed text, Tab closes it and moves
 *   on. A click outside closes it.
 * - A failed request is logged; the control stays usable (an id it could not resolve shows as
 *   the id, and can be removed).
 *
 * Its messages are of the `base` category (those of `FilterPicker`), which every page with a
 * filter bar loads already.
 *
 * @since 1.20
 */
export default {
    name: 'PickerFilterControl',
    props: {
        filter: { type: Object, required: true },
        modelValue: { type: [String, Number, Array], default: '' },
        inputId: { type: String, default: null },
        multiple: { type: Boolean, default: false },
        search: { type: Function, required: true },
        resolve: { type: Function, required: true },
        itemLabel: { type: Function, required: true },
        itemTitle: { type: Function, default: null },
        icon: { type: String, default: null },
        block: { type: String, required: true },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            query: '',
            open: false,
            activeIndex: -1,
            results: [],
            searching: false,
            resolving: false,
            // Items seen among the suggestions or resolved, by id: a value of them needs no request.
            known: {},
            listboxId: `picker-filter-${++uid}-listbox`,
        };
    },
    computed: {
        ids() {
            const value = this.modelValue;
            if (Array.isArray(value)) {
                return value.map(idOf).filter((id) => id !== '');
            }
            const id = idOf(value);
            if (id === '') {
                return [];
            }
            return this.multiple ? id.split(',').filter((part) => part !== '') : [id];
        },
        // The comparable form of the value, watched instead of the (new with every change) array.
        valueKey() {
            return this.ids.join(',');
        },
        hasValue() {
            return this.ids.length > 0;
        },
        chips() {
            return this.ids
                .map((id) => ({ id, item: this.known[id] || null }))
                // While resolving, an id not known yet is not shown as a bare number.
                .filter((chip) => chip.item || !this.resolving)
                .map((chip) => ({ ...chip, label: chip.item ? this.itemLabel(chip.item) : chip.id }));
        },
        term() {
            return this.query.trim();
        },
        placeholder() {
            return this.filter.placeholder || this.filter.label || '';
        },
        accessibleName() {
            return this.filter.label || this.filter.placeholder || null;
        },
        activeDescendant() {
            return this.open && this.activeIndex >= 0 && this.activeIndex < this.results.length ? this.optionId(this.activeIndex) : null;
        },
        feedback() {
            if (this.results.length) {
                return null;
            }
            return this.searching ? i18n.t('base', 'Loading...') : i18n.t('base', 'No results found!');
        },
    },
    watch: {
        valueKey() {
            this.show();
            if (this.pendingFocus) {
                // After a choice (single: the chip's X) or a removal (the input): whichever the
                // applied value renders keeps the focus in the control.
                this.pendingFocus = false;
                this.$nextTick(() => (this.$refs.input || this.$refs.remove)?.focus());
            }
        },
    },
    created() {
        this.searchSeq = 0;
        this.resolveSeq = 0;
        this.searchTimer = null;
        this.pendingFocus = false;
        // Not an immediate watcher: that would run before the fields above exist.
        this.show();
    },
    beforeUnmount() {
        clearTimeout(this.searchTimer);
        // A late answer of a search or a resolve is stale: it must neither open the list (and
        // bind document listeners of an instance that is gone) nor emit.
        this.searchSeq++;
        this.resolveSeq++;
        this.unbindOutside();
    },
    methods: {
        idOf,
        optionId(index) {
            return `${this.listboxId}-${index}`;
        },
        chipTitle(chip) {
            return (chip.item && this.itemTitle && this.itemTitle(chip.item)) || null;
        },
        removeLabel(label) {
            return i18n.t('base', 'Remove {label}', { label });
        },
        emitIds(ids) {
            this.$emit('update:modelValue', this.multiple ? ids : (ids[0] ?? ''));
        },
        // The items a (new) value names: known, resolved, or dropped.
        show() {
            // A resolve of an older value is of no use any more.
            this.resolveSeq++;
            this.resolving = false;
            const ids = this.ids;
            const valid = ids.filter((id) => ID_PATTERN.test(id));
            if (valid.length !== ids.length) {
                // No record id at all (a mangled link): the server would refuse it, no need to ask.
                this.emitIds(valid);
                return;
            }
            const unknown = ids.filter((id) => !this.known[id]);
            if (unknown.length) {
                this.resolveIds(ids, unknown);
            }
        },
        resolveIds(ids, unknown) {
            const seq = this.resolveSeq;
            this.resolving = true;
            this.resolve(unknown).then((items) => {
                if (seq !== this.resolveSeq) {
                    return;
                }
                (items || []).forEach((item) => (this.known[idOf(item.id)] = item));
                const missing = unknown.filter((id) => !this.known[id]);
                if (missing.length) {
                    // Nothing the caller may see: the server would refuse these ids.
                    this.emitIds(ids.filter((id) => !missing.includes(id)));
                }
            }).catch((response) => {
                if (seq === this.resolveSeq) {
                    log.error(response);
                }
            }).finally(() => {
                if (seq === this.resolveSeq) {
                    this.resolving = false;
                }
            });
        },
        onFieldClick() {
            if (!this.resolving) {
                this.$refs.input?.focus();
            }
        },
        onInput(event) {
            this.query = event.target.value;
            this.runSearch();
        },
        runSearch() {
            clearTimeout(this.searchTimer);
            // From now on an answer to what was typed before is stale.
            const seq = ++this.searchSeq;
            const term = this.term;
            if (term === '') {
                this.searching = false;
                this.results = [];
                this.close();
                return;
            }
            this.searching = true;
            this.searchTimer = setTimeout(() => this.load(term, seq), SEARCH_DEBOUNCE_MS);
        },
        load(term, seq) {
            this.search(term, MAX_SUGGESTIONS).then((items) => {
                if (seq !== this.searchSeq) {
                    return;
                }
                const chosen = this.multiple ? this.ids : [];
                this.results = (items || []).filter((item) => !chosen.includes(idOf(item.id)));
                this.results.forEach((item) => (this.known[idOf(item.id)] = item));
                // Newer suggestions while open: the active one of the older is gone.
                this.activeIndex = this.results.length ? 0 : -1;
            }).catch((response) => {
                if (seq !== this.searchSeq) {
                    return;
                }
                log.error(response);
                this.results = [];
                this.activeIndex = -1;
            }).finally(() => {
                if (seq === this.searchSeq) {
                    this.searching = false;
                    this.openList();
                }
            });
        },
        openList() {
            if (this.open || this.term === '') {
                return;
            }
            this.open = true;
            this.activeIndex = this.results.length ? 0 : -1;
            this.bindOutside();
        },
        close() {
            if (!this.open) {
                return;
            }
            this.open = false;
            this.activeIndex = -1;
            this.unbindOutside();
        },
        choose(item) {
            clearTimeout(this.searchTimer);
            this.searchSeq++;
            this.searching = false;
            const id = idOf(item.id);
            this.known[id] = item;
            this.query = '';
            this.results = [];
            this.close();
            const ids = this.multiple ? (this.ids.includes(id) ? this.ids : [...this.ids, id]) : [id];
            this.pendingFocus = ids.join(',') !== this.valueKey;
            this.emitIds(ids);
        },
        remove(id) {
            // The input is back (single) or stays focused (multiple) once the owner applied the value.
            this.pendingFocus = true;
            this.emitIds(this.ids.filter((entry) => entry !== id));
        },
        moveTo(index) {
            if (!this.results.length) {
                return;
            }
            this.activeIndex = Math.max(0, Math.min(index, this.results.length - 1));
            this.$nextTick(() => this.scrollActiveIntoView());
        },
        onKeydown(event) {
            const last = this.results.length - 1;
            switch (event.key) {
                case 'ArrowDown':
                case 'ArrowUp':
                    event.preventDefault();
                    if (!this.open) {
                        this.openList();
                        if (event.key === 'ArrowUp') {
                            this.moveTo(last);
                        }
                    } else {
                        this.moveTo(this.activeIndex + (event.key === 'ArrowDown' ? 1 : -1));
                    }
                    break;
                case 'Home':
                case 'End':
                    if (this.open && this.results.length) {
                        event.preventDefault();
                        this.moveTo(event.key === 'Home' ? 0 : last);
                    }
                    break;
                case 'Enter':
                    if (this.open) {
                        event.preventDefault();
                        const active = this.results[this.activeIndex];
                        if (active) {
                            this.choose(active);
                        }
                    }
                    break;
                case 'Escape':
                    if (this.open || this.query !== '') {
                        // Handled here: a surrounding panel must not close as well.
                        event.preventDefault();
                        event.stopPropagation();
                        if (this.open) {
                            this.close();
                        } else {
                            this.query = '';
                            this.runSearch();
                        }
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
