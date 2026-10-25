<template>
    <section :id="tableId" ref="root" class="card content-card data-table" :aria-labelledby="title ? titleId : null">
        <div v-if="title || headerActions.length || headerMenu.length || hasChooser" class="card-header">
            <h2 v-if="title" :id="titleId" class="card-title">{{ title }}</h2>
            <HeaderActions
                :actions="headerActions"
                :menu="headerMenu"
                root-class="card-header-actions"
                tool-variant="ghost"
            >
                <button
                    v-if="hasChooser"
                    type="button"
                    class="btn btn-ghost btn-icon-only data-table-columns-toggle"
                    :aria-label="texts.columns"
                    :title="texts.columns"
                    @click="openChooser"
                ><i class="ti ti-layout-columns" aria-hidden="true"></i></button>
            </HeaderActions>
        </div>
        <div class="card-body">
            <FilterBar
                v-if="filters.length"
                ref="filterBar"
                :model-value="values"
                :filters="filters"
                :sync-url="syncUrl"
                :id-prefix="`${tableId}-filter`"
                @update:model-value="onFilterChange"
            />
            <div ref="wrap" class="data-table-wrap">
                <table
                    class="table table-hover"
                    :class="{ 'is-loading': loading && rows.length > 0 }"
                    :aria-label="label"
                    :aria-busy="loading ? 'true' : 'false'"
                >
                    <thead>
                        <tr>
                            <th
                                v-for="column in shownColumns"
                                :key="column.key"
                                scope="col"
                                :class="`data-table-col-${column.type}`"
                                :aria-sort="ariaSort(column)"
                            >
                                <button
                                    v-if="column.sort"
                                    type="button"
                                    class="data-table-sort"
                                    :class="{ active: sortKey === column.sort }"
                                    :aria-label="sortLabel(column)"
                                    @click="toggleSort(column)"
                                >{{ column.label }}<i class="ti" :class="sortIcon(column)" aria-hidden="true"></i></button>
                                <template v-else>{{ column.label }}</template>
                            </th>
                            <th v-if="hasDetails" class="data-table-col-details"><span class="visually-hidden">{{ texts.details }}</span></th>
                            <th v-if="actions.length" class="data-table-col-actions"><span class="visually-hidden">{{ texts.actions }}</span></th>
                        </tr>
                    </thead>
                    <tbody v-if="loading && !rows.length && !error">
                        <tr v-for="index in skeletonCount" :key="index" class="data-table-skeleton placeholder-glow" aria-hidden="true">
                            <td v-for="column in shownColumns" :key="column.key" :class="`data-table-col-${column.type}`">
                                <div v-if="column.type === 'main'" class="data-table-main">
                                    <span class="placeholder data-table-main-image"></span>
                                    <span class="data-table-main-text">
                                        <span class="placeholder col-8"></span>
                                        <span class="placeholder placeholder-sm col-6"></span>
                                    </span>
                                </div>
                                <span v-else class="placeholder col-6"></span>
                            </td>
                            <td v-if="hasDetails" class="data-table-col-details"></td>
                            <td v-if="actions.length" class="data-table-col-actions"></td>
                        </tr>
                    </tbody>
                    <tbody v-else-if="!rows.length">
                        <tr class="data-table-message">
                            <td :colspan="colspan">
                                <template v-if="error">
                                    <span role="alert">{{ error }}</span>
                                    <button type="button" class="btn btn-link btn-sm" @click="retry">{{ texts.retry }}</button>
                                </template>
                                <template v-else>
                                    <span role="status">{{ texts.empty }}</span>
                                    <button v-if="filtered" type="button" class="btn btn-link btn-sm" @click="clearFilters">{{ texts.clearFilters }}</button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <template v-for="row in rows" :key="row.id">
                            <tr
                                :class="{ 'is-expanded': isExpanded(row), 'is-busy': busy[row.id], 'data-table-row-link': !!rowUrl(row), 'data-table-row-toggle': !rowUrl(row) && hasDetails }"
                                :data-id="row.id"
                                @click="onRowClick(row, $event)"
                            >
                                <component
                                    :is="column.type === 'main' ? 'th' : 'td'"
                                    v-for="column in shownColumns"
                                    :key="column.key"
                                    :scope="column.type === 'main' ? 'row' : null"
                                    :class="`data-table-col-${column.type}`"
                                >
                                    <TableCell
                                        v-if="row.cells[column.key] !== undefined"
                                        :column="column"
                                        :cell="row.cells[column.key]"
                                        @filter="setFilter"
                                    />
                                </component>
                                <td v-if="hasDetails" class="data-table-col-details">
                                    <button
                                        type="button"
                                        class="btn btn-ghost btn-icon-only btn-sm data-table-details-toggle"
                                        :aria-expanded="isExpanded(row) ? 'true' : 'false'"
                                        :aria-controls="detailsId(row)"
                                        :aria-label="rowLabel('Details for {name}', row)"
                                        @click.stop="toggleDetails(row)"
                                    ><i class="ti" :class="isExpanded(row) ? 'ti-chevron-up' : 'ti-chevron-down'" aria-hidden="true"></i></button>
                                </td>
                                <td v-if="actions.length" class="data-table-col-actions" @click.stop>
                                    <template v-if="rowActions(row).length === 1">
                                        <a
                                            :href="rowActions(row)[0].url"
                                            class="btn btn-ghost btn-sm"
                                            :class="{ 'btn-icon-only': rowActions(row)[0].icon, 'text-danger': rowActions(row)[0].destructive }"
                                            :aria-label="actionLabel(rowActions(row)[0], row)"
                                            :title="rowActions(row)[0].label"
                                            @click="runAction(row, rowActions(row)[0], $event)"
                                        ><i v-if="rowActions(row)[0].icon" class="ti" :class="`ti-${rowActions(row)[0].icon}`" aria-hidden="true"></i><template v-else>{{ rowActions(row)[0].label }}</template></a>
                                    </template>
                                    <DropdownMenu
                                        v-else-if="rowActions(row).length > 1"
                                        :menu-id="`${tableId}.row`"
                                        :entries="menuEntries(row)"
                                        :context="{ row }"
                                        :toggle-aria-label="rowLabel('More actions for {name}', row)"
                                        :toggle-title="texts.actions"
                                        toggle-class="btn btn-ghost btn-icon-only btn-sm"
                                        root-class="data-table-actions-menu list-unstyled m-0"
                                    >
                                        <template #toggle><i class="ti ti-dots-vertical" aria-hidden="true"></i></template>
                                    </DropdownMenu>
                                </td>
                            </tr>
                            <tr v-if="hasDetails && isExpanded(row)" :id="detailsId(row)" class="data-table-row-details">
                                <td :colspan="colspan">
                                    <dl>
                                        <template v-for="column in foldedColumns" :key="column.key">
                                            <template v-if="row.cells[column.key] !== undefined">
                                                <dt>{{ column.label }}</dt>
                                                <dd><TableCell :column="column" :cell="row.cells[column.key]" @filter="setFilter" /></dd>
                                            </template>
                                        </template>
                                    </dl>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div v-if="error && rows.length" class="alert alert-danger data-table-error" role="alert">
                {{ error }}
                <button type="button" class="btn btn-link btn-sm" @click="retry">{{ texts.retry }}</button>
            </div>
        </div>
        <div v-if="total > 0" class="card-footer data-table-footer">
            <span class="data-table-count" role="status">{{ countText }}</span>
            <label class="data-table-page-size">
                <span>{{ texts.rowsPerPage }}</span>
                <select class="form-select form-select-sm" :value="pageSize" @change="setPageSize(Number($event.target.value))">
                    <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
                </select>
            </label>
            <nav v-if="pages > 1" class="data-table-pager" :aria-label="texts.pages">
                <ul class="pagination pagination-sm">
                    <li class="page-item" :class="{ disabled: page <= 1 }">
                        <button type="button" class="page-link" :aria-label="texts.previous" :disabled="page <= 1" @click="goTo(page - 1)"><i class="ti ti-chevron-left" aria-hidden="true"></i></button>
                    </li>
                    <li v-for="(slot, index) in pagerSlots" :key="index" class="page-item" :class="{ active: slot === page, disabled: slot === null }">
                        <span v-if="slot === null" class="page-link" aria-hidden="true">…</span>
                        <button
                            v-else
                            type="button"
                            class="page-link"
                            :aria-current="slot === page ? 'page' : null"
                            :aria-label="pageLabel(slot)"
                            @click="goTo(slot)"
                        >{{ slot }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: page >= pages }">
                        <button type="button" class="page-link" :aria-label="texts.next" :disabled="page >= pages" @click="goTo(page + 1)"><i class="ti ti-chevron-right" aria-hidden="true"></i></button>
                    </li>
                </ul>
            </nav>
        </div>
        <UiModal v-if="hasChooser" :show="chooserOpen" :title="texts.columns" dialog-class="data-table-columns" @update:show="chooserOpen = $event">
            <p v-if="maxColumns" class="form-text data-table-columns-limit">{{ limitText }}</p>
            <div v-for="column in optionalColumns" :key="column.key" class="form-check form-switch data-table-columns-option">
                <input
                    :id="`${tableId}-column-${column.key}`"
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    :checked="draftKeys.includes(column.key)"
                    :disabled="!draftKeys.includes(column.key) && atLimit"
                    @change="toggleDraft(column.key, $event.target.checked)"
                >
                <label class="form-check-label" :for="`${tableId}-column-${column.key}`">{{ column.label }}</label>
                <span v-if="draftKeys.includes(column.key) && foldedKeys.includes(column.key)" class="form-text data-table-columns-folded">{{ texts.hiddenAtWidth }}</span>
            </div>
            <template #footer>
                <button v-if="draftDiffersFromDefault" type="button" class="btn btn-link me-auto data-table-columns-reset" @click="resetDraft">{{ texts.reset }}</button>
                <button type="button" class="btn btn-light" @click="chooserOpen = false">{{ texts.cancel }}</button>
                <button type="button" class="btn btn-primary data-table-columns-save" @click="saveColumns">{{ texts.save }}</button>
            </template>
        </UiModal>
    </section>
