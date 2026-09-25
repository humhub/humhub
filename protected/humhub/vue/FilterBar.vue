<template>
    <div class="c-filter-bar-container">
        <form
            :id="barId"
            ref="bar"
            class="c-filter-bar"
            :class="{ 'is-open': open, 'is-collapsing': collapsing, 'is-animating': animating, 'has-panel': hasPanel }"
            role="search"
            @submit.prevent
        >
            <template v-for="entry in rowEntries" :key="entry.key">
                <div
                    v-if="entry.filter && hasControl(entry.filter)"
                    :class="itemClass(entry.filter)"
                    :data-filter-bar-keep="entry.keep ? '' : null"
                >
                    <slot :name="`filter-${entry.filter.key}`" :filter="entry.filter" :value="draft[entry.filter.key]" :update="(value) => update(entry.filter.key, value)">
                        <FilterControl
                            :filter="entry.filter"
                            :model-value="draft[entry.filter.key]"
                            :input-id="inputId(entry.filter)"
                            :options="optionsOf(entry.filter)"
                            :loading="isLoading(entry.filter)"
                            :component="customType(entry.filter)"
                            @update:model-value="update(entry.filter.key, $event)"
                        />
                    </slot>
                </div>
                <button
                    v-else-if="!entry.filter"
                    ref="toggle"
                    type="button"
                    class="btn c-icon-button c-icon-button--ghost c-filter-bar__toggle"
                    :class="{ 'is-active': open, 'c-filter-bar__toggle--labeled': hasPanel }"
                    data-filter-bar-toggle
                    :aria-expanded="open ? 'true' : 'false'"
                    :aria-controls="hasPanel ? panelId : barId"
                    :aria-label="hasPanel ? null : toggleLabel"
                    :title="toggleLabel"
                    @click="onToggle"
                >
                    <i class="ti ti-filter" aria-hidden="true"></i>
                    <template v-if="hasPanel">
                        <span class="c-filter-bar__toggle-label">{{ panelLabel }}</span>
                        <span v-if="activePanelCount" class="c-filter-bar__count" aria-hidden="true">{{ activePanelCount }}</span>
                        <span v-if="activePanelCount" class="visually-hidden">{{ activePanelCountLabel }}</span>
                    </template>
                </button>
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
            <div
                v-if="hasPanel"
                :id="panelId"
                ref="panel"
                class="c-filter-bar__panel"
                role="group"
                :aria-label="panelLabel"
                @keydown.esc="onPanelEscape"
            >
                <template v-for="filter in panelFilters" :key="filter.key">
                    <div v-if="hasControl(filter)" :class="itemClass(filter)">
                        <slot :name="`filter-${filter.key}`" :filter="filter" :value="draft[filter.key]" :update="(value) => update(filter.key, value)">
                            <FilterControl
                                :filter="filter"
                                :model-value="draft[filter.key]"
                                :input-id="inputId(filter)"
                                :options="optionsOf(filter)"
                                :loading="isLoading(filter)"
                                :component="customType(filter)"
                                @update:model-value="update(filter.key, $event)"
                            />
                        </slot>
                    </div>
                </template>
            </div>
        </form>
    </div>
</template>

<script>

import { client, getFilterType, i18n, isRegistered, log } from '@humhub/vue';
import FilterControl from './filter/FilterControl.vue';
import { defaultValue, defaultValues, fixedSignature, isDefault, readValues, writeQuery } from './filter/filterQuery.js';

export const TEXT_DEBOUNCE_MS = 300;

const ANIMATION_MS = 300;

const CORE_TYPES = ['text', 'select', 'tags', 'checkbox'];

let uid = 0;

const reducedMotion = () => typeof window.matchMedia === 'function'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const same = (keys, a, b) => keys.every((key) => JSON.stringify(a?.[key]) === JSON.stringify(b?.[key]));

const keysOf = (filters, fixed) => [...new Set([...filters.map((filter) => filter.key), ...Object.keys(fixed || {})])];

const isPanel = (filter) => filter.placement === 'panel';

