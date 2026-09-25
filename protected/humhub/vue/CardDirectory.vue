<template>
    <div class="c-card-directory">
        <PageToolbar :title="title" :title-tag="titleTag" :actions="actions">
            <template v-if="$slots.actions" #actions>
                <slot name="actions" :meta="meta" :total="total"></slot>
            </template>
            <FilterBar
                v-if="filters.length"
                ref="filterBar"
                :model-value="values"
                :filters="filters"
                :sync-url="syncUrl"
                :id-prefix="idPrefix"
                @update:model-value="onFilterChange"
            >
                <template v-for="name in filterSlotNames()" :key="name" #[name]="scope">
                    <slot :name="name" v-bind="scope"></slot>
                </template>
            </FilterBar>
        </PageToolbar>
        <div v-if="$slots.notice" class="c-card-directory__notice">
            <slot name="notice" :meta="meta" :total="total"></slot>
        </div>
        <CardGrid
            :items="items"
            :item-key="itemKey"
            :loading="loading"
            :error="error"
            :has-more="hasMore"
            :skeleton-count="skeletonCount"
            :card-class="cardClass"
            :page-starts="pageStarts"
            @load-more="loadMore"
            @retry="retry"
        >
            <template #card="{ item, index }"><slot name="card" :item="item" :index="index" :state="states[item[itemKey]]"></slot></template>
            <template v-if="$slots.skeleton" #skeleton="{ index }"><slot name="skeleton" :index="index"></slot></template>
            <template v-if="$slots.empty" #empty><slot name="empty"></slot></template>
        </CardGrid>
    </div>
</template>

<script>
import { client, i18n, log } from '@humhub/vue';
import CardGrid from './CardGrid.vue';
import FilterBar from './FilterBar.vue';
import PageToolbar from './PageToolbar.vue';
import { defaultValues, requestParams } from './filter/filterQuery.js';

/**
 * A card directory page (`.c-card-directory`, at most 1440px wide): a `PageToolbar` — the
 * `title` and the `actions` slot — holding the `FilterBar` for the filters of a
 * `FilterSet`, an optional `notice` between the toolbar and the grid, and the card grid
 * (`CardGrid`), fed page by page from an endpoint answering the API's offset-page envelope
 * (`{results, total, page, pageSize, pages}`, see `docs/develop/concept-api.md`). The data is
 * always loaded after mounting — see "Initial data: embed or load" in
 * `docs/develop/ui-js-vuejs-components.md`.
 *
 * - Props: `url` (the endpoint; it may carry a query string of its own, e.g.
 *   `/api/v2/space?purpose=directory`, which the filter and page parameters are appended to),
 *   `filters` (the definitions), `title` (toolbar heading, rendered as `titleTag`, default
 *   `h1`), `actions` (the toolbar's actions as data, see `PageToolbar`), `pageSize`, `skeletonCount`, `cardClass` (extra class on every
 *   grid cell, see `CardGrid`), `itemKey`, `metaKeys`, `syncUrl` and `idPrefix` (handed to the
 *   `FilterBar`, which owns the URL sync, the text debounce and the dropping of hidden filters),
 *   `itemStates` (see below).
 * - Item states: `itemStates(ids)` — optional, a function answering a promise of an object that
 *   maps an item's key to its state (e.g. the viewer's membership of every space shown) — is
 *   called once per loaded page with the keys (`itemKey`) of that page's items, after the page
 *   is rendered; nothing is requested for an empty page. The `card` slot gets the item's `state`:
 *   `undefined` while the page's states are loading, `null` for an item missing from the answer
 *   or when the request failed (the failure is logged). An answer for a page that is no longer
 *   shown (a later page 1 replaced the list, e.g. `reload()` or a filter change, which refetch
 *   the states with the list) is ignored. `replaceState(id, patch)` merges `patch` into one
 *   item's state (shallow) after an action changed it; a patch made while the item's states are
 *   still loading survives the answer (its fields win, the answer's others are applied).
 * - Every applied filter change (a `FilterBar` emit) starts at page 1.
 * - A response that is no longer the latest request's is ignored.
 * - `metaKeys` names envelope fields beyond the standard ones to keep (`meta`, handed to the
 *   `actions` and `notice` slots and the `loaded` event).
 * - Errors: `CardGrid` emits `retry` for both a failed first page (nothing to show yet) and a
 *   failure while paging (cards from earlier pages already shown) — it never emits `load-more`
 *   for either. A page-1 failure (initial load, a filter change, `reload()`) clears `items`,
 *   `page`/`pages` and `total`/`meta`, since stale cards (and the counts describing them) from
 *   a previous filter set must not linger under a filter they no longer match; the full-grid
 *   error/retry replaces them. A failure while paging leaves the shown items (and their counts)
 *   untouched — the error sits below the grid instead. Either way the page that failed is
 *   remembered (`failedPage`) and `retry()` re-fetches exactly that page, not necessarily
 *   page 1.
 * - Instance API for the owning island (template ref): `reload()` (always page 1),
 *   `loadMore()`, `retry()`, `replaceItem(id, item)` after an action changed one record,
 *   `replaceState(id, patch)` (see "Item states"),
 *   `setFilter(key, value)` to set one filter from outside the bar (e.g. a card's type pill;
 *   delegated to the `FilterBar`, applied like a change in it but at once — a text filter too,
 *   without the debounce —, returns `false` for an unknown key), `reloadFilterOptions()` after something outside the bar changed what a filter's remote
 *   options (or their counts) should be.
 * - Slots: `actions` (`{ meta, total }`, right of the title), `notice` (`{ meta, total }`,
 *   between toolbar and grid), `filter-<key>` (`{ filter, value, update }`), `card`
 *   (`{ item, index, state }` — `index` within the loaded page, for the stagger; `state` see
 *   "Item states"), `skeleton` (`{ index }`, the placeholder of one skeleton cell, handed to
 *   `CardGrid`, default `CardSkeleton`), `empty`.
 * - Emits `loaded` (`{ total, meta, values }`) after every successful page — `values` are the
 *   filter values the page was loaded with (e.g. to highlight the search in the cards).
 *
 * @since 1.20
 */
