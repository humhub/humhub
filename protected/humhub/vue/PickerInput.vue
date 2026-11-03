<template>
    <div ref="root" class="select-menu picker" :class="rootClass">
        <div ref="field" class="form-control picker-field" :class="{ 'is-invalid': isInvalid }" @click="onFieldClick">
            <template v-if="mode === 'multiple' && !cfg.listed">
                <span
                    v-for="item in chosen"
                    :key="item.id"
                    class="picker-chip"
                    :class="{ 'is-locked': item.locked }"
                    :data-value="item.id"
                    :title="item.locked ? msg('locked') : null"
                >
                    <PickerImage v-if="item.image" class="picker-chip-image" :image="item.image" :size="18" />
                    <span class="picker-chip-label">{{ item.text }}</span>
                    <button
                        v-if="!item.locked && !isDisabled"
                        type="button"
                        class="picker-chip-remove"
                        :aria-label="msg('remove', { label: item.text })"
                        :title="msg('remove', { label: item.text })"
                        @click.stop="remove(item.id)"
                    ><i class="ti ti-circle-x" aria-hidden="true"></i></button>
                </span>
            </template>
            <PickerImage
                v-if="mode !== 'multiple' && chosen.length && !editing && chosen[0].image"
                class="picker-value-image"
                :image="chosen[0].image"
                :size="24"
            />
            <input
                :id="inputId"
                ref="input"
                type="text"
                class="picker-input"
                role="combobox"
                autocomplete="off"
                spellcheck="false"
                autocapitalize="off"
                aria-autocomplete="list"
                :aria-expanded="open ? 'true' : 'false'"
                :aria-controls="listboxId"
                :aria-activedescendant="activeDescendant"
                :aria-label="accessibleName"
                :aria-describedby="select ? nativeDescribedBy : describedBy"
                :aria-busy="loading ? 'true' : null"
                :aria-invalid="isInvalid ? 'true' : null"
                :aria-required="cfg.required ? 'true' : null"
                :placeholder="inputPlaceholder"
                :value="inputText"
                :readonly="!searchable"
                :disabled="isDisabled"
                :autofocus="cfg.autofocus || null"
                :maxlength="cfg.maxInput > 0 ? cfg.maxInput : null"
                @beforeinput="onBeforeInput"
                @input="onInput"
                @keydown="onKeydown"
                @focus="onFocus"
                @blur="onBlur"
            >
        </div>
        <span v-if="loading" class="spinner-border spinner-border-sm select-menu-spinner" aria-hidden="true"></span>
        <i v-else-if="mode === 'select'" class="ti ti-chevron-down select-menu-chevron" aria-hidden="true"></i>
        <button
            v-if="clearable"
            type="button"
            class="select-menu-clear"
            :aria-label="msg('clear')"
            :title="msg('clear')"
            @click.stop="clear"
        ><i class="ti ti-x" aria-hidden="true"></i></button>
        <ul
            :id="listboxId"
            ref="listbox"
            class="dropdown-menu select-menu-drawer"
            :class="{ show: open }"
            data-bs-popper="static"
            role="listbox"
            :aria-label="accessibleName"
            :aria-multiselectable="mode === 'multiple' ? 'true' : null"
            :aria-busy="loading ? 'true' : null"
            @scroll="onListScroll"
        >
            <template v-for="row in rows" :key="row.key">
                <li v-if="row.header" class="dropdown-header" role="presentation">{{ row.header }}</li>
                <li
                    v-else
                    :id="optionId(row.index)"
                    class="dropdown-item select-menu-option picker-option"
                    :class="{ active: isChosen(row.item.id), 'is-focused': row.index === activeIndex, disabled: row.item.disabled, 'is-new': row.item.isNew }"
                    role="option"
                    :aria-selected="isChosen(row.item.id) ? 'true' : 'false'"
                    :aria-disabled="row.item.disabled ? 'true' : null"
                    @pointerdown.prevent
                    @click="choose(row.item)"
                    @mousemove="activeIndex = row.index"
                >
                    <PickerImage v-if="row.item.image" class="picker-option-image" :image="row.item.image" :size="optionImageSize" />
                    <span class="picker-option-text">
                        <span class="picker-option-label"><template v-for="(part, index) in highlight(row.item)" :key="index"><mark v-if="part.match">{{ part.text }}</mark><template v-else>{{ part.text }}</template></template></span>
                        <span v-if="row.item.subText" class="picker-option-subtext">{{ row.item.subText }}</span>
                    </span>
                </li>
            </template>
            <li v-if="feedback" class="dropdown-item-text select-menu-feedback" role="presentation">{{ feedback }}</li>
        </ul>
        <ul v-if="cfg.listed && chosen.length" class="list-unstyled picker-list">
            <li
                v-for="item in chosen"
                :key="item.id"
                class="picker-list-item"
                :data-value="item.id"
                :title="item.locked ? msg('locked') : null"
            >
                <PickerImage v-if="item.image" class="picker-list-item-image" :image="item.image" :size="24" />
                <span class="picker-list-item-text">
                    <span class="picker-list-item-label">{{ item.text }}</span>
                    <span v-if="item.subText" class="picker-list-item-subtext">{{ item.subText }}</span>
                </span>
                <button
                    v-if="!item.locked && !isDisabled"
                    type="button"
                    class="picker-list-item-remove"
                    :aria-label="msg('remove', { label: item.text })"
                    :title="msg('remove', { label: item.text })"
                    @click="remove(item.id)"
                ><i class="ti ti-x" aria-hidden="true"></i></button>
            </li>
        </ul>
        <template v-if="cfg.name && !select">
            <input v-for="(value, index) in hiddenValues" :key="index" type="hidden" :name="hiddenName" :value="value">
        </template>
        <span class="visually-hidden" role="status">{{ open ? (feedback || '') : '' }}</span>
    </div>