/**
 * A filter bar (after the design system's `c-filter-bar`): renders a list's filter definitions
 * (`humhub\components\listing\FilterDefinition`) as a form of its own — nothing is submitted.
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
 * - `fixed` (`{ key: value }`): values set by the embedding page or component (a picker's
 *   `spaceId`) — part of every emitted value object, never rendered, never read from or
 *   written to the URL, never counted as set, kept by "clear all"; a definition whose key is
 *   fixed is not rendered either. A changed `fixed` (by content, not identity) applies at once; a
 *   key removed from it is reset to its filter's default, or dropped when it is no filter.
 * - Filter types: `text` a search field with a search icon (`placeholder`, else `label`, as
 *   placeholder; `label` as accessible name); `select` a `FilterSelect` — its placeholder is the
 *   "all" state: `placeholder`, else the label of a static option with the empty value (which
 *   is not listed), else `label`. With `optionsUrl` it loads further options
 *   (`{results: [{id, name, count?}]}`) after the static `options` and stays disabled (spinner)
 *   until they are there; a failed load leaves it usable with the static options only. `tags`
 *   toggle buttons; with `multiple` the option with the empty value is "All" and clears them.
 *   `checkbox` a check box. Any other type is rendered by the component registered for it
 *   (`registerFilterType()`, see `FilterControl`); an unregistered type renders nothing (logged
 *   at debug level), its value is still URL-synced and emitted. `wide` makes an item take a
 *   full row.
 * - Slot `filter-<key>` (`{ filter, value, update }`) replaces one filter's control (inside its
 *   `.c-filter-bar__item` cell), whatever its type; `update(value)` goes through the same apply
 *   logic.
 * - `placement: 'panel'` puts a filter into a panel below the row, behind a "Filters" toggle
 *   after the search field that shows how many panel filters are set; the panel is open from the
 *   start when one of them is set on load. Escape inside the panel closes it and returns the
 *   focus to the toggle.
 * - A ghost "clear all filters" X slides in at the end of the row while a visible filter
 *   (in the row or the panel) differs from its default, and resets every filter to its default.
 * - Narrow widths (a container query on `.c-filter-bar-container`, so it follows the space the
 *   bar really has, not the viewport): only the first text filter, the toggle and the clear X
 *   stay; the others collapse behind the toggle (`aria-expanded`), which expands the bar with a
 *   height animation — without a panel a funnel icon, only shown then (the CSS decides when
 *   the bar collapses, and a `ResizeObserver` closes the bar again once it no longer
 *   collapses); with a panel the "Filters" toggle, which opens the panel too. Either follows
 *   the search field.
 * - `idPrefix`: prefix of the controls' ids (`<idPrefix>-<key>`).
 * - Instance API (template ref): `setFilter(key, value)` sets one filter from outside the bar
 *   (applied like a change in the bar but at once — also a text filter, whose pending debounce
 *   it cancels, applying whatever else was typed with it; returns `false` for an unknown or
 *   fixed key), `reloadOptions()` re-runs every `optionsUrl` filter's load, for when something
 *   outside the bar changed what the options (or their counts) should be.
 *
 * @since 1.20
 */