</template>

<script>
import { client, i18n, log, modal, status } from '@humhub/vue';
import DropdownMenu from './DropdownMenu.vue';
import FilterBar from './FilterBar.vue';
import HeaderActions from './header/HeaderActions.vue';
import TableCell from './table/TableCell.vue';
import UiModal from './UiModal.vue';
import { defaultValues, isDefault, requestParams } from './filter/filterQuery.js';

// The space a column needs before it folds into the row details (docs/ui/data-table.md,
// "Mobile"), by column type; plus the details and actions columns.
const COLUMN_WIDTHS = { main: 240, chip: 160, date: 120, number: 90, text: 150 };
const SIDE_COLUMN_WIDTH = 48;
// A folded column comes back only with this much room to spare, so columns don't flicker.
const FOLD_HYSTERESIS = 32;
const PAGE_SIZES = [25, 50, 100];

let uid = 0;

/**
 * A table of records (`.card.content-card.data-table`, see docs/ui/data-table.md): a content
 * card with optional header actions, the `FilterBar` of the list's filters, a Bootstrap `.table`
 * of typed columns and the footer with the count, the rows per page and the pager. Rows load
 * page by page from an endpoint answering the offset-page envelope (`{results, total, page,
 * pageSize, pages}`) with rows `{id, cells: {<column>: cell}, actions: [{id, url}]}` — the server
 * side is a `humhub\components\table\DataTable` (`definitions()` gives the `columns`, `actions`
 * and `filters` props, `row()` the rows).
 *
 * - Columns (`{key, type, label, priority, sort?, filter?}`): `main` (image, linked name,
 *   subtitle — the row header), `chip` (badges; with a `filter`, each one a button setting it),
 *   `date`, `number`, `text`. A column with `sort` sorts by its header: ascending → descending →
 *   the list's default order (`sort=<key>` / `sort=-<key>`), back on page 1.
 * - Folding: when the table gets too narrow (its own width, not the viewport's), columns fold
 *   into the row details, the highest `priority` number first; every row then gets a chevron
 *   toggling them. The main column never folds.
 * - Row click: opens the record (the main cell's `url`; with Ctrl / Cmd in a new tab); a row
 *   without one toggles its details while columns are folded.
 * - Row actions (`{id, label, kind, icon?, confirm?, destructive?}`, once per table; each row
 *   names those it offers): one action is a button, several a ⋮ menu with the destructive ones
 *   last in red. `link` opens the URL, `modal` loads it into the global modal, `post` submits a
 *   POST form to it (a web route answering with a redirect), `request` POSTs to it and replaces
 *   the row with the answer's `row` (or removes it with `removed: true`). `confirm` asks first.
 * - Paging: 25 / 50 / 100 rows per page, the choice remembered per table (`tableId`) in this
 *   browser;
 *   the pager shows at most five slots.
 * - States: skeleton rows while the first page loads, the rows dimmed while a later one does,
 *   "No results found." (with "Clear filters" while filters are set), an error with "Retry".
 *
 * - Columns chooser (▦ in the header, for tables with optional columns — all but the main one):
 *   a dialog with a switch per optional column (at most `maxColumns`, the others then locked; a
 *   chosen column folded away at the current width is marked), Reset while the choice differs
 *   from the columns' defaults (`visible`), Save. The choice applies at once and is saved for the
 *   user (`PUT columnsUrl` with `{columns}`, `null` for the defaults); the page hands it back as
 *   `columnChoice`.
 *
 * Props: `tableId` (the table's id, also the key of the remembered page size), `label` (the table's
 * accessible name), `title` (an optional card title), `url`, `columns`, `actions`, `filters`,
 * `headerActions` / `headerMenu` (the card's header actions and ⋮ menu, see `HeaderActions`),
 * `labels` (`{one, other, range}`: the count texts with `{count}`, `{from}`, `{to}`, `{total}`),
 * `syncUrl` (the filter values in the page URL, default on), `columnChoice`, `columnsUrl`,
 * `maxColumns` (the columns chooser).
 *
 * @since 1.20
 */