</template>

<script>
import { i18n, log } from '@humhub/vue';
import comboboxMixin from './picker/comboboxMixin.js';
import { toImage } from './picker/images.js';
import { labelOf, pruneAfterReset, readConfig, readOption, readSelection, writeSelection } from './picker/nativeSelect.js';
import PickerImage from './picker/PickerImage.vue';
import { createSource, matches, normalizeItem } from './picker/sources.js';

/**
 * The typing pause before a remote search.
 */
export const SEARCH_DEBOUNCE_MS = 250;

/**
 * The items a remote search lists when `maxResults` is not given.
 */
export const DEFAULT_REMOTE_RESULTS = 8;

/**
 * The rows a long list renders at once; further ones follow on scroll and when the active
 * option is beyond (all icons of `IconPicker` are thousands).
 */
export const RENDER_STEP = 200;

let uid = 0;

const toIds = (value) => (Array.isArray(value) ? value : [value])
    .filter((id) => id !== null && id !== undefined)
    .map(String)
    .filter((id) => id !== '');

const sameIds = (a, b) => a.length === b.length && a.every((id, index) => id === b[index]);

const format = (text, params) => String(text).replace(/\{(\w+)\}/g, (all, name) => (params[name] ?? all));

const DEFAULT_MESSAGES = {
    noResults: () => i18n.t('base', 'No results found!'),
    inputTooShort: ({ n }) => i18n.t('base', 'Please enter at least {n} characters.', { n }),
    maximumSelected: ({ n }) => i18n.t('base', 'You cannot choose more than {n}.', { n }),
    loading: () => i18n.t('base', 'Loading...'),
    error: () => i18n.t('base', 'The results could not be loaded.'),
    locked: () => i18n.t('base', 'You are not allowed to remove this item.'),
    add: ({ text }) => i18n.t('base', 'Add: “{text}”', { text }),
    remove: ({ label }) => i18n.t('base', 'Remove {label}', { label }),
    clear: () => i18n.t('base', 'Clear selection'),
};

/**
 * The picker and enhanced select of the platform (`<picker-input>`), the replacement of Select2
 * — the binding description is docs/develop/ui-js-vuejs-picker.md ("`PickerInput`"):
 *
 * - Modes: `multiple` (chips, or rows below the field with `listed`), single record (a source,
 *   or `maxSelection` 1), enhanced select (static options, not `multiple`).
 * - Items `{ id, text, subText, image, disabled, locked }` from a source (`picker/sources.js`):
 *   the static `options`, the legacy `url`, or the named `source`.
 * - `v-model` holds the id (single) or the ids (`multiple`); with `selectId` the native
 *   `<select>` is the model instead (native mode, see `picker/nativeSelect.js`).
 * - Combobox basics (open state, active option, outside click, list positioning) from
 *   `picker/comboboxMixin.js`; styled by `resources/scss/_picker.scss`.
 *
 * @since 1.20
 */
