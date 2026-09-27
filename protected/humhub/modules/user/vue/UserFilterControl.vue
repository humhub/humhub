<template>
    <div
        ref="root"
        class="c-select c-picker c-user-filter"
        :class="{ 'is-open': open, 'has-selection': hasValue, 'is-disabled': resolving, 'is-loading': resolving }"
    >
        <div class="c-picker__field" @click="onFieldClick">
            <span
                v-if="hasValue && !resolving"
                class="c-picker__chip c-user-filter__chip"
                role="group"
                :aria-label="accessibleName"
            >
                <span class="c-user-filter__avatar" aria-hidden="true">
                    <UserImage v-if="user" v-bind="imageProps(user)" :size="18" :link="false" />
                </span>
                <span class="c-picker__chip-label">{{ chipLabel }}</span>
                <button
                    :id="inputId"
                    ref="remove"
                    type="button"
                    class="c-picker__chip-remove"
                    :aria-label="removeLabel"
                    :title="removeLabel"
                    @click.stop="remove"
                ><i class="ti ti-x" aria-hidden="true"></i></button>
            </span>
            <template v-else>
                <i class="ti ti-user c-user-filter__icon" aria-hidden="true"></i>
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
                    :aria-busy="resolving || searching ? 'true' : null"
                    :placeholder="placeholder"
                    :value="query"
                    :disabled="resolving"
                    @input="onInput"
                    @keydown="onKeydown"
                >
            </template>
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
                :key="result.id"
                class="c-select__option c-user-filter__option"
                :class="{ 'is-active': index === activeIndex }"
                role="option"
                :aria-selected="index === activeIndex ? 'true' : 'false'"
                @pointerdown.prevent
                @click="choose(result)"
                @mousemove="activeIndex = index"
            >
                <span class="c-user-filter__avatar" aria-hidden="true">
                    <UserImage v-bind="imageProps(result)" :size="24" :link="false" />
                </span>
                <span class="c-user-filter__name">{{ result.displayName }}</span>
            </li>
            <li v-if="feedback" class="c-select__feedback" role="presentation">{{ feedback }}</li>
        </ul>
        <span class="visually-hidden" role="status">{{ open ? (feedback || '') : '' }}</span>
    </div>
</template>

<script>
import { apiUrl, client, i18n, log } from '@humhub/vue';
import UserImage from './UserImage.vue';

/**
 * The typing pause before a search, as `FilterBar`'s text filters (`TEXT_DEBOUNCE_MS`) — not
 * imported: the user module's bundle must not carry the core's `FilterBar`.
 */
export const SEARCH_DEBOUNCE_MS = 300;

/**
 * The most suggestions one search shows.
 */
export const MAX_SUGGESTIONS = 8;

let uid = 0;

const idOf = (value) => (value === null || value === undefined ? '' : String(value));

// A user id as the server takes it: a positive integer, without a sign or leading zeros.
const ID_PATTERN = /^[1-9]\d*$/;

/**
 * The control of the `user` filter type of `FilterBar` (`registerFilterType('user',
 * 'UserFilterControl')` in the user module's `vue/index.js`; the PHP side is
 * `humhub\modules\user\components\listing\UserFilter`): one person, searched by name — a list's
 * "Author" or "Assignee". A page renders it by a `user` definition in its bar and needs the user
 * module's Vue bundle (`UserVueAsset` in its asset bundle's `$depends`).
 *
 * After the WAI-ARIA combobox pattern, styled as a `FilterPicker` (`.c-select`, `.c-picker`,
 * with a person icon and avatars, `.c-user-filter` in `_page-toolbar.scss`):
 *
 * - Props: the filter-type contract — `filter` (the definition: `placeholder`, else `label`, as
 *   placeholder; `label` as accessible name), `modelValue` (the user id as a string, `''` for
 *   none — how the bar reads it from the URL), `inputId` (the id of the focusable control).
 *   Emits `update:modelValue` with the chosen user's id as a string, `''` when removed.
 * - Without a value: a search field. A typed text (trimmed, not empty) is searched after
 *   `SEARCH_DEBOUNCE_MS` in `GET /api/v2/user?purpose=picker&q=<text>&pageSize=8` — the users the
 *   caller may see, the ones the server accepts as the value; only the answer to the latest text
 *   is shown, its first suggestion active. The suggestions are avatar and name.
 * - With a value: a chip (avatar, name, a remove X, which empties the value and returns the
 *   focus to the search field), a `group` named after the filter's `label`, so the filter's
 *   name stays with the choice. A value the control has not seen among its suggestions (from
 *   the page URL) is resolved once with `GET /api/v2/user?purpose=picker&ids=<id>` — the field
 *   is disabled with a spinner meanwhile —; a value naming nobody the caller may see is emptied,
 *   one that is no user id at all (a positive integer) at once, without a request.
 * - Keyboard: ArrowDown/ArrowUp open the listbox and, like Home/End, move the active suggestion
 *   (`aria-activedescendant`, `aria-selected`; the focus stays in the input), Enter chooses it, Escape closes the
 *   listbox and, pressed again, drops the typed text, Tab closes it and moves on. A click outside
 *   closes it.
 * - A failed request is logged; the control stays usable (a value it could not resolve shows as
 *   the id, and can be removed).
 *
 * Its messages are of the `base` category (those of `FilterPicker`), which every page with a
 * filter bar loads already — a page embedding it declares no category of the user module.
 *
 * @since 1.20
 */