export default {
    name: 'DataTable',
    components: { DropdownMenu, FilterBar, HeaderActions, TableCell, UiModal },
    props: {
        tableId: { type: String, required: true },
        label: { type: String, required: true },
        title: { type: String, default: '' },
        url: { type: String, required: true },
        columns: { type: Array, required: true },
        actions: { type: Array, default: () => [] },
        filters: { type: Array, default: () => [] },
        headerActions: { type: Array, default: () => [] },
        headerMenu: { type: Array, default: () => [] },
        labels: { type: Object, default: () => ({}) },
        syncUrl: { type: Boolean, default: true },
        skeletonCount: { type: Number, default: 5 },
        /**
         * The keys of the optional columns the user chose (`null`: the columns' defaults), the
         * endpoint saving a new choice (`PUT`, `{columns}`) and the most optional columns shown
         * at once (0: no limit).
         */
        columnChoice: { type: Array, default: null },
        columnsUrl: { type: String, default: '' },
        maxColumns: { type: Number, default: 0 },
    },
    data() {
        return {
            values: defaultValues(this.filters),
            sortKey: '',
            sortDescending: false,
            page: 1,
            pageSize: this.storedPageSize(),
            rows: [],
            total: 0,
            pages: 0,
            loading: true,
            error: null,
            expanded: {},
            busy: {},
            foldedKeys: [],
            pageSizes: PAGE_SIZES,
            chosenKeys: this.initialChoice(),
            chooserOpen: false,
            draftKeys: [],
        };
    },
    computed: {
        titleId() {
            return `${this.tableId}-title`;
        },
        texts() {
            return {
                actions: i18n.t('base', 'Actions'),
                details: i18n.t('base', 'Details'),
                retry: i18n.t('base', 'Retry'),
                empty: i18n.t('base', 'No results found!'),
                clearFilters: i18n.t('base', 'Clear filters'),
                rowsPerPage: i18n.t('base', 'Rows per page'),
                pages: i18n.t('base', 'Pages'),
                previous: i18n.t('base', 'Previous page'),
                next: i18n.t('base', 'Next page'),
                columns: i18n.t('base', 'Columns'),
                hiddenAtWidth: i18n.t('base', 'Hidden at this width'),
                reset: i18n.t('base', 'Reset'),
                cancel: i18n.t('base', 'Cancel'),
                save: i18n.t('base', 'Save'),
            };
        },
        optionalColumns() {
            return this.columns.filter((column) => column.type !== 'main');
        },
        defaultKeys() {
            return this.optionalColumns.filter((column) => column.visible !== false).map((column) => column.key);
        },
        hasChooser() {
            return this.optionalColumns.length > 0;
        },
        // The main column and the chosen optional ones, in the columns' order.
        activeColumns() {
            return this.columns.filter((column) => column.type === 'main' || this.chosenKeys.includes(column.key));
        },
        atLimit() {
            return this.maxColumns > 0 && this.draftKeys.length >= this.maxColumns;
        },
        draftDiffersFromDefault() {
            return [...this.draftKeys].sort().join() !== [...this.defaultKeys].sort().join();
        },
        limitText() {
            return i18n.t('base', 'Choose up to {count} columns.', { count: this.maxColumns });
        },
        actionsById() {
            return Object.fromEntries(this.actions.map((action) => [action.id, action]));
        },
        shownColumns() {
            return this.activeColumns.filter((column) => !this.foldedKeys.includes(column.key));
        },
        foldedColumns() {
            return this.activeColumns.filter((column) => this.foldedKeys.includes(column.key));
        },
        hasDetails() {
            return this.foldedKeys.length > 0;
        },
        colspan() {
            return this.shownColumns.length + (this.hasDetails ? 1 : 0) + (this.actions.length ? 1 : 0);
        },
        filtered() {
            return this.filters.some((filter) => !isDefault(filter, this.values[filter.key]));
        },
        sortParam() {
            if (!this.sortKey) {
                return null;
            }
            return this.sortDescending ? `-${this.sortKey}` : this.sortKey;
        },
        countText() {
            if (this.pages <= 1) {
                const template = this.total === 1 ? this.labels.one : this.labels.other;
                return this.format(template || '{count}', { count: this.total });
            }
            const from = (this.page - 1) * this.pageSize + 1;
            const to = Math.min(this.total, this.page * this.pageSize);
            return this.format(this.labels.range || '{from}–{to} / {total}', { from, to, total: this.total });
        },
        // At most five slots: 1 2 3 4 5, 1 2 3 … 12, 1 … 6 … 12, 1 … 10 11 12 (null = gap).
        pagerSlots() {
            const { page, pages } = this;
            if (pages <= 5) {
                return Array.from({ length: pages }, (_, index) => index + 1);
            }
            if (page <= 3) {
                return [1, 2, 3, null, pages];
            }
            if (page >= pages - 2) {
                return [1, null, pages - 2, pages - 1, pages];
            }
            return [1, null, page, null, pages];
        },
    },
    created() {
        this.requestSeq = 0;
        this.started = false;
        this.uid = ++uid;
    },
    mounted() {
        this.started = true;
        this.fetch();
        if (typeof ResizeObserver !== 'undefined' && this.$refs.wrap) {
            this.resizeObserver = new ResizeObserver(() => this.fold());
            this.resizeObserver.observe(this.$refs.wrap);
        }
    },
    beforeUnmount() {
        this.requestSeq++;
        this.resizeObserver?.disconnect();
    },
    methods: {
        reload() {
            return this.fetch();
        },
        retry() {
            return this.fetch();
        },
        setFilter(key, value) {
            return this.$refs.filterBar ? this.$refs.filterBar.setFilter(key, value) : false;
        },
        clearFilters() {
            this.$refs.filterBar?.reset();
        },
        onFilterChange(values) {
            this.values = values;
            // The FilterBar's initial emit (while it is created) only seeds the first fetch.
            if (this.started) {
                this.page = 1;
                this.fetch();
            }
        },
        toggleSort(column) {
            if (this.sortKey !== column.sort) {
                this.sortKey = column.sort;
                this.sortDescending = false;
            } else if (!this.sortDescending) {
                this.sortDescending = true;
            } else {
                this.sortKey = '';
                this.sortDescending = false;
            }
            this.page = 1;
            this.fetch();
        },
        ariaSort(column) {
            if (!column.sort) {
                return null;
            }
            if (this.sortKey !== column.sort) {
                return 'none';
            }
            return this.sortDescending ? 'descending' : 'ascending';
        },
        sortIcon(column) {
            if (this.sortKey !== column.sort) {
                return 'ti-arrows-sort';
            }
            return this.sortDescending ? 'ti-arrow-down' : 'ti-arrow-up';
        },
        // The label names what a click does next.
        sortLabel(column) {
            if (this.sortKey === column.sort && !this.sortDescending) {
                return i18n.t('base', '{column}, sort descending', { column: column.label });
            }
            if (this.sortKey === column.sort) {
                return i18n.t('base', '{column}, default order', { column: column.label });
            }
            return i18n.t('base', '{column}, sort ascending', { column: column.label });
        },
        goTo(page) {
            if (page < 1 || page > this.pages || page === this.page) {
                return;
            }
            this.page = page;
            this.fetch().then(() => this.$refs.root?.scrollIntoView?.({ block: 'start', behavior: 'smooth' }));
        },
        setPageSize(size) {
            this.pageSize = PAGE_SIZES.includes(size) ? size : PAGE_SIZES[0];
            try {
                window.localStorage.setItem(this.storageKey(), String(this.pageSize));
            } catch (e) {
                // Storage unavailable (private mode): the choice lasts for this page only.
            }
            this.page = 1;
            this.fetch();
        },
        storageKey() {
            return `humhub.dataTable.${this.tableId}.pageSize`;
        },
        storedPageSize() {
            try {
                const stored = Number(window.localStorage.getItem(this.storageKey()));
                return PAGE_SIZES.includes(stored) ? stored : PAGE_SIZES[0];
            } catch (e) {
                return PAGE_SIZES[0];
            }
        },
        pageLabel(page) {
            return i18n.t('base', 'Page {page}', { page });
        },
        rowName(row) {
            const main = this.columns.find((column) => column.type === 'main');
            return (main && row.cells[main.key] && row.cells[main.key].text) || String(row.id);
        },
        // A label naming the row's record: "Details for Emma Williams".
        rowLabel(message, row) {
            return i18n.t('base', message, { name: this.rowName(row) });
        },
        actionLabel(action, row) {
            return `${action.label}: ${this.rowName(row)}`;
        },
        format(template, params) {
            return String(template).replace(/\{(\w+)\}/g, (match, key) => (params[key] !== undefined ? params[key] : match));
        },
        fetch() {
            const seq = ++this.requestSeq;
            this.loading = true;
            this.error = null;

            const params = {
                ...requestParams(this.filters, this.values),
                page: String(this.page),
                pageSize: String(this.pageSize),
            };
            if (this.sortParam) {
                params.sort = this.sortParam;
            }
            const url = this.url + (this.url.includes('?') ? '&' : '?') + new URLSearchParams(params).toString();

            return client.get(url).then((response) => {
                if (seq !== this.requestSeq) {
                    return;
                }
                this.rows = response.results || [];
                this.total = response.total || 0;
                this.pages = response.pages || 0;
                this.page = response.page || this.page;
                this.expanded = {};
                this.loading = false;
                this.$nextTick(() => this.fold());
            }).catch((response) => {
                if (seq !== this.requestSeq) {
                    return;
                }
                this.loading = false;
                this.error = (response && typeof response.message === 'string' && response.message !== '')
                    ? response.message
                    : i18n.t('base', 'The list could not be loaded.');
                log.error(response);
            });
        },
        // Folds the columns that no longer fit, the highest priority number first.
        fold() {
            const wrap = this.$refs.wrap;
            if (!wrap || !wrap.clientWidth) {
                return;
            }
            const foldable = this.activeColumns
                .filter((column) => column.type !== 'main')
                .sort((a, b) => (b.priority || 1) - (a.priority || 1));
            // The columns, the actions column, and the chevron column once one has folded.
            const width = (columns) => columns.reduce(
                (sum, column) => sum + (COLUMN_WIDTHS[column.type] || 150),
                SIDE_COLUMN_WIDTH * ((this.actions.length ? 1 : 0) + (columns.length < this.activeColumns.length ? 1 : 0)),
            );

            const folded = [];
            let shown = [...this.activeColumns];
            for (const column of foldable) {
                if (width(shown) <= wrap.clientWidth) {
                    break;
                }
                folded.push(column.key);
                shown = shown.filter((candidate) => candidate !== column);
            }

            // A column already folded comes back only with room to spare.
            const unfolding = this.foldedKeys.filter((key) => !folded.includes(key));
            if (unfolding.length && width(shown) > wrap.clientWidth - FOLD_HYSTERESIS) {
                return;
            }
            if (folded.join() !== this.foldedKeys.join()) {
                this.foldedKeys = folded;
            }
        },
        initialChoice() {
            const keys = this.columns.filter((column) => column.type !== 'main').map((column) => column.key);
            if (Array.isArray(this.columnChoice)) {
                return this.columnChoice.filter((key) => keys.includes(key));
            }
            return this.columns.filter((column) => column.type !== 'main' && column.visible !== false).map((column) => column.key);
        },
        openChooser() {
            this.draftKeys = [...this.chosenKeys];
            this.chooserOpen = true;
        },
        toggleDraft(key, on) {
            const keys = this.draftKeys.filter((candidate) => candidate !== key);
            this.draftKeys = on ? [...keys, key] : keys;
        },
        resetDraft() {
            this.draftKeys = [...this.defaultKeys];
        },
        // Applies the choice at once and saves it for the user (back to the defaults: `null`).
        saveColumns() {
            const keys = this.optionalColumns.map((column) => column.key).filter((key) => this.draftKeys.includes(key));
            this.chosenKeys = keys;
            this.chooserOpen = false;
            this.$nextTick(() => this.fold());
            if (!this.columnsUrl) {
                return Promise.resolve();
            }
            const columns = this.draftDiffersFromDefault ? keys : null;
            return client.put(this.columnsUrl, { data: JSON.stringify({ columns }), contentType: 'application/json' })
                .catch((response) => {
                    status('error', (response && response.message) || i18n.t('base', 'An unexpected error occurred.'));
                    log.error(response);
                });
        },
        isExpanded(row) {
            return !!this.expanded[row.id];
        },
        toggleDetails(row) {
            this.expanded = { ...this.expanded, [row.id]: !this.expanded[row.id] };
        },
        detailsId(row) {
            return `${this.tableId}-details-${this.uid}-${row.id}`;
        },
        // The record the row links to: its main cell's URL.
        rowUrl(row) {
            const main = this.columns.find((column) => column.type === 'main');
            return (main && row.cells[main.key] && row.cells[main.key].url) || null;
        },
        // A click on the row opens its record (with Ctrl / Cmd in a new tab); a row without one
        // toggles its details while columns are folded — but never a click on a link or a
        // button inside it.
        onRowClick(row, event) {
            if (event.target.closest('a, button, input, select, label')) {
                return;
            }
            const url = this.rowUrl(row);
            if (url) {
                this.open(url, event.ctrlKey || event.metaKey);
                return;
            }
            if (this.hasDetails) {
                this.toggleDetails(row);
            }
        },
        open(url, newTab = false) {
            if (newTab) {
                window.open(url, '_blank', 'noopener');
            } else {
                window.location.assign(url);
            }
        },
        rowActions(row) {
            return (row.actions || [])
                .map((rowAction) => this.actionsById[rowAction.id] ? { ...this.actionsById[rowAction.id], url: rowAction.url } : null)
                .filter(Boolean);
        },
        menuEntries(row) {
            const entries = [];
            const actions = this.rowActions(row);
            actions.forEach((action, index) => {
                if (action.destructive && index > 0 && !actions[index - 1].destructive) {
                    entries.push({ id: `divider-${action.id}`, divider: true, sortOrder: index * 10 - 5 });
                }
                const entry = {
                    id: action.id,
                    label: action.label,
                    url: action.url,
                    sortOrder: index * 10,
                    htmlOptions: action.destructive ? { class: 'text-danger' } : {},
                };
                // A plain link stays a link (DropdownMenu lets its click through).
                if (action.kind !== 'link' || action.confirm) {
                    entry.onClick = () => this.runAction(row, action);
                }
                entries.push(entry);
            });
            return entries;
        },
        runAction(row, action, event) {
            // A plain link without confirmation stays a link (new tab, copy address).
            if (action.kind === 'link' && !action.confirm) {
                return Promise.resolve();
            }
            event?.preventDefault();
            this.closeMenus();

            const confirmed = action.confirm
                ? modal.confirm({ body: action.confirm, confirmText: action.label })
                : Promise.resolve(true);

            return confirmed.then((ok) => {
                if (!ok) {
                    return null;
                }
                switch (action.kind) {
                    case 'modal':
                        return modal.load(action.url);
                    case 'post':
                        return this.submitPost(action.url);
                    case 'request':
                        return this.request(row, action);
                    default:
                        window.location.assign(action.url);
                        return null;
                }
            });
        },
        // The ⋮ menu an action was chosen from stays open on its own (its entry handles the click).
        closeMenus() {
            const Dropdown = typeof bootstrap === 'undefined' ? null : bootstrap.Dropdown;
            this.$el.querySelectorAll?.('[data-bs-toggle="dropdown"][aria-expanded="true"]').forEach((toggle) => {
                Dropdown?.getInstance(toggle)?.hide();
            });
        },
        submitPost(url) {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = url;
            const param = document.querySelector('meta[name="csrf-param"]')?.getAttribute('content');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (param && token) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = param;
                input.value = token;
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        },
        request(row, action) {
            this.busy = { ...this.busy, [row.id]: true };

            return client.post(action.url).then((response) => {
                if (response && response.removed) {
                    this.rows = this.rows.filter((candidate) => candidate.id !== row.id);
                    this.total = Math.max(0, this.total - 1);
                } else if (response && response.row) {
                    const index = this.rows.findIndex((candidate) => candidate.id === row.id);
                    if (index !== -1) {
                        this.rows.splice(index, 1, response.row);
                    }
                }
                if (response && response.message) {
                    status('success', response.message);
                }
            }).catch((response) => {
                status('error', (response && response.message) || i18n.t('base', 'An unexpected error occurred.'));
                log.error(response);
            }).finally(() => {
                const busy = { ...this.busy };
                delete busy[row.id];
                this.busy = busy;
            });
        },
    },
};
</script>