export default {
    name: 'CardDirectory',
    components: { CardGrid, FilterBar, PageToolbar },
    props: {
        url: { type: String, required: true },
        title: { type: String, default: '' },
        titleTag: { type: String, default: 'h1' },
        filters: { type: Array, default: () => [] },
        pageSize: { type: Number, default: 24 },
        skeletonCount: { type: Number, default: 12 },
        cardClass: { type: String, default: undefined },
        itemKey: { type: String, default: 'id' },
        metaKeys: { type: Array, default: () => [] },
        syncUrl: { type: Boolean, default: true },
        idPrefix: { type: String, default: 'filter' },
        /**
         * The toolbar's actions as data, handed to `PageToolbar`.
         * @since 1.20
         */
        actions: { type: Array, default: () => [] },
        /**
         * `(ids) => Promise<{ [id]: state }>`, called once per loaded page (see "Item states").
         * @since 1.20
         */
        itemStates: { type: Function, default: null },
    },
    emits: ['loaded'],
    data() {
        return {
            // The applied filter values - the FilterBar's `v-model`, which it sets (from the page
            // URL) while it is created, before this component's first fetch in `mounted()`.
            values: defaultValues(this.filters),
            items: [],
            page: 0,
            pages: 0,
            total: 0,
            meta: {},
            // The first page is requested in `mounted()`; loading from the first render on shows the
            // skeletons at once.
            loading: true,
            error: null,
            failedPage: null,
            pageStarts: [0],
            // The item states by item key (see "Item states"), reset with every page 1.
            states: {},
        };
    },
    computed: {
        hasMore() {
            return this.page < this.pages;
        },
    },
    created() {
        this.requestSeq = 0;
        // Bumped whenever the shown list is replaced (or dropped), so the states answered for a
        // page of the previous list are ignored.
        this.listSeq = 0;
        this.started = false;
    },
    mounted() {
        this.started = true;
        this.fetch(1);
    },
    beforeUnmount() {
        this.requestSeq++;
        this.listSeq++;
    },
    methods: {
        reload() {
            return this.fetch(1);
        },
        loadMore() {
            if (!this.hasMore || this.loading) {
                return Promise.resolve();
            }
            return this.fetch(this.page + 1);
        },
        retry() {
            return this.fetch(this.failedPage || 1);
        },
        replaceItem(id, item) {
            const index = this.items.findIndex((candidate) => candidate[this.itemKey] === id);
            if (index !== -1) {
                this.items.splice(index, 1, item);
            }
        },
        /**
         * Merges `patch` into the state of the item keyed `id` (shallow).
         * @since 1.20
         */
        replaceState(id, patch) {
            this.states[id] = { ...(this.states[id] || {}), ...patch };
        },
        setFilter(key, value) {
            return this.$refs.filterBar ? this.$refs.filterBar.setFilter(key, value) : false;
        },
        reloadFilterOptions() {
            this.$refs.filterBar?.reloadOptions();
        },
        // `$slots` is a plain object the render function replaces on every render, not a
        // reactive one a computed can track — called from the template instead, so it is
        // re-evaluated with every render instead of caching a value that never invalidates.
        filterSlotNames() {
            return Object.keys(this.$slots).filter((name) => name.startsWith('filter-'));
        },
        onFilterChange(values) {
            this.values = values;
            // The FilterBar's initial emit (while it is created) only seeds the values the first
            // fetch in `mounted()` uses.
            if (this.started) {
                this.fetch(1);
            }
        },
        fetch(page) {
            const seq = ++this.requestSeq;
            this.loading = true;
            this.error = null;

            const query = new URLSearchParams({
                ...requestParams(this.filters, this.values),
                page: String(page),
                pageSize: String(this.pageSize),
            }).toString();
            const url = this.url + (this.url.includes('?') ? '&' : '?') + query;

            return client.get(url).then((response) => {
                if (seq !== this.requestSeq) {
                    return;
                }
                const results = response.results || [];
                if (page === 1) {
                    this.listSeq++;
                    this.states = {};
                }
                this.pageStarts = page === 1 ? [0] : [...this.pageStarts, this.items.length];
                this.items = page === 1 ? results : [...this.items, ...results];
                this.page = response.page || page;
                this.pages = response.pages || 0;
                this.total = response.total || 0;
                this.meta = Object.fromEntries(this.metaKeys.map((key) => [key, response[key]]));
                this.loading = false;
                this.failedPage = null;
                this.$emit('loaded', { total: this.total, meta: this.meta, values: { ...this.values } });
                this.fetchStates(results.map((item) => item[this.itemKey]));
            }).catch((response) => {
                if (seq !== this.requestSeq) {
                    return;
                }
                this.loading = false;
                this.failedPage = page;
                if (page === 1) {
                    // Drop stale cards (and the counts/meta that described them) from before the
                    // change that just failed — they belong to a filter set that no longer
                    // applies, and CardGrid would otherwise keep showing them dimmed forever (its
                    // "is-loading" state only ever clears).
                    this.listSeq++;
                    this.states = {};
                    this.items = [];
                    this.pageStarts = [0];
                    this.page = 0;
                    this.pages = 0;
                    this.total = 0;
                    this.meta = {};
                }
                this.error = (response && typeof response.message === 'string' && response.message !== '')
                    ? response.message
                    : i18n.t('base', 'The list could not be loaded.');
                log.error(response);
            });
        },
        fetchStates(ids) {
            if (!this.itemStates || !ids.length) {
                return;
            }
            const seq = this.listSeq;
            const apply = (answer) => {
                if (seq !== this.listSeq) {
                    return;
                }
                const states = { ...this.states };
                ids.forEach((id) => {
                    const answered = answer && answer[id] !== undefined ? answer[id] : null;
                    // States are reset with every page 1, so a state already set for an id of
                    // this page is a `replaceState()` patch made while the request was pending:
                    // it is applied on top of the answer rather than lost to it.
                    states[id] = states[id] !== undefined ? { ...(answered || {}), ...states[id] } : answered;
                });
                this.states = states;
            };
            Promise.resolve()
                .then(() => this.itemStates(ids))
                .then(apply, (error) => {
                    if (seq === this.listSeq) {
                        log.error(error);
                    }
                    apply(null);
                });
        },
    },
};
</script>
