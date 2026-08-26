<template>
    <div ref="root">
        <form class="dropdown-header dropdown-controls" @submit.prevent>
            <div :class="{ 'input-group': !!directoryUrl }">
                <input
                    id="space-menu-search"
                    ref="search"
                    v-model="keyword"
                    type="text"
                    class="form-control"
                    autocomplete="off"
                    :placeholder="searchLabel"
                    :title="searchTitle"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.enter.prevent="open"
                    @keydown.esc.prevent="reset"
                >

                <span v-if="directoryUrl" id="space-directory-link" class="input-group-text">
                    <!-- eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock -->
                    <a :href="directoryUrl" v-html="directoryIconHtml"></a>
                </span>

                <!-- eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock -->
                <div
                    v-show="keyword"
                    id="space-search-reset"
                    class="search-reset"
                    @click="reset"
                    v-html="resetIconHtml"
                ></div>
            </div>
        </form>

        <hr class="dropdown-divider">

        <div id="space-menu-spaces" ref="list" class="hh-list" v-additions>
            <SpaceChooserItem
                v-for="(space, index) in spaces"
                :key="space.guid"
                :space="space"
                :relation="relationOf(space)"
                :new-items="newItemsOf(space)"
                :selected="index === selected"
            />

            <div v-if="loading" class="text-center p-2">
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span class="visually-hidden" role="status">{{ loadingLabel }}</span>
            </div>

            <div v-if="tooShort" class="dropdown-item disabled">{{ minKeywordLabel }}</div>

            <div
                v-else-if="!loading && !spaces.length"
                class="dropdown-item disabled"
            >{{ emptyLabel }}</div>

            <div v-if="hasMore && !loading" ref="sentinel" class="stream-end"></div>
        </div>

        <div v-if="createSpaceUrl" class="dropdown-footer">
            <a
                href="#"
                class="btn btn-accent col-lg-12"
                data-action-click="ui.modal.load"
                :data-action-url="createSpaceUrl"
            >{{ createLabel }}</a>
        </div>
    </div>
</template>

<script>
/**
 * The space menu of the top navigation — search, the caller's spaces, and the directory search
 * that the same field performs. Replaces `space\widgets\views\spaceChooser.php` and
 * `humhub.space.chooser.js`.
 *
 * The island is mounted ON the dropdown menu (`#space-menu-dropdown.dropdown-menu`, see
 * `space\widgets\Chooser`), not on the `<li>` around it: the topbar styles the menu item with
 * child selectors (`.nav > li.nav-item > a.nav-link`), which an element in between would break.
 * The toggle beside it is its own small island, `SpaceChooserToggle`.
 *
 * ## One field, two lists
 *
 * Empty field: the caller's own spaces (`GET /api/v2/space?scope=mine`), memberships first.
 * Typing: the same endpoint without a scope, so the search reaches every space the caller may
 * see — the directory search the legacy chooser ran against a second route. Which results are
 * the caller's own, and how much is new in them, comes from `GET /api/v2/space/states` for
 * exactly the spaces displayed; the list endpoint itself is caller-neutral (see its docblock).
 *
 * ## Loaded when it is opened
 *
 * Nothing is fetched until the menu opens the first time — a page whose menu is never opened
 * costs no space queries at all, which is what the legacy `lazyLoad` did. The result stays in
 * memory afterwards, including across pjax navigations, because the top menu is not re-rendered
 * by them.
 *
 * @since 1.20
 */
import { events, i18n, log } from '@humhub/vue';
import SpaceChooserItem from './components/SpaceChooserItem.vue';
import { fetchSpaces, fetchStates } from './components/spaceApi.js';

const LIVE_NEW_CONTENT = 'humhub:modules:content:live:NewContent';

// Everything that changes which spaces are the caller's own, or how they are marked.
const RELATION_EVENTS = [
    'humhub:space:followed',
    'humhub:space:unfollowed',
    'humhub:space:archived',
    'humhub:space:unarchived',
];
const SEARCH_DEBOUNCE_MS = 300;
const MIN_KEYWORD_LENGTH = 2;

