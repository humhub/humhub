<template>
    <div class="c-filter-bar-container">
        <form
            :id="barId"
            ref="bar"
            class="c-filter-bar"
            :class="{ 'is-open': open, 'is-collapsing': collapsing }"
            role="search"
            @submit.prevent
        >
            <template v-for="(filter, index) in visibleFilters" :key="filter.key">
                <div
                    :class="['c-filter-bar__item', `c-filter-bar__item--${filter.type}`, `form-search-filter-${filter.key}`, { 'c-filter-bar__item--wide': filter.wide }]"
                    :data-filter-bar-keep="index === keepIndex ? '' : null"
                >
                    <slot :name="`filter-${filter.key}`" :filter="filter" :value="draft[filter.key]" :update="(value) => update(filter.key, value)">
                        <div v-if="filter.type === 'text'" class="c-search-field">
                            <i class="ti ti-search c-search-field__icon" aria-hidden="true"></i>
                            <input
                                :id="inputId(filter)"
                                type="text"
                                class="c-search-field__input"
                                autocomplete="off"
                                :value="draft[filter.key]"
                                :placeholder="filter.placeholder || filter.label || ''"
                                :aria-label="filter.label || filter.placeholder || null"
                                @input="update(filter.key, $event.target.value)"
                            >
                        </div>
                        <FilterSelect
                            v-else-if="filter.type === 'select'"
                            :id="inputId(filter)"
                            :model-value="String(draft[filter.key] ?? '')"
                            :options="optionsOf(filter)"
                            :placeholder="filter.placeholder || ''"
                            :label="filter.label || ''"
                            :loading="isLoading(filter)"
                            @update:model-value="update(filter.key, $event)"
                        />
                        <div v-else-if="filter.type === 'tags'" class="c-filter-tags" role="group" :aria-label="filter.label">
                            <span v-if="filter.label" class="c-filter-tags__label" aria-hidden="true">{{ filter.label }}</span>
                            <button
                                v-for="option in optionsOf(filter)"
                                :key="option.value"
                                type="button"
                                class="c-filter-tags__tag"
                                :class="{ active: isTagActive(filter, option.value) }"
                                :aria-pressed="isTagActive(filter, option.value) ? 'true' : 'false'"
                                @click="toggleTag(filter, option.value)"
                            >{{ option.label }}</button>
                        </div>
                        <div v-else-if="filter.type === 'checkbox'" class="c-filter-check form-check">
                            <input
                                :id="inputId(filter)"
                                class="form-check-input"
                                type="checkbox"
                                :checked="draft[filter.key]"
                                @change="update(filter.key, $event.target.checked)"
                            >
                            <label class="form-check-label" :for="inputId(filter)">{{ filter.label }}</label>
                        </div>
                    </slot>
                </div>
                <button
                    v-if="collapsible && index === toggleAfter"
                    ref="toggle"
                    type="button"
                    class="btn c-icon-button c-icon-button--ghost c-filter-bar__toggle"
                    :class="{ 'is-active': open }"
                    data-filter-bar-toggle
                    :aria-expanded="open ? 'true' : 'false'"
                    :aria-controls="barId"
                    :aria-label="toggleLabel"
                    :title="toggleLabel"
                    @click="onToggle"
                ><i class="ti ti-filter" aria-hidden="true"></i></button>
            </template>
            <Transition name="c-filter-bar-clear">
                <button
                    v-if="resettable"
                    ref="clear"
                    type="button"
                    class="btn c-icon-button c-icon-button--ghost c-filter-bar__clear"
                    data-filter-bar-keep
                    data-filter-bar-clear
                    :aria-label="resetLabel"
                    :title="resetLabel"
                    @click="reset"
                ><i class="ti ti-x" aria-hidden="true"></i></button>
            </Transition>
        </form>
    </div>
</template>

<script>

import { client, i18n, log } from '@humhub/vue';
import FilterSelect from './FilterSelect.vue';
import { defaultValue, defaultValues, isDefault, readValues, writeQuery } from './filter/filterQuery.js';

export const TEXT_DEBOUNCE_MS = 300;

const ANIMATION_MS = 300;

let uid = 0;

const reducedMotion = () => typeof window.matchMedia === 'function'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const same = (filters, a, b) => filters.every((filter) => JSON.stringify(a?.[filter.key]) === JSON.stringify(b?.[filter.key]));