export default {
    name: 'UserFilterControl',
    components: { UserImage },
    props: {
        filter: { type: Object, required: true },
        modelValue: { type: [String, Number], default: '' },
        inputId: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            query: '',
            open: false,
            activeIndex: -1,
            results: [],
            // The text the shown results answer (`null`: none yet).
            resultsTerm: null,
            searching: false,
            resolving: false,
            // The chosen user, once known (from a suggestion or resolved by id).
            user: null,
            listboxId: `user-filter-${++uid}-listbox`,
        };
    },
    computed: {
        value() {
            return idOf(this.modelValue);
        },
        hasValue() {
            return this.value !== '';
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
        chipLabel() {
            return this.user ? this.user.displayName : this.value;
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
        removeLabel() {
            return i18n.t('base', 'Remove {label}', { label: this.chipLabel });
        },
    },
    watch: {
        value: {
            handler(value) {
                this.show(value);
                if (this.pendingFocus) {
                    // After a choice the chip's X, after a removal the input: whichever the
                    // applied value renders keeps the focus in the control.
                    this.pendingFocus = false;
                    this.$nextTick(() => (this.$refs.input || this.$refs.remove)?.focus());
                }
            },
        },
    },
    created() {
        this.searchSeq = 0;
        this.resolveSeq = 0;
        this.searchTimer = null;
        this.pendingFocus = false;
        // Users seen among the suggestions, by id: a value chosen from them needs no request.
        this.known = new Map();
        // Not an immediate watcher: that would run before the fields above exist.
        this.show(this.value);
    },
    beforeUnmount() {
        clearTimeout(this.searchTimer);
        this.unbindOutside();
    },
    methods: {
        optionId(index) {
            return `${this.listboxId}-${index}`;
        },
        // What `UserImage` takes of a list item — the item carries more (`title`, `tags` …),
        // which would otherwise fall through to the DOM as attributes.
        imageProps(user) {
            return {
                guid: user.guid,
                displayName: user.displayName,
                url: user.url,
                imageUrl: user.imageUrl,
                contentContainerId: user.contentContainerId ?? null,
            };
        },
        // The user a (new) value names: known, resolved, or none.
        show(value) {
            // A resolve of an older value is of no use any more.
            this.resolveSeq++;
            this.resolving = false;
            if (value === '') {
                this.user = null;
                return;
            }
            if (this.user && idOf(this.user.id) === value) {
                return;
            }
            if (this.known.has(value)) {
                this.user = this.known.get(value);
                return;
            }
            this.user = null;
            if (!ID_PATTERN.test(value)) {
                // No user id at all (a mangled link): the server would refuse it, no need to ask.
                this.$emit('update:modelValue', '');
                return;
            }
            this.resolve(value);
        },
        resolve(value) {
            const seq = this.resolveSeq;
            this.resolving = true;
            client.get(apiUrl('user', { purpose: 'picker', ids: value })).then((response) => {
                if (seq !== this.resolveSeq) {
                    return;
                }
                const user = (response.results || []).find((entry) => idOf(entry.id) === value);
                if (user) {
                    this.known.set(value, user);
                    this.user = user;
                } else {
                    // Nobody the caller may see: the server would refuse the value.
                    this.$emit('update:modelValue', '');
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
            if (!this.hasValue && !this.resolving) {
                this.$refs.input?.focus();
            }
        },
        onInput(event) {
            this.query = event.target.value;
            this.search();
        },
        search() {
            clearTimeout(this.searchTimer);
            // From now on an answer to what was typed before is stale.
            const seq = ++this.searchSeq;
            const term = this.term;
            if (term === '') {
                this.searching = false;
                this.results = [];
                this.resultsTerm = null;
                this.close();
                return;
            }
            this.searching = true;
            this.searchTimer = setTimeout(() => this.load(term, seq), SEARCH_DEBOUNCE_MS);
        },
        load(term, seq) {
            client.get(apiUrl('user', { purpose: 'picker', q: term, pageSize: MAX_SUGGESTIONS })).then((response) => {
                if (seq !== this.searchSeq) {
                    return;
                }
                this.results = response.results || [];
                this.resultsTerm = term;
                this.results.forEach((user) => this.known.set(idOf(user.id), user));
                // Newer suggestions while open: the active one of the older is gone.
                this.activeIndex = this.results.length ? 0 : -1;
            }).catch((response) => {
                if (seq !== this.searchSeq) {
                    return;
                }
                log.error(response);
                this.results = [];
                this.resultsTerm = term;
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
        choose(user) {
            clearTimeout(this.searchTimer);
            this.searchSeq++;
            this.searching = false;
            this.known.set(idOf(user.id), user);
            this.user = user;
            this.query = '';
            this.results = [];
            this.resultsTerm = null;
            this.close();
            this.pendingFocus = idOf(user.id) !== this.value;
            this.$emit('update:modelValue', idOf(user.id));
        },
        remove() {
            // The input is back once the owner applied the empty value.
            this.pendingFocus = true;
            this.$emit('update:modelValue', '');
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
                            this.search();
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