export default {
    name: 'PickerInput',
    components: { PickerImage },
    mixins: [comboboxMixin],
    props: {
        modelValue: { type: [String, Number, Array], default: '' },
        multiple: { type: Boolean, default: false },
        maxSelection: { type: Number, default: 0 },
        source: { type: String, default: null },
        url: { type: String, default: null },
        sourceParams: { type: Object, default: () => ({}) },
        itemKey: { type: String, default: 'id' },
        options: { type: Array, default: () => [] },
        initialItems: { type: Array, default: () => [] },
        defaultResults: { type: Array, default: () => [] },
        suggested: { type: Number, default: 0 },
        minInput: { type: Number, default: 0 },
        maxInput: { type: Number, default: 0 },
        maxResults: { type: Number, default: null },
        search: { type: Number, default: 5 },
        allowNew: { type: Boolean, default: false },
        newPrefix: { type: String, default: '' },
        allowClear: { type: Boolean, default: true },
        listed: { type: Boolean, default: false },
        lockedIds: { type: Array, default: () => [] },
        disabledIds: { type: Array, default: () => [] },
        placeholder: { type: String, default: '' },
        placeholderMore: { type: String, default: '' },
        label: { type: String, default: '' },
        describedBy: { type: String, default: null },
        disabled: { type: Boolean, default: false },
        required: { type: Boolean, default: false },
        invalid: { type: Boolean, default: false },
        autofocus: { type: Boolean, default: false },
        messages: { type: Object, default: () => ({}) },
        id: { type: String, default: null },
        name: { type: String, default: null },
        selectId: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        const key = ++uid;
        return {
            chosen: [],
            renderLimit: RENDER_STEP,
            query: '',
            editing: false,
            results: [],
            // The term the current `results` answer (`null`: none).
            resultsTerm: null,
            loading: false,
            failed: false,
            remote: false,
            // Native mode: the config read from the `<select>`, and its live state.
            nativeConfig: null,
            nativeDisabled: false,
            nativeInvalid: false,
            nativeDescribedBy: null,
            select: null,
            listboxId: `picker-input-${key}-listbox`,
            fallbackId: `picker-input-${key}`,
        };
    },
    computed: {
        // The effective settings: the props, overridden by what a native `<select>` says.
        cfg() {
            return this.nativeConfig ? { ...this.$props, ...this.nativeConfig } : this.$props;
        },
        inputId() {
            return this.cfg.id || this.fallbackId;
        },
        hasSource() {
            return !!(this.cfg.source || this.cfg.url);
        },
        mode() {
            if (this.cfg.multiple && this.cfg.maxSelection !== 1) {
                return 'multiple';
            }
            return this.hasSource || this.cfg.multiple ? 'single' : 'select';
        },
        isDisabled() {
            return this.cfg.disabled || this.nativeDisabled;
        },
        isInvalid() {
            return this.cfg.invalid || this.nativeInvalid;
        },
        staticItems() {
            return this.cfg.options
                .filter((option) => String(option.id) !== '')
                .map((option) => this.withFlags({ ...normalizeItem(option), group: option.group ?? null }));
        },
        // The text of an empty option (`prompt`) of an enhanced select, its placeholder.
        emptyText() {
            const empty = this.cfg.options.find((option) => String(option.id) === '');
            return empty ? String(empty.text ?? '') : '';
        },
        searchable() {
            if (this.mode !== 'select') {
                return true;
            }
            if (this.cfg.search < 0) {
                return false;
            }
            return this.staticItems.length >= this.cfg.search;
        },
        term() {
            return this.query.trim();
        },
        chosenIds() {
            return this.chosen.map((item) => item.id);
        },
        limitReached() {
            return this.mode === 'multiple' && this.cfg.maxSelection > 0 && this.chosen.length >= this.cfg.maxSelection;
        },
        staticLimit() {
            return this.cfg.maxResults ?? 0;
        },
        offered() {
            if (this.limitReached) {
                return [];
            }
            let list = this.remote ? this.results : this.staticItems.filter((item) => matches(item.text, this.editing ? this.term : ''));
            // Chosen items are left out first, so the limit counts what is offered.
            if (this.mode === 'multiple') {
                list = list.filter((item) => !this.chosenIds.includes(item.id));
            }
            const limit = this.remote ? this.remoteLimit(this.resultsTerm) : this.staticLimit;
            return limit > 0 ? list.slice(0, limit) : list;
        },
        newChoice() {
            if (!this.cfg.allowNew || this.limitReached || this.term.length < Math.max(this.cfg.minInput, 1)) {
                return null;
            }
            const text = this.term.toLocaleLowerCase();
            if ([...this.offered, ...this.chosen].some((item) => item.text.toLocaleLowerCase() === text)) {
                return null;
            }
            return {
                id: `${this.cfg.newPrefix}${this.term}`,
                text: this.term,
                subText: null,
                image: { type: 'icon', name: 'circle-plus' },
                disabled: false,
                locked: false,
                isNew: true,
            };
        },
        choices() {
            return this.newChoice ? [...this.offered, this.newChoice] : this.offered;
        },
        comboboxOptions() {
            return this.choices;
        },
        // One size per list: larger once an option has a sub text (two lines).
        optionImageSize() {
            return this.choices.some((item) => item.subText) ? 40 : 32;
        },
        rows() {
            const rows = [];
            let group = null;
            this.choices.forEach((item, index) => {
                if (index >= this.renderLimit) {
                    return;
                }
                if (item.group && item.group !== group) {
                    group = item.group;
                    rows.push({ key: `group:${group}`, header: group });
                }
                rows.push({ key: `${item.isNew ? 'new' : 'item'}:${item.id}`, item, index });
            });
            return rows;
        },
        feedback() {
            if (this.limitReached) {
                return this.msg('maximumSelected', { n: this.cfg.maxSelection });
            }
            if (this.choices.length) {
                return null;
            }
            if (this.loading) {
                return this.msg('loading');
            }
            if (this.failed) {
                return this.msg('error');
            }
            if (this.remote && this.term.length < this.cfg.minInput) {
                return this.msg('inputTooShort', { n: this.cfg.minInput });
            }
            if (this.remote && this.term === '') {
                return null;
            }
            return this.msg('noResults');
        },
        inputText() {
            if (this.mode === 'multiple' || this.editing) {
                return this.query;
            }
            return this.chosen.length ? this.chosen[0].text : '';
        },
        inputPlaceholder() {
            if (this.mode === 'multiple') {
                return this.chosen.length ? this.cfg.placeholderMore : (this.cfg.placeholder || this.emptyText);
            }
            if (this.editing && this.chosen.length) {
                return this.chosen[0].text;
            }
            return this.cfg.placeholder || this.emptyText;
        },
        accessibleName() {
            return this.cfg.label || this.cfg.placeholder || null;
        },
        activeDescendant() {
            return this.open && this.activeIndex >= 0 && this.activeIndex < this.choices.length ? this.optionId(this.activeIndex) : null;
        },
        clearable() {
            if (this.isDisabled || !this.chosen.some((item) => !item.locked)) {
                return false;
            }
            return this.mode !== 'select' || this.cfg.allowClear;
        },
        hiddenName() {
            return this.cfg.multiple && this.chosen.length ? `${this.cfg.name}[]` : this.cfg.name;
        },
        hiddenValues() {
            return this.chosen.length ? this.chosenIds : [''];
        },
        rootClass() {
            return {
                'is-open': this.open,
                'has-selection': this.chosen.length > 0,
                'is-disabled': this.isDisabled,
                'is-loading': this.loading,
                'is-multiple': this.mode === 'multiple',
                'is-single': this.mode === 'single',
                'is-select': this.mode === 'select',
                'is-listed': this.cfg.listed,
                'is-required': this.cfg.required,
                'is-drop-up': this.dropUp,
                'is-editing': this.editing,
            };
        },
    },
    watch: {
        modelValue(value) {
            if (this.select) {
                return;
            }
            const ids = toIds(value).slice(0, this.mode === 'multiple' ? undefined : 1);
            if (!sameIds(ids, this.chosenIds)) {
                this.chosen = this.lockedFirst(this.itemsFor(ids));
            }
        },
        isDisabled(disabled) {
            if (disabled) {
                this.close();
            }
        },
        activeIndex(index) {
            if (index >= this.renderLimit) {
                this.renderLimit = index + RENDER_STEP;
            }
        },
        term() {
            this.renderLimit = RENDER_STEP;
        },
        choices(choices) {
            if (this.activeIndex >= choices.length) {
                this.activeIndex = choices.length - 1;
            }
            // The answer to a typed text arrived: its first item is active again.
            if (this.open && this.editing && this.activeIndex < 0 && choices.length) {
                this.activeIndex = 0;
            }
        },
        comboboxOptions() {
            this.$nextTick(() => this.placeDrawer());
        },
        feedback() {
            this.$nextTick(() => this.placeDrawer());
        },
    },
    created() {
        // Items by id, as far as seen (options, initial items, answers) — for labels of ids.
        this.known = new Map();
        // The term of an Enter on the new item waiting for the answer (see chooseTypedText()).
        this.pendingEnter = null;
        this.cache = new Map();
        this.seq = 0;
        this.timer = null;
        this.initNative();
        this.sourceImpl = createSource({
            source: this.cfg.source,
            url: this.cfg.url,
            options: this.cfg.options,
            itemKey: this.cfg.itemKey,
            params: this.cfg.sourceParams,
        });
        this.remote = this.sourceImpl.remote;
        this.remember(this.staticItems);
        this.remember(this.cfg.initialItems.map((item) => this.withFlags(normalizeItem(item))));
        this.remember(this.cfg.defaultResults.map((item) => this.withFlags(normalizeItem(item))));
        if (!this.select) {
            const ids = toIds(this.modelValue).slice(0, this.mode === 'multiple' ? undefined : 1);
            this.chosen = this.lockedFirst(this.itemsFor(ids));
        }
    },
    mounted() {
        this.mountNative();
        if (this.cfg.autofocus) {
            this.$refs.input?.focus();
        }
    },
    beforeUnmount() {
        clearTimeout(this.timer);
        this.unmountNative();
    },
    methods: {
        // Native mode: binds to the `<select>` of `selectId` (picker/nativeSelect.js). Runs in
        // `created()` before the source is created, so `cfg` holds the select's settings there.
        initNative() {
            if (!this.selectId) {
                return;
            }
            this.select = document.getElementById(this.selectId);
            if (!this.select) {
                log.error(`PickerInput: no <select id="${this.selectId}">`);
                return;
            }
            // `disabled`, `invalid` and `aria-describedby` follow the select (`native…`).
            const { modelValue, initialItems, disabled, invalid, ...config } = readConfig(this.select);
            this.nativeConfig = config;
            this.initialValues = [...this.select.options].map((option) => option.value);
            this.nativeDisabled = this.select.disabled;
            this.nativeInvalid = this.select.classList.contains('is-invalid');
            this.nativeDescribedBy = this.select.getAttribute('aria-describedby');
            this.chosen = this.nativeItems(initialItems);
        },
        // The items read from the `<select>`, their images as descriptions.
        nativeItems(items) {
            return this.lockedFirst(items.map((item) => this.withFlags({ ...item, image: toImage(item.image) })));
        },
        mountNative() {
            const select = this.select;
            if (!select) {
                return;
            }
            // Visually hidden, as Select2 did, not `hidden`: scripts and WebDriver's `selectOption()`
            // still reach it. Out of the tab order and the accessibility tree; `autofocus` moves to the input.
            this.nativeAttributes = Object.fromEntries(['tabindex', 'aria-hidden', 'autofocus'].map((name) => [name, select.getAttribute(name)]));
            select.classList.add('visually-hidden');
            select.setAttribute('tabindex', '-1');
            select.setAttribute('aria-hidden', 'true');
            select.removeAttribute('autofocus');
            this.onNativeChange = () => {
                if (this.writing) {
                    return;
                }
                // An enhanced select's options may have changed as well.
                if (!this.hasSource) {
                    this.nativeConfig = { ...this.nativeConfig, options: [...select.options].map(readOption) };
                    this.remember(this.staticItems);
                }
                this.chosen = this.nativeItems(readSelection(select));
                this.remember(this.chosen);
            };
            // The form resets the options' selection after the event: read it right after.
            this.onNativeReset = () => {
                this.resetTimer = setTimeout(() => {
                    if (this.hasSource) {
                        pruneAfterReset(select, this.initialValues);
                    }
                    this.onNativeChange();
                }, 0);
            };
            // jQuery's `.trigger('change')` reaches jQuery handlers only (and a native `change` both).
            if (window.jQuery) {
                window.jQuery(select).on('change.picker', this.onNativeChange);
            } else {
                select.addEventListener('change', this.onNativeChange);
            }
            this.nativeForm = select.form;
            this.nativeForm?.addEventListener('reset', this.onNativeReset);
            this.observer = new MutationObserver(() => {
                this.nativeDisabled = select.disabled;
                this.nativeInvalid = select.classList.contains('is-invalid');
                this.nativeDescribedBy = select.getAttribute('aria-describedby');
            });
            this.observer.observe(select, { attributes: true, attributeFilter: ['disabled', 'class', 'aria-describedby'] });
            const label = labelOf(select);
            if (label) {
                this.labelEl = label;
                // A label would focus the hidden select: the input takes its place. A click inside
                // the picker (a wrapping label) is the picker's own.
                this.onLabelClick = (event) => {
                    event.preventDefault();
                    if (!this.$refs.root?.contains(event.target)) {
                        this.$refs.input?.focus();
                    }
                };
                label.addEventListener('click', this.onLabelClick);
            }
        },
        unmountNative() {
            const select = this.select;
            if (!select) {
                return;
            }
            if (window.jQuery) {
                window.jQuery(select).off('change.picker', this.onNativeChange);
            } else {
                select.removeEventListener('change', this.onNativeChange);
            }
            this.nativeForm?.removeEventListener('reset', this.onNativeReset);
            clearTimeout(this.resetTimer);
            this.observer?.disconnect();
            this.labelEl?.removeEventListener('click', this.onLabelClick);
            if (select.isConnected) {
                select.classList.remove('visually-hidden');
                Object.entries(this.nativeAttributes).forEach(([name, value]) => (value === null ? select.removeAttribute(name) : select.setAttribute(name, value)));
                delete select.dataset.pickerBound;
            }
        },
        writeNative(items) {
            if (!this.select) {
                return;
            }
            this.writing = true;
            try {
                writeSelection(this.select, items, { prune: this.hasSource });
            } finally {
                this.writing = false;
            }
        },
        msg(key, params = {}) {
            const given = this.cfg.messages[key];
            return typeof given === 'string' && given !== '' ? format(given, params) : DEFAULT_MESSAGES[key](params);
        },
        withFlags(item) {
            return {
                ...item,
                locked: item.locked || this.cfg.lockedIds.map(String).includes(item.id),
                disabled: item.disabled || this.cfg.disabledIds.map(String).includes(item.id),
            };
        },
        remember(items) {
            items.forEach((item) => this.known.set(item.id, item));
        },
        itemsFor(ids) {
            return ids.map((id) => this.known.get(id) || this.chosen.find((item) => item.id === id) || this.withFlags(normalizeItem({ id, text: id })));
        },
        lockedFirst(items) {
            return [...items.filter((item) => item.locked), ...items.filter((item) => !item.locked)];
        },
        isChosen(id) {
            return this.chosenIds.includes(id);
        },
        highlight(item) {
            if (item.isNew) {
                return [{ text: this.msg('add', { text: item.text }), match: false }];
            }
            const term = this.editing ? this.term : '';
            const at = term === '' ? -1 : item.text.toLocaleLowerCase().indexOf(term.toLocaleLowerCase());
            if (at < 0) {
                return [{ text: item.text, match: false }];
            }
            return [
                { text: item.text.slice(0, at), match: false },
                { text: item.text.slice(at, at + term.length), match: true },
                { text: item.text.slice(at + term.length), match: false },
            ].filter((part) => part.text !== '');
        },
        commit(items) {
            this.chosen = items;
            this.remember(items);
            this.writeNative(items);
            this.$emit('update:modelValue', this.cfg.multiple ? [...this.chosenIds] : (this.chosenIds[0] ?? ''));
        },
        choose(item) {
            if (!item || item.disabled) {
                return;
            }
            // A new item takes the source's image for a new item (`newImage`), not the add icon.
            const chosenItem = item.isNew ? { ...item, isNew: false, image: this.sourceImpl?.newImage ?? null } : item;
            if (this.mode === 'multiple') {
                if (!this.isChosen(item.id)) {
                    this.commit([...this.chosen, chosenItem]);
                }
                this.query = '';
                this.runSearch();
                this.$refs.input?.focus();
                return;
            }
            this.close();
            // A locked value stays.
            if (this.chosenIds[0] !== item.id && !this.chosen[0]?.locked) {
                this.commit([chosenItem]);
            }
        },
        remove(id) {
            const item = this.chosen.find((entry) => entry.id === id);
            if (!item || item.locked || this.isDisabled) {
                return;
            }
            this.commit(this.chosen.filter((entry) => entry.id !== id));
            this.$refs.input?.focus();
        },
        clear() {
            this.query = '';
            this.editing = false;
            this.commit(this.chosen.filter((item) => item.locked));
            this.$refs.input?.focus();
        },
        runSearch() {
            if (!this.remote) {
                return;
            }
            clearTimeout(this.timer);
            this.failed = false;
            const term = this.term;
            if (term === '') {
                this.suggest();
                return;
            }
            if (term.length < this.cfg.minInput) {
                this.seq++;
                this.loading = false;
                this.setResults(null, []);
                return;
            }
            if (this.cache.has(term)) {
                this.seq++;
                this.loading = false;
                this.setResults(term, this.cache.get(term));
                return;
            }
            const seq = ++this.seq;
            this.loading = true;
            // No answer of an older term is listed (or chosen by Enter) while this one loads.
            this.setResults(null, []);
            this.timer = setTimeout(() => this.load(term, seq), SEARCH_DEBOUNCE_MS);
        },
        // What the list shows before anything is typed (remote sources).
        suggest() {
            this.seq++;
            this.loading = false;
            if (this.cfg.defaultResults.length) {
                this.setResults('', this.cfg.defaultResults.map((item) => this.withFlags(normalizeItem(item))));
                return;
            }
            if (this.remoteLimit('') === 0) {
                this.setResults('', []);
                return;
            }
            if (this.cache.has('')) {
                this.setResults('', this.cache.get(''));
                return;
            }
            this.load('', ++this.seq);
        },
        // How many items of the answer to `term` are listed: the suggestions for the empty term
        // (`suggested`, else the remote limit when no input is required), `maxResults` (8) else.
        remoteLimit(term) {
            const max = this.cfg.maxResults ?? DEFAULT_REMOTE_RESULTS;
            if (term !== '') {
                return max;
            }
            if (this.cfg.defaultResults.length) {
                return Infinity;
            }
            if (this.cfg.suggested > 0) {
                return this.cfg.suggested;
            }
            return this.cfg.minInput === 0 ? max : 0;
        },
        // Enter pressed on the new item before the answer came: an offered item of the typed
        // name is chosen, else the new item.
        chooseTypedText() {
            this.pendingEnter = null;
            const text = this.term.toLocaleLowerCase();
            this.choose(this.offered.find((item) => item.text.toLocaleLowerCase() === text) ?? this.newChoice);
        },
        setResults(term, list) {
            this.resultsTerm = term;
            this.results = list;
        },
        load(term, seq) {
            this.loading = true;
            // The executor asks the source right away; a source throwing synchronously fails like a rejected request.
            new Promise((resolve) => {
                resolve(this.sourceImpl.search(term));
            }).then((items) => {
                if (seq !== this.seq) {
                    return;
                }
                const list = items.map((item) => this.withFlags(item));
                this.cache.set(term, list);
                this.remember(list);
                this.setResults(term, list);
                if (this.pendingEnter === term) {
                    this.chooseTypedText();
                }
            }).catch((error) => {
                if (seq !== this.seq) {
                    return;
                }
                this.failed = true;
                this.setResults(null, []);
                log.error(error);
                // No answer will come: a waiting Enter takes the new item.
                if (this.pendingEnter === term) {
                    this.chooseTypedText();
                }
            }).finally(() => {
                if (seq === this.seq) {
                    this.loading = false;
                }
            });
        },
        // Whether the listed results answer another term than the current one.
        resultsStale() {
            return this.remote && this.resultsTerm !== this.term && !this.loading;
        },
        // Near the end of the rendered rows: the next ones.
        onListScroll(event) {
            const list = event.target;
            if (this.renderLimit < this.choices.length && list.scrollTop + list.clientHeight >= list.scrollHeight - 100) {
                this.renderLimit += RENDER_STEP;
            }
        },
        onFocus() {
            if (this.resultsStale()) {
                this.runSearch();
            }
        },
        // The input of a single picker / searchable select shows the chosen label: typing
        // replaces it instead of being appended to it — the browser inserts into an emptied input.
        onBeforeInput(event) {
            if (this.mode !== 'multiple' && this.searchable && !this.editing && event.target.value !== '') {
                event.target.value = '';
            }
        },
        // Leaving the closed control drops typed text (after Escape, say) — the value shows again.
        onBlur() {
            if (!this.open) {
                this.close();
            }
        },
        onFieldClick() {
            if (this.isDisabled) {
                return;
            }
            this.$refs.input?.focus();
            if (this.open && this.mode === 'select') {
                this.hide();
                return;
            }
            this.show(-1);
        },
        onInput(event) {
            this.pendingEnter = null;
            this.query = event.target.value;
            this.editing = true;
            this.runSearch();
            this.show(0);
            this.activeIndex = 0;
        },
        show(activeIndex = -1) {
            if (this.isDisabled || this.open) {
                return;
            }
            this.open = true;
            this.activeIndex = activeIndex;
            this.bindOutside();
            if (this.resultsStale()) {
                this.runSearch();
            }
        },
        hide() {
            if (!this.open) {
                return;
            }
            this.open = false;
            this.activeIndex = -1;
            this.unbindOutside();
        },
        close() {
            this.pendingEnter = null;
            clearTimeout(this.timer);
            if (this.loading) {
                this.seq++;
                this.loading = false;
            }
            this.query = '';
            this.editing = false;
            this.hide();
        },
        typeAhead(char) {
            const count = this.choices.length;
            if (!count) {
                return;
            }
            if (!this.open) {
                this.show(-1);
            }
            for (let step = 1; step <= count; step++) {
                const index = (this.activeIndex + step + count) % count;
                if (this.choices[index].text.toLocaleLowerCase().startsWith(char.toLocaleLowerCase())) {
                    this.moveTo(index);
                    return;
                }
            }
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
                case 'PageDown':
                case 'PageUp':
                    if (this.open && this.choices.length) {
                        event.preventDefault();
                        this.movePage(event.key === 'PageDown' ? 1 : -1);
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
                    // Never submits the surrounding form (a `<select>` does not either): opens the list when closed.
                    event.preventDefault();
                    if (!this.open) {
                        this.show(0);
                    } else {
                        const item = this.choices[this.activeIndex];
                        // The answer to the typed text is still out and may hold an item of that name:
                        // choose once it is in (chooseTypedText()), not a duplicate new item now.
                        if (item?.isNew && this.remote && !this.failed && this.resultsTerm !== this.term) {
                            this.pendingEnter = this.term;
                            break;
                        }
                        this.choose(item);
                    }
                    break;
                case 'Escape':
                    if (this.open || this.query !== '') {
                        // Handled here: a surrounding dialog must not close as well.
                        event.preventDefault();
                        event.stopPropagation();
                        if (this.open) {
                            this.hide();
                        } else {
                            this.close();
                        }
                    }
                    break;
                case 'Backspace':
                    if (this.mode === 'multiple' && this.query === '') {
                        const lastRemovable = [...this.chosen].reverse().find((item) => !item.locked);
                        if (lastRemovable) {
                            event.preventDefault();
                            this.remove(lastRemovable.id);
                        }
                    }
                    break;
                case 'Tab':
                    this.close();
                    break;
                default:
                    if (!this.searchable && event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                        this.typeAhead(event.key);
                    }
            }
        },
    },
};
</script>