/**
 * A filter bar (after the design system's `c-filter-bar`): renders the filter definitions of a
 * `humhub\components\filter\FilterSet` as a form of its own — nothing is submitted.
 * It keeps the values the controls show (`draft`) apart from the **applied** ones, which are its
 * `v-model`: `update:modelValue` is emitted with a new values object only once a change is
 * applied, so the owner reloads on every emit and never has to debounce itself. Used inside a
 * `PageToolbar`, by `CardDirectory` or on its own (e.g. a file list's filters).
 *
 * - Initial values: `modelValue` (missing keys at their defaults), overridden by what the page
 *   URL carries (`syncUrl`). When they differ from `modelValue`, they are emitted at once, while
 *   the bar is created — an owner can therefore start from `{}` and load in `mounted()`.
 * - URL sync (`syncUrl`, default `true`): applied values are written back with
 *   `history.replaceState` (defaults omitted, other parameters kept, no history entries, so
 *   nothing competes with PJAX's `popstate`; PJAX's own `history.state.url` is kept current).
 * - Text filters apply after `TEXT_DEBOUNCE_MS`, all others at once. A hidden filter is dropped
 *   as soon as a visible one changes.
 * - A `modelValue` changed from outside (to something other than the applied values) replaces
 *   the shown and applied values, without an emit.
 * - `text`: a search field with a search icon (`placeholder`, else `label`, as placeholder;
 *   `label` as accessible name).
 * - `select`: a `FilterSelect` — its `placeholder` is the "all" state, a static option with the
 *   empty value is not listed (its label is the placeholder fallback). With `optionsUrl` it
 *   loads further options (`{results: [{id, name, count?}]}`) after the static `options` and
 *   stays disabled (spinner) until they are there; a failed load leaves it usable with the
 *   static options only.
 * - `tags` toggle buttons; with `multiple` the option with the empty value is "All" and clears
 *   them. `checkbox` a check box. `wide` makes an item take a full row.
 * - Slot `filter-<key>` (`{ filter, value, update }`) replaces one filter's control (inside its
 *   `.c-filter-bar__item` cell); `update(value)` goes through the same apply logic.
 * - A ghost "clear all filters" X slides in at the end of the row while a visible filter
 *   differs from its default, and resets every filter to its default.
 * - Narrow widths (a container query on `.c-filter-bar-container`, so it follows the space the
 *   bar really has, not the viewport): only the first text filter and the clear X stay; the
 *   others collapse behind a funnel toggle (`aria-expanded`), which expands the bar with a
 *   height animation. The CSS decides when the bar collapses — the toggle is only visible then,
 *   and a `ResizeObserver` closes the bar again once it no longer collapses.
 * - `idPrefix`: prefix of the controls' ids (`<idPrefix>-<key>`).
 * - Instance API (template ref): `setFilter(key, value)` sets one filter from outside the bar
 *   (applied like a change in the bar, returns `false` for an unknown key), `reloadOptions()`
 *   re-runs every `optionsUrl` filter's load, for when something outside the bar changed what
 *   the options (or their counts) should be.
 *
 * @since 1.20
 */
