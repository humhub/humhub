<template>
    <section :id="tableId" ref="root" class="card content-card data-table" :aria-labelledby="title ? titleId : null">
        <div v-if="title || headerActions.length || headerMenu.length || hasChooser || hasBulk" class="card-header">
            <h2 v-if="title" :id="titleId" class="card-title">{{ title }}</h2>
            <HeaderActions
                :actions="headerActions"
                :menu="selectedCount ? [] : headerMenu"
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
                <template #end>
                    <!-- While rows are selected, the ⋮ is the selection menu with the count -->
                    <DropdownMenu
                        v-if="selectedCount"
                        :menu-id="`${tableId}.bulk`"
                        :entries="bulkEntries"
                        :toggle-aria-label="selectionLabel"
                        :toggle-title="selectionLabel"
                        toggle-class="btn btn-secondary data-table-bulk-toggle"
                        root-class="data-table-bulk list-unstyled m-0"
                    >
                        <template #toggle>{{ selectedCount }}<i class="ti ti-dots-vertical" aria-hidden="true"></i></template>
                    </DropdownMenu>
                </template>
            </HeaderActions>
            <span class="visually-hidden" role="status">{{ selectedCount ? selectionLabel : '' }}</span>
        </div>
        <div class="card-body">
            <FilterBar
                v-if="shownFilters.length"
                ref="filterBar"
                :model-value="values"
                :filters="shownFilters"
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
                    <colgroup>
                        <col v-if="showSelect" class="data-table-col-select">
                        <col
                            v-for="column in shownColumns"
                            :key="column.key"
                            :class="`data-table-col-${column.type}`"
                            :style="column.type === 'main' ? mainColumnStyle : null"
                        >
                        <col v-if="hasDetails" class="data-table-col-details">
                        <col v-if="actions.length" class="data-table-col-actions">
                    </colgroup>
                    <thead>
                        <tr>
                            <th v-if="showSelect" class="data-table-col-select">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :checked="pageSelected === 'all'"
                                    :indeterminate.prop="pageSelected === 'some'"
                                    :disabled="!rows.length"
                                    :aria-label="texts.selectPage"
                                    @change="selectPage($event.target.checked)"
                                >
                            </th>
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
                            <td v-if="showSelect" class="data-table-col-select"></td>
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
                        <template v-for="(row, index) in rows" :key="row.id">
                            <tr
                                :class="{ 'is-expanded': isExpanded(row), 'is-busy': busy[row.id], 'table-active': isSelected(row), 'data-table-row-link': !!rowUrl(row), 'data-table-row-toggle': !rowUrl(row) && hasDetails }"
                                :data-id="row.id"
                                @click="onRowClick(row, $event)"
                            >
                                <td v-if="showSelect" class="data-table-col-select">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :checked="isSelected(row)"
                                        :aria-label="rowLabel('Select {name}', row)"
                                        @click="onSelect(row, index, $event)"
                                    >
                                </td>
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
                                            class="btn btn-sm"
                                            :class="[rowActions(row)[0].destructive ? 'btn-ghost-danger' : 'btn-ghost', { 'btn-icon-only': rowActions(row)[0].icon }]"
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
            <nav v-if="pages > 1" class="data-table-pager" :aria-label="texts.pages">
                <ul class="pagination">
                    <li class="page-item" :class="{ invisible: page <= 1 }">
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
                    <li class="page-item" :class="{ invisible: page >= pages }">
                        <button type="button" class="page-link" :aria-label="texts.next" :disabled="page >= pages" @click="goTo(page + 1)"><i class="ti ti-chevron-right" aria-hidden="true"></i></button>
                    </li>
                </ul>
            </nav>
            <FilterSelect
                v-if="total > pageSizes[0]"
                class="data-table-page-size"
                :model-value="String(pageSize)"
                :options="pageSizeOptions"
                :label="texts.rowsPerPage"
                :title="texts.rowsPerPage"
                :clearable="false"
                drop-up
                @update:model-value="setPageSize(Number($event))"
            />
        </div>
        <UiModal v-if="hasChooser" :show="chooserOpen" :title="texts.columns" dialog-class="data-table-columns" @update:show="chooserOpen = $event">
            <p v-if="maxColumns" class="data-table-columns-limit">{{ limitText }}</p>
            <div class="list-group data-table-columns-list">
                <label
                    v-for="column in choosableColumns"
                    :key="column.key"
                    class="list-group-item data-table-columns-option"
                    :class="{ disabled: isLocked(column.key) }"
                >
                    <span class="data-table-columns-label">
                        {{ column.label }}
                        <span v-if="draftKeys.includes(column.key) && foldedKeys.includes(column.key)" class="form-text data-table-columns-folded">{{ texts.hiddenAtWidth }}</span>
                    </span>
                    <span class="form-check form-switch">
                        <input
                            :id="`${tableId}-column-${column.key}`"
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            :checked="draftKeys.includes(column.key)"
                            :disabled="isLocked(column.key)"
                            @change="toggleDraft(column.key, $event.target.checked)"
                        >
                    </span>
                </label>
            </div>
            <template #footer>
                <button type="button" class="btn btn-light data-table-columns-reset" :disabled="!draftDiffersFromDefault" @click="resetDraft">{{ texts.reset }}</button>
                <button type="button" class="btn btn-primary data-table-columns-save" @click="saveColumns">{{ texts.save }}</button>
            </template>
        </UiModal>
    </section>