export default {
    name: 'FilterBar',
    components: { FilterControl },
    props: {
        filters: { type: Array, required: true },
        modelValue: { type: Object, default: () => ({}) },
        syncUrl: { type: Boolean, default: true },
        idPrefix: { type: String, default: 'filter' },
        /**
         * Values set by the embedding page or component: sent, not rendered, not URL-synced.
         * @since 1.20
         */
        fixed: { type: Object, default: () => ({}) },
    },
    emits: ['update:modelValue'],
    data() {
        const own = this.filters.filter((filter) => !Object.hasOwn(this.fixed, filter.key));
        const base = { ...defaultValues(this.filters), ...this.modelValue };
        const initial = { ...(this.syncUrl ? readValues(own, window.location.search, base) : base), ...this.fixed };
        return {
            draft: initial,
            remoteOptions: {},
            loading: {},
            // A panel filter set on load opens the panel.
            open: own.some((filter) => isPanel(filter) && !filter.hidden && !isDefault(filter, initial[filter.key])),
            collapsing: false,
            animating: false,
            barId: `filter-bar-${++uid}`,
        };
    },
    computed: {
        // The filters the bar owns: every definition whose key is not fixed.
        ownFilters() {
            return this.filters.filter((filter) => !Object.hasOwn(this.fixed, filter.key));
        },
        visibleFilters() {
            return this.ownFilters.filter((filter) => !filter.hidden);
        },
        primaryFilters() {
            return this.visibleFilters.filter((filter) => !isPanel(filter));
        },
        panelFilters() {
            return this.visibleFilters.filter(isPanel);
        },
        hasPanel() {
            return this.panelFilters.length > 0;
        },
        panelId() {
            return `${this.barId}-panel`;
        },
        // The item that stays visible while the bar is collapsed: the first search field.
        keepIndex() {
            return this.primaryFilters.findIndex((filter) => filter.type === 'text');
        },
        collapsible() {
            return this.hasPanel || this.primaryFilters.length > (this.keepIndex === -1 ? 0 : 1);
        },
        // The row: the primary filters and the toggle, which follows the kept search field (or
        // leads the row without one).
        rowEntries() {
            const entries = this.primaryFilters.map((filter, index) => ({ key: `filter-${filter.key}`, filter, keep: index === this.keepIndex }));
            if (this.collapsible) {
                entries.splice(Math.max(this.keepIndex, 0) + 1, 0, { key: 'toggle', filter: null });
            }
            return entries;
        },
        resettable() {
            return this.visibleFilters.some((filter) => !isDefault(filter, this.draft[filter.key]));
        },
        activePanelCount() {
            return this.panelFilters.filter((filter) => !isDefault(filter, this.draft[filter.key])).length;
        },
        resetLabel() {
            return i18n.t('base', 'Clear all filters');
        },
        toggleLabel() {
            return this.open ? i18n.t('base', 'Hide filters') : i18n.t('base', 'Show filters');
        },
        panelLabel() {
            return i18n.t('base', 'Filters');
        },
        activePanelCountLabel() {
            return i18n.t('base', '{count} active', { count: this.activePanelCount });
        },
    },
    watch: {
        draft: 'onDraftChange',
        modelValue(value) {
            if (!same(keysOf(this.filters, this.fixed), { ...value, ...this.fixed }, this.applied)) {
                clearTimeout(this.debounceTimer);
                this.applied = { ...defaultValues(this.filters), ...value, ...this.fixed };
                this.draft = { ...this.applied };
                this.writeUrl();
            }
        },
        fixed: {
            deep: true,
            handler(value) {
                // Only a real change: an inline `:fixed="{…}"` is a new object with every render.
                const signature = fixedSignature(value);
                if (signature === this.fixedSignature) {
                    return;
                }
                const removed = Object.keys(this.appliedFixed).filter((key) => !Object.hasOwn(value, key));
                this.fixedSignature = signature;
                this.appliedFixed = { ...value };
                const draft = { ...this.draft, ...value };
                // A key no longer fixed: back to its filter's default (the bar owns it now), or gone.
                removed.forEach((key) => {
                    const filter = this.filters.find((candidate) => candidate.key === key);
                    if (filter) {
                        draft[key] = defaultValue(filter);
                    } else {
                        delete draft[key];
                    }
                });
                this.draft = draft;
                // Applied (and emitted) even when no filter value changed - e.g. a removed key
                // that is no filter -, so the embedding list always reloads.
                this.apply();
            },
        },
    },
    created() {
        this.debounceTimer = null;
        // Set by `setFilter()` until its change is applied: applied at once, never debounced.
        this.applyNow = false;
        // The filter types already reported as unregistered, so a render loop logs each once.
        this.reportedTypes = new Set();
        this.fixedSignature = fixedSignature(this.fixed);
        this.appliedFixed = { ...this.fixed };
        this.applied = { ...this.draft };
        if (!same(keysOf(this.filters, this.fixed), this.applied, this.modelValue)) {
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
            if (!this.ownFilters.some((filter) => filter.key === key)) {
                return false;
            }
            // Set from outside the bar (e.g. a card's tag): nobody is typing, so the text
            // debounce does not apply — see `onDraftChange()`.
            this.applyNow = true;
            this.update(key, value);
            return true;
        },
        reset() {
            // A click, not typing: applied at once, also when only a text filter was set.
            this.applyNow = true;
            this.draft = { ...defaultValues(this.filters), ...this.fixed };
        },
        reloadOptions() {
            this.ownFilters.filter((filter) => filter.optionsUrl).forEach((filter) => this.loadOptions(filter));
        },
        inputId(filter) {
            return `${this.idPrefix}-${filter.key}`;
        },
        itemClass(filter) {
            return ['c-filter-bar__item', `c-filter-bar__item--${filter.type}`, `form-search-filter-${filter.key}`, { 'c-filter-bar__item--wide': filter.wide }];
        },
        // The component registered for a filter's type, once both halves of the registration
        // are there (read from the reactive registry, so a late registration re-renders the bar).
        customType(filter) {
            if (CORE_TYPES.includes(filter.type)) {
                return null;
            }
            const name = getFilterType(filter.type);
            return name && isRegistered(name) ? name : null;
        },
        // `$slots` is not reactive, so this is called from the template, not a computed.
        hasControl(filter) {
            if (CORE_TYPES.includes(filter.type) || this.$slots[`filter-${filter.key}`] || this.customType(filter)) {
                return true;
            }
            if (!this.reportedTypes.has(filter.type)) {
                this.reportedTypes.add(filter.type);
                log.debug(`FilterBar: no component is registered for the filter type "${filter.type}" (filter "${filter.key}") — not rendered`);
            }
            return false;
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

            const changed = keysOf(this.filters, this.fixed).filter((key) => JSON.stringify(values[key]) !== JSON.stringify(this.applied[key]));
            if (!changed.length) {
                this.applyNow = false;
                return;
            }

            const changedFilters = this.ownFilters.filter((filter) => changed.includes(filter.key));
            if (changedFilters.some((filter) => !filter.hidden)) {
                const stale = this.ownFilters.filter((filter) => filter.hidden && !isDefault(filter, values[filter.key]));
                if (stale.length) {
                    // Assigning re-runs this watcher with the hidden filters cleared.
                    this.draft = { ...values, ...Object.fromEntries(stale.map((filter) => [filter.key, defaultValue(filter)])) };
                    return;
                }
            }

            // Only a change of the bar's own text filters is debounced (a changed fixed value is not).
            if (!this.applyNow && changedFilters.length === changed.length && changedFilters.every((filter) => filter.type === 'text')) {
                this.debounceTimer = setTimeout(() => this.apply(), TEXT_DEBOUNCE_MS);
            } else {
                this.applyNow = false;
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
            const path = pathname + writeQuery(this.ownFilters, this.applied, search) + hash;
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
        toggleElement() {
            const toggle = this.$refs.toggle;
            return Array.isArray(toggle) ? toggle[0] : toggle;
        },
        // CSS owns the breakpoint (a container query), so "does the bar collapse right now?" is
        // answered by whether it shows the funnel toggle, not by a width kept in step with the
        // SCSS. A bar with a panel always opens and closes (the "Filters" toggle is always shown).
        collapsesNow() {
            if (this.hasPanel) {
                return true;
            }
            const toggle = this.toggleElement();
            return Boolean(toggle) && window.getComputedStyle(toggle).display !== 'none';
        },
        onToggle() {
            if (this.collapsesNow()) {
                this.setOpen(!this.open);
            }
        },
        onPanelEscape() {
            if (this.open) {
                this.setOpen(false);
                this.toggleElement()?.focus();
            }
        },
        setOpen(open, animate = !reducedMotion()) {
            const bar = this.$refs.bar;
            const from = bar.getBoundingClientRect().height;
            const clear = this.$refs.clear || null;
            const flipping = animate && clear && clear.getClientRects().length > 0;
            const clearFrom = flipping ? clear.getBoundingClientRect() : null;
            const focused = document.activeElement;

            // Apply the final layout synchronously, so the height and the clear X can be measured
            // where they land.
            this.collapsing = false;
            this.animating = false;
            this.open = open;
            bar.classList.remove('is-collapsing', 'is-animating');
            bar.classList.toggle('is-open', open);
            bar.style.maxHeight = 'none';
            const to = bar.getBoundingClientRect().height;
            const clearTo = flipping ? clear.getBoundingClientRect() : null;

            // Focus inside what is being hidden (the panel, a collapsed filter) goes to the toggle.
            if (!open && focused && bar.contains(focused) && focused.closest('.c-filter-bar__panel, .c-filter-bar__item:not([data-filter-bar-keep])')) {
                this.toggleElement()?.focus();
            }

            clearTimeout(this.settleTimer);
            if (!animate) {
                // An open bar must stop clipping, or a select's drawer would be cut off.
                bar.style.maxHeight = open ? 'none' : '';
                return;
            }

            // While closing, the collapsed items and the panel stay in flow for the length of
            // the animation, so the bar shrinks past real content instead of snapping first.
            this.collapsing = !open;
            this.animating = true;
            bar.classList.toggle('is-collapsing', !open);
            bar.classList.add('is-animating');
            if (flipping) {
                this.flipClear(clear, clearFrom, clearTo, open);
            }

            bar.style.maxHeight = `${from}px`;
            void bar.offsetHeight; // reflow, so the browser animates from `from`
            bar.style.maxHeight = `${to}px`;

            this.settleTimer = setTimeout(() => {
                bar.style.maxHeight = open ? 'none' : '';
                this.collapsing = false;
                this.animating = false;
            }, ANIMATION_MS);
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
        // and any inline height, so it cannot be stranded half-open. A bar with a panel keeps
        // it: open means "the panel is shown" at every width.
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