export default {
    name: 'FilterBar',
    components: { FilterSelect },
    props: {
        filters: { type: Array, required: true },
        modelValue: { type: Object, default: () => ({}) },
        syncUrl: { type: Boolean, default: true },
        idPrefix: { type: String, default: 'filter' },
    },
    emits: ['update:modelValue'],
    data() {
        const base = { ...defaultValues(this.filters), ...this.modelValue };
        const initial = this.syncUrl ? readValues(this.filters, window.location.search, base) : base;
        return {
            draft: initial,
            remoteOptions: {},
            loading: {},
            open: false,
            collapsing: false,
            barId: `filter-bar-${++uid}`,
        };
    },
    computed: {
        visibleFilters() {
            return this.filters.filter((filter) => !filter.hidden);
        },
        // The item that stays visible while the bar is collapsed: the first search field.
        keepIndex() {
            return this.visibleFilters.findIndex((filter) => filter.type === 'text');
        },
        collapsible() {
            return this.visibleFilters.length > (this.keepIndex === -1 ? 0 : 1);
        },
        // The funnel toggle follows the kept search field (or leads the bar without one).
        toggleAfter() {
            return Math.max(this.keepIndex, 0);
        },
        resettable() {
            return this.visibleFilters.some((filter) => !isDefault(filter, this.draft[filter.key]));
        },
        resetLabel() {
            return i18n.t('base', 'Clear all filters');
        },
        toggleLabel() {
            return this.open ? i18n.t('base', 'Hide filters') : i18n.t('base', 'Show filters');
        },
    },
    watch: {
        draft: 'onDraftChange',
        modelValue(value) {
            if (!same(this.filters, value, this.applied)) {
                clearTimeout(this.debounceTimer);
                this.applied = { ...defaultValues(this.filters), ...value };
                this.draft = { ...this.applied };
                this.writeUrl();
            }
        },
    },
    created() {
        this.debounceTimer = null;
        this.applied = { ...this.draft };
        if (!same(this.filters, this.applied, this.modelValue)) {
            this.$emit('update:modelValue', { ...this.applied });
        }
        this.reloadOptions();
    },
    mounted() {
        if (typeof ResizeObserver === 'function') {
            this.resizeObserver = new ResizeObserver(() => this.sync());
            this.resizeObserver.observe(this.$refs.bar);
        }
    },
    beforeUnmount() {
        clearTimeout(this.debounceTimer);
        clearTimeout(this.settleTimer);
        clearTimeout(this.flipTimer);
        this.resizeObserver?.disconnect();
    },
    methods: {
        setFilter(key, value) {
            if (!this.filters.some((filter) => filter.key === key)) {
                return false;
            }
            this.update(key, value);
            return true;
        },
        reset() {
            this.draft = defaultValues(this.filters);
        },
        reloadOptions() {
            this.filters.filter((filter) => filter.optionsUrl).forEach((filter) => this.loadOptions(filter));
        },
        inputId(filter) {
            return `${this.idPrefix}-${filter.key}`;
        },
        loadOptions(filter) {
            this.loading[filter.key] = true;
            client.get(filter.optionsUrl).then((response) => {
                this.remoteOptions[filter.key] = (response.results || []).map((option) => ({
                    value: String(option.id),
                    label: option.count !== undefined && option.count !== null ? `${option.name} (${option.count})` : option.name,
                }));
            }).catch((response) => {
                log.error(response);
            }).finally(() => {
                this.loading[filter.key] = false;
            });
        },
        isLoading(filter) {
            return this.loading[filter.key] === true;
        },
        optionsOf(filter) {
            return [...(filter.options || []), ...(this.remoteOptions[filter.key] || [])];
        },
        update(key, value) {
            this.draft = { ...this.draft, [key]: value };
        },
        onDraftChange(values) {
            // Cleared unconditionally, before the "nothing changed" early return: a value typed
            // back to what is already applied (e.g. typed then deleted within the debounce
            // window) must cancel a pending debounce, not just skip scheduling a new one -
            // otherwise the stale timer still fires `apply()` later.
            clearTimeout(this.debounceTimer);

            const changed = this.filters.filter((filter) => JSON.stringify(values[filter.key]) !== JSON.stringify(this.applied[filter.key]));
            if (!changed.length) {
                return;
            }

            if (changed.some((filter) => !filter.hidden)) {
                const stale = this.filters.filter((filter) => filter.hidden && !isDefault(filter, values[filter.key]));
                if (stale.length) {
                    // Assigning re-runs this watcher with the hidden filters cleared.
                    this.draft = { ...values, ...Object.fromEntries(stale.map((filter) => [filter.key, defaultValue(filter)])) };
                    return;
                }
            }

            if (changed.every((filter) => filter.type === 'text')) {
                this.debounceTimer = setTimeout(() => this.apply(), TEXT_DEBOUNCE_MS);
            } else {
                this.apply();
            }
        },
        apply() {
            clearTimeout(this.debounceTimer);
            this.applied = { ...this.draft };
            this.writeUrl();
            this.$emit('update:modelValue', { ...this.applied });
        },
        writeUrl() {
            if (!this.syncUrl) {
                return;
            }
            const { pathname, search, hash } = window.location;
            const path = pathname + writeQuery(this.filters, this.applied, search) + hash;
            window.history.replaceState(this.nextHistoryState(path), '', path);
        },
        // PJAX (jquery.pjax.modified.js) keeps its own state object in `history.state`
        // (`{id, url, title, container, fragment, timeout}`) and compares `state.url` against
        // the current location on `popstate` to detect a same-page navigation - replacing the
        // URL without refreshing that `url` field would leave it pointing at the filter values
        // from before this change. Any other kind of state (none, or a plain object without a
        // `url` string) is passed through unchanged - it is none of this component's business.
        nextHistoryState(path) {
            const state = window.history.state;
            if (!state || typeof state !== 'object' || typeof state.url !== 'string') {
                return state;
            }
            return { ...state, url: window.location.origin + path };
        },
        isTagActive(filter, value) {
            const current = this.draft[filter.key];
            if (filter.multiple === true) {
                return value === '' ? current.length === 0 : current.includes(value);
            }
            return current === value;
        },
        toggleTag(filter, value) {
            const current = this.draft[filter.key];
            if (filter.multiple !== true) {
                this.update(filter.key, current === value ? '' : value);
                return;
            }
            if (value === '') {
                this.update(filter.key, []);
                return;
            }
            this.update(filter.key, current.includes(value) ? current.filter((v) => v !== value) : [...current, value]);
        },
        toggleElement() {
            const toggle = this.$refs.toggle;
            return Array.isArray(toggle) ? toggle[0] : toggle;
        },
        // CSS owns the breakpoint (a container query), so "does the bar collapse right now?" is
        // answered by whether it shows the toggle, not by a width kept in step with the SCSS.
        collapsesNow() {
            const toggle = this.toggleElement();
            return Boolean(toggle) && window.getComputedStyle(toggle).display !== 'none';
        },
        onToggle() {
            if (this.collapsesNow()) {
                this.setOpen(!this.open);
            }
        },
        setOpen(open, animate = !reducedMotion()) {
            const bar = this.$refs.bar;
            const from = bar.getBoundingClientRect().height;
            const clear = this.$refs.clear || null;
            const flipping = animate && clear && clear.getClientRects().length > 0;
            const clearFrom = flipping ? clear.getBoundingClientRect() : null;

            // Apply the final layout synchronously, so the clear X can be measured where it lands.
            this.collapsing = false;
            this.open = open;
            bar.classList.remove('is-collapsing');
            bar.classList.toggle('is-open', open);
            const clearTo = flipping ? clear.getBoundingClientRect() : null;

            clearTimeout(this.settleTimer);
            if (!animate) {
                bar.style.maxHeight = '';
                return;
            }

            // While closing, the collapsible items stay in flow for the length of the animation,
            // so the bar shrinks past real content instead of snapping to one row first.
            this.collapsing = !open;
            bar.classList.toggle('is-collapsing', !open);
            if (flipping) {
                this.flipClear(clear, clearFrom, clearTo, open);
            }

            const to = open ? bar.scrollHeight : this.collapsedHeight();
            bar.style.maxHeight = `${from}px`;
            void bar.offsetHeight; // reflow, so the browser animates from `from`
            bar.style.maxHeight = `${to}px`;

            this.settleTimer = setTimeout(() => {
                // An open bar must stop clipping, or a select's drawer would be cut off.
                bar.style.maxHeight = open ? 'none' : '';
                this.collapsing = false;
            }, ANIMATION_MS);
        },
        collapsedHeight() {
            const bar = this.$refs.bar;
            const keeper = bar.querySelector('[data-filter-bar-keep], [data-filter-bar-toggle]');
            const style = window.getComputedStyle(bar);
            const padding = (parseFloat(style.paddingTop) || 0) + (parseFloat(style.paddingBottom) || 0);
            return (keeper ? keeper.offsetHeight : 38) + padding;
        },
        // FLIP the clear X between the row it has when expanded (after the last filter) and the
        // one beside the toggle when collapsed, instead of letting it jump there.
        flipClear(clear, from, to, opening) {
            const dx = from.left - to.left;
            const dy = from.top - to.top;
            if (!dx && !dy) {
                return;
            }
            clear.classList.add('is-flipping');
            clear.style.transform = opening ? `translate(${dx}px, ${dy}px)` : '';
            void clear.offsetHeight;
            clear.style.transform = opening ? '' : `translate(${-dx}px, ${-dy}px)`;
            clearTimeout(this.flipTimer);
            this.flipTimer = setTimeout(() => {
                clear.classList.remove('is-flipping');
                clear.style.transform = '';
            }, ANIMATION_MS);
        },
        // A bar that stops collapsing (grown past the container breakpoint) drops its open state
        // and any inline height, so it cannot be stranded half-open.
        sync() {
            if (this.collapsesNow() || (!this.open && !this.collapsing)) {
                return;
            }
            clearTimeout(this.settleTimer);
            this.setOpen(false, false);
        },
    },
};
</script>