</template>

<script>
import { client, i18n, log, modal, status } from '@humhub/vue';
import DropdownMenu from './DropdownMenu.vue';
import FilterBar from './FilterBar.vue';
import FilterSelect from './FilterSelect.vue';
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
// The key of the checkbox column in a column choice
const SELECT_KEY = '_select';

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
 *   last in red (`.dropdown-item-danger`). `link` opens the URL, `modal` loads it into the global modal, `post` submits a
 *   POST form to it (a web route answering with a redirect), `request` POSTs to it and replaces
 *   the row with the answer's `row` (or removes it with `removed: true`; any other answer reloads
 *   the page). `confirm` asks first.
 * - Paging: 25 / 50 / 100 rows per page, the choice remembered per table (`tableId`) in this
 *   browser, offered only when there are more rows than the smallest size;
 *   the pager shows at most five slots.
 * - States: skeleton rows while the first page loads, the rows dimmed while a later one does,
 *   "No results found." (with "Clear filters" while filters are set), an error with "Retry".
 *
 * - Selection (for tables with a bulk action, one with a `bulkUrl`, while "Bulk select" is on in
 *   the columns chooser; off by default): a checkbox per row (with
 *   Shift a range) and one for the page; while rows are selected, the header's ⋮ is the
 *   selection menu ("2 ⋮"): each bulk action with the selected rows it applies to, "Select all
 *   35" (every row of the filters, across pages), "Clear selection". A bulk action POSTs `ids`
 *   (or `all` with the filters in the query) to its `bulkUrl` and reloads the page. A filter or
 *   sort change clears the selection.
 * - Columns chooser (▦ in the header, for tables with optional columns — all but the main one —
 *   or a bulk action): a dialog with a switch per optional column and "Bulk select" (the
 *   checkbox column, key `_select`; at most `maxColumns` of them, the others then locked; a
 *   chosen column folded away at the current width is marked), Reset (to the columns' defaults,
 *   `visible`) and Save. The choice applies at once and is saved for the
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
    components: { DropdownMenu, FilterBar, FilterSelect, HeaderActions, TableCell, UiModal },
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
            // The selected rows by id (their rows, for the actions they offer), or every row of
            // the list's filters (`allSelected`).
            selected: {},
            allSelected: false,
            lastSelectedIndex: null,
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
                save: i18n.t('base', 'Save'),
                bulkSelect: i18n.t('base', 'Bulk select'),
                selectPage: i18n.t('base', 'Select all on this page'),
                clearSelection: i18n.t('base', 'Clear selection'),
            };
        },
        bulkActions() {
            return this.actions.filter((action) => action.bulkUrl);
        },
        hasBulk() {
            return this.bulkActions.length > 0;
        },
        // The checkbox column: for tables with a bulk action, while chosen in the columns chooser.
        showSelect() {
            return this.hasBulk && this.chosenKeys.includes(SELECT_KEY);
        },
        selectedCount() {
            return this.allSelected ? this.total : Object.keys(this.selected).length;
        },
        selectionLabel() {
            return i18n.t('base', '{count} selected', { count: this.selectedCount });
        },
        // `all`, `some` or `none` of the rows on this page selected (the header checkbox).
        pageSelected() {
            const count = this.rows.filter((row) => this.isSelected(row)).length;
            if (count === 0) {
                return 'none';
            }
            return count === this.rows.length ? 'all' : 'some';
        },
        // The selection menu: each bulk action with the selected rows it applies to, then
        // "Select all" (across pages) and "Clear selection".
        bulkEntries() {
            const entries = [];
            this.bulkActions.forEach((action, index) => {
                const count = this.allSelected
                    ? null
                    : Object.values(this.selected).filter((row) => (row.actions || []).some((rowAction) => rowAction.id === action.id)).length;
                if (count === 0) {
                    return;
                }
                entries.push({
                    id: `bulk-${action.id}`,
                    label: count === null ? action.label : `${action.label} (${count})`,
                    sortOrder: index * 10,
                    htmlOptions: action.destructive ? { class: 'dropdown-item-danger' } : {},
                    onClick: () => this.runBulk(action, count),
                });
            });
            entries.push({ id: 'bulk-divider', divider: true, sortOrder: 900 });
            if (!this.allSelected && this.total > this.selectedCount) {
                entries.push({
                    id: 'bulk-select-all',
                    label: i18n.t('base', 'Select all {count}', { count: this.total }),
                    sortOrder: 910,
                    onClick: () => { this.allSelected = true; },
                });
            }
            entries.push({ id: 'bulk-clear', label: this.texts.clearSelection, sortOrder: 920, onClick: () => this.clearSelection() });
            return entries;
        },
        optionalColumns() {
            return this.columns.filter((column) => column.type !== 'main');
        },
        defaultKeys() {
            return this.optionalColumns.filter((column) => column.visible !== false).map((column) => column.key);
        },
        // What the columns chooser offers: "Bulk select" (the checkbox column), then the optional
        // columns.
        choosableColumns() {
            return [
                ...(this.hasBulk ? [{ key: SELECT_KEY, label: this.texts.bulkSelect }] : []),
                ...this.optionalColumns,
            ];
        },
        hasChooser() {
            return this.choosableColumns.length > 0;
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
        // The main column twice as wide as each of the others (`table-layout: fixed`); alone, it
        // takes the table's width.
        mainColumnStyle() {
            const others = this.shownColumns.length - 1;
            return others > 0 ? { width: `${(200 / (others + 2)).toFixed(4)}%` } : null;
        },
        pageSizeOptions() {
            return this.pageSizes.map((size) => ({ value: String(size), label: String(size) }));
        },
        // The filters of the chosen columns (a column's `filter`), and those of no column (e.g. the
        // search): a hidden column's filter goes with it.
        shownFilters() {
            const columnFilters = this.columns.map((column) => column.filter).filter(Boolean);
            const chosenFilters = this.activeColumns.map((column) => column.filter).filter(Boolean);
            return this.filters.filter((filter) => !columnFilters.includes(filter.key) || chosenFilters.includes(filter.key));
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
            return this.shownColumns.length + (this.showSelect ? 1 : 0) + (this.hasDetails ? 1 : 0) + (this.actions.length ? 1 : 0);
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
                this.clearSelection();
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
            this.clearSelection();
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
                ...requestParams(this.shownFilters, this.values),
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
                SIDE_COLUMN_WIDTH * ((this.actions.length ? 1 : 0) + (this.showSelect ? 1 : 0) + (columns.length < this.activeColumns.length ? 1 : 0)),
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
            if (this.actions.some((action) => action.bulkUrl)) {
                keys.push(SELECT_KEY);
            }
            if (Array.isArray(this.columnChoice)) {
                return this.columnChoice.filter((key) => keys.includes(key));
            }
            return this.columns.filter((column) => column.type !== 'main' && column.visible !== false).map((column) => column.key);
        },
        openChooser() {
            this.draftKeys = [...this.chosenKeys];
            this.chooserOpen = true;
        },
        // A switch that can't be turned on: the most columns are chosen.
        isLocked(key) {
            return !this.draftKeys.includes(key) && this.atLimit;
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
            const keys = this.choosableColumns.map((column) => column.key).filter((key) => this.draftKeys.includes(key));
            // A column that goes takes its filter along (only shown filters are sent): reload when
            // it was set.
            const narrowed = this.columns
                .filter((column) => column.filter && this.chosenKeys.includes(column.key) && !keys.includes(column.key))
                .map((column) => this.filters.find((filter) => filter.key === column.filter))
                .some((filter) => filter && !isDefault(filter, this.values[filter.key]));
            this.chosenKeys = keys;
            if (narrowed) {
                this.page = 1;
                this.fetch();
            }
            this.chooserOpen = false;
            if (!this.showSelect) {
                this.clearSelection();
            }
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
        isSelected(row) {
            return this.allSelected || this.selected[row.id] !== undefined;
        },
        // A row's checkbox; with Shift every row between the last one clicked and this one.
        onSelect(row, index, event) {
            const on = event.target.checked;
            const rows = event.shiftKey && this.lastSelectedIndex !== null
                ? this.rows.slice(Math.min(this.lastSelectedIndex, index), Math.max(this.lastSelectedIndex, index) + 1)
                : [row];
            this.setSelected(rows, on);
            this.lastSelectedIndex = index;
        },
        selectPage(on) {
            this.setSelected(this.rows, on);
        },
        setSelected(rows, on) {
            // "All" turns into the rows of this page once one is deselected.
            const selected = this.allSelected ? Object.fromEntries(this.rows.map((row) => [row.id, row])) : { ...this.selected };
            this.allSelected = false;
            rows.forEach((row) => {
                if (on) {
                    selected[row.id] = row;
                } else {
                    delete selected[row.id];
                }
            });
            this.selected = selected;
        },
        clearSelection() {
            this.selected = {};
            this.allSelected = false;
            this.lastSelectedIndex = null;
        },
        // Applies a bulk action to the selection (its ids, or `all` with the list's filters),
        // then reloads the page.
        runBulk(action, count) {
            this.closeMenus();
            const body = count === null
                ? i18n.t('base', 'Apply "{action}" to all {count} entries?', { action: action.label, count: this.total })
                : i18n.t('base', 'Apply "{action}" to {count} selected entries?', { action: action.label, count });

            return modal.confirm({ body, confirmText: action.label }).then((ok) => {
                if (!ok) {
                    return null;
                }
                let url = action.bulkUrl;
                let data;
                if (this.allSelected) {
                    const params = new URLSearchParams(requestParams(this.shownFilters, this.values)).toString();
                    url += params ? (url.includes('?') ? '&' : '?') + params : '';
                    data = { all: 1 };
                } else {
                    data = { ids: Object.values(this.selected).filter((row) => (row.actions || []).some((rowAction) => rowAction.id === action.id)).map((row) => row.id) };
                }
                return client.post(url, { data }).then((response) => {
                    if (response && response.message) {
                        status('success', response.message);
                    }
                    this.clearSelection();
                    return this.fetch();
                }).catch((response) => {
                    status('error', (response && response.message) || i18n.t('base', 'An unexpected error occurred.'));
                    log.error(response);
                });
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
                    icon: action.icon,
                    url: action.url,
                    sortOrder: index * 10,
                    htmlOptions: action.destructive ? { class: 'dropdown-item-danger' } : {},
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
                } else {
                    // An answer without the new row (e.g. another API's own shape): reload the page
                    this.fetch();
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