export default {
    components: { SpaceChooserItem },
    i18nCategories: ['SpaceModule.chooser', 'base'],
    props: {
        // Entries per page.
        pageSize: { type: Number, default: 25 },
        // Where "Create Space" opens, empty when the caller may not create one.
        createSpaceUrl: { type: String, default: '' },
        // Where the directory link points, empty when the caller may not access it.
        directoryUrl: { type: String, default: '' },
        // Rendered icons - the icon provider is pluggable, so a client cannot build them.
        directoryIconHtml: { type: String, default: '' },
        resetIconHtml: { type: String, default: '' },
    },
    data() {
        return {
            keyword: '',
            spaces: [],
            states: {},
            page: 1,
            pages: 0,
            loading: false,
            loaded: false,
            selected: -1,
            searchTimer: null,
            observer: null,
        };
    },
    computed: {
        hasMore() {
            return this.page < this.pages;
        },
        searchLabel() {
            return i18n.t('SpaceModule.chooser', 'Search');
        },
        searchTitle() {
            return i18n.t('SpaceModule.chooser', 'Search for spaces');
        },
        createLabel() {
            return i18n.t('SpaceModule.chooser', 'Create Space');
        },
        loadingLabel() {
            return i18n.t('base', 'Loading...');
        },
        // The strings the legacy chooser used, so no translation is lost with the rewrite.
        emptyLabel() {
            return this.searching
                ? i18n.t('SpaceModule.chooser', 'No Spaces found.')
                : i18n.t('SpaceModule.chooser', 'You are not a member of or following any Spaces.');
        },
        minKeywordLabel() {
            return i18n.t(
                'SpaceModule.chooser',
                'Please enter at least {count} characters to search Spaces.',
                { count: MIN_KEYWORD_LENGTH },
            );
        },
        /**
         * A single character is not searched for: the legacy chooser asked for two before it
         * queried, and the hint that says so is part of the menu.
         */
        searching() {
            return this.keyword.trim().length >= MIN_KEYWORD_LENGTH;
        },
        tooShort() {
            const length = this.keyword.trim().length;

            return length > 0 && length < MIN_KEYWORD_LENGTH;
        },
    },
    watch: {
        keyword() {
            this.selected = -1;
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.load(1), SEARCH_DEBOUNCE_MS);
        },
    },
    mounted() {
        this.dropdown = this.$refs.root.closest('.dropdown');

        if (this.dropdown) {
            this.dropdown.addEventListener('show.bs.dropdown', this.onShow);
        }

        // The button is server-rendered and Bootstrap opens the menu without the island, so a
        // visitor can have clicked before this script was parsed - `show.bs.dropdown` is gone
        // by then. A menu that is already open at mount is therefore loaded right away.
        if (this.menuElement() && this.menuElement().classList.contains('show')) {
            this.onShow();
        }

        events.on(LIVE_NEW_CONTENT, this.onNewContent);
        RELATION_EVENTS.forEach((name) => events.on(name, this.onRelationChanged));
    },
    beforeUnmount() {
        this.disconnectObserver();
        if (this.dropdown) {
            this.dropdown.removeEventListener('show.bs.dropdown', this.onShow);
        }

        events.off(LIVE_NEW_CONTENT, this.onNewContent);
        RELATION_EVENTS.forEach((name) => events.off(name, this.onRelationChanged));
        clearTimeout(this.searchTimer);
    },
    methods: {
        /**
         * The dropdown menu itself, which is the element the island is mounted on (see
         * `space\widgets\Chooser`): Bootstrap marks it `show` while it is open.
         */
        menuElement() {
            return this.$refs.root ? this.$refs.root.closest('.dropdown-menu') : null;
        },
        onShow() {
            if (!this.loaded) {
                this.load(1);
            }

            // The field is where the keyboard lands, as in the legacy menu. `preventScroll`,
            // because focusing scrolls the field into view - and opening a menu should not
            // move the page underneath it.
            this.$nextTick(() => this.$refs.search && this.$refs.search.focus({ preventScroll: true }));
        },
        /**
         * Loads a page: the caller's own spaces while the field is empty, every space they may
         * see once they type. A page beyond the first appends, so scrolling extends the list.
         */
        load(page) {
            this.loading = true;

            const keyword = this.searching ? this.keyword.trim() : '';

            fetchSpaces({
                q: keyword || null,
                scope: keyword ? null : 'mine',
                page,
                pageSize: this.pageSize,
            }).then((result) => {
                // A response for a keyword that has since changed is stale - the next timer
                // already asked for the current one.
                if (keyword !== this.keyword.trim()) {
                    return;
                }

                this.spaces = page > 1 ? [...this.spaces, ...result.results] : result.results;
                this.page = result.page;
                this.pages = result.pages;
                this.loaded = true;

                return this.loadStates(result.results);
            }).catch((error) => {
                log.error(error, true);
            }).finally(() => {
                this.loading = false;
                this.armObserver();
            });
        },
        /**
         * What the caller is to the spaces just loaded — membership, following, and what is new
         * in them. Asked for the spaces displayed, never for "all of mine".
         */
        loadStates(spaces) {
            const guids = spaces.map((space) => space.guid);

            if (!guids.length) {
                return Promise.resolve();
            }

            return fetchStates(guids).then((states) => {
                this.states = { ...this.states, ...states };
            });
        },
        /**
         * (Re)observes the sentinel at the end of the list, which is what asks for the next
         * page - the menu's list scrolls (`#space-menu-dropdown .hh-list` is `max-height:
         * 200px; overflow: auto`). Re-arming after every page makes the callback run against
         * the current state, so a page too short to fill the list keeps loading.
         */
        armObserver() {
            if (!window.IntersectionObserver) {
                return;
            }

            this.$nextTick(() => {
                this.disconnectObserver();

                const sentinel = this.$refs.sentinel;

                if (!sentinel) {
                    return;
                }

                this.observer = new IntersectionObserver((entries) => {
                    if (entries.some((entry) => entry.isIntersecting) && !this.loading) {
                        this.load(this.page + 1);
                    }
                }, { root: this.$refs.list, rootMargin: '1px' });

                this.observer.observe(sentinel);
            });
        },
        disconnectObserver() {
            if (this.observer) {
                this.observer.disconnect();
                this.observer = null;
            }
        },
        relationOf(space) {
            const state = this.states[space.guid];

            if (state && state.isMember) {
                return 'member';
            }
            if (state && state.isFollowing) {
                return 'following';
            }

            return space.archived ? 'archived' : 'none';
        },
        newItemsOf(space) {
            const state = this.states[space.guid];

            return state ? state.newItems : 0;
        },
        /**
         * Counts what arrives while the menu is open, the way the legacy chooser did: content
         * of the caller's own making, silent content and profile content do not count.
         */
        onNewContent(event, liveEvents) {
            (liveEvents || []).forEach((liveEvent) => {
                const data = liveEvent.data || {};

                if (data.uguid || data.silent || data.originator === this.currentUserGuid()) {
                    return;
                }

                const state = this.states[data.sguid];

                if (state && state.isMember) {
                    this.states = {
                        ...this.states,
                        [data.sguid]: { ...state, newItems: state.newItems + 1 },
                    };
                }
            });
        },
        /**
         * Following, unfollowing and archiving change what the list should show. Rather than
         * patching entries in place, the list is marked stale and re-read the next time the
         * menu opens — it is closed while any of this happens, and the server decides the
         * order anyway.
         */
        onRelationChanged() {
            this.loaded = false;

            if (this.dropdown && this.dropdown.classList.contains('show')) {
                this.load(1);
            }
        },
        currentUserGuid() {
            return (window.humhub && window.humhub.modules && window.humhub.modules.user
                && typeof window.humhub.modules.user.guid === 'function')
                ? window.humhub.modules.user.guid()
                : null;
        },
        move(offset) {
            if (!this.spaces.length) {
                return;
            }

            const next = this.selected + offset;
            this.selected = Math.max(0, Math.min(this.spaces.length - 1, next));

            this.$nextTick(() => {
                const item = this.$refs.list.querySelectorAll('[data-space-chooser-item]')[this.selected];

                if (item && item.scrollIntoView) {
                    item.scrollIntoView({ block: 'nearest' });
                }
            });
        },
        open() {
            const space = this.spaces[this.selected] || this.spaces[0];

            if (space) {
                window.location.href = space.url;
            }
        },
        reset() {
            this.keyword = '';
            this.selected = -1;
        },
    },
};
</script>
