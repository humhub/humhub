<template>
    <div id="panel-activities" class="panel panel-default panel-activities">
        <!-- eslint-disable-next-line vue/no-v-html -- server-rendered PanelMenu, see docblock -->
        <div v-if="panelMenuHtml" v-additions v-html="panelMenuHtml"></div>

        <!-- eslint-disable-next-line vue/no-v-html -- localized heading with markup, see docblock -->
        <div class="panel-heading" v-html="headingLabel"></div>

        <div class="panel-body p-0 pb-1 collapse show">
            <div id="activity-box-content" ref="list" class="hh-list activities" v-additions @scroll="onScroll">
                <hr class="m-0">

                <p v-if="!entries.length && !loading" class="p-3 m-0">{{ emptyLabel }}</p>

                <ActivityEntry
                    v-for="entry in entries"
                    :key="entry.key"
                    :activity="entry"
                    :show-space="!containerGuid"
                />

                <div v-if="loading" class="text-center p-2">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span class="visually-hidden" role="status">{{ loadingLabel }}</span>
                </div>

                <div v-if="hasMore" ref="sentinel" class="stream-end"></div>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * The "Latest activities" box — the whole panel, not just its list, replacing
 * `activity\widgets\views\activity-box.php` and `humhub.activity.js`.
 *
 * The widget rendering this island hands over the first page (so the first paint costs no
 * request), the container it is scoped to and the rendered `PanelMenu`; everything after that
 * is the island's: paging, the empty state and, in a container, hiding the redundant space
 * badge.
 *
 * ## Two pieces of server markup
 *
 * `panelMenuHtml` is the platform's `PanelMenu` widget, whose entries modules contribute
 * server-side — it stays a server concern and is mounted as-is, with `v-additions` so its own
 * legacy enhancers (the `ui.panel.PanelMenu` widget behind `[data-ui-init]`, dropdown,
 * tooltips) boot exactly as they do anywhere else. The heading is localized markup
 * (`<strong>Latest</strong> activities`), the same string the panel always used.
 *
 * That menu collapses the panel body, and it finds the body through the `collapse` class
 * below: `ui.panel.PanelMenu` looks for `.collapse` inside the panel FIRST and only walks
 * siblings of its own `<ul>` if there is none — and that walk cannot work here, because the
 * `<ul>` is mounted inside the element carrying `v-html` rather than as a direct sibling of
 * the heading. The class comes with `show`, so the body is visible before the widget boots;
 * the widget replaces that with the state it remembers.
 *
 * ## Paging
 *
 * A sentinel at the end of the list (`.stream-end`, the element the legacy widget also
 * observed) drives loading through an `IntersectionObserver` rooted in the scrolling list
 * itself — `.activities` is `max-height: 400px; overflow: auto`, so the box scrolls natively.
 * The custom scrollbar (`niceScroll`) the legacy widget installed on it is gone with the
 * rewrite; nothing but its cosmetics depended on it.
 *
 * The observer is re-armed after every page, which makes it fire again while the sentinel is
 * still in view — otherwise a page too short to push the sentinel out of the box would stall
 * the list until the next manual scroll.
 *
 * The cursor is whatever the previous page returned and is passed back untouched; an entry's
 * id is never a cursor (see `ActivityWindowService`).
 *
 * ## Live updates
 *
 * A `NewActivity` live event says no more than "a container you follow has a new activity", so
 * the box asks the API for the current head of its list (debounced, because activities arrive
 * in bursts) and reconciles it by the entries' opaque `key`:
 *
 * - an entry the list already shows under the same key is REPLACED where it stands: same
 *   position, fresh sentence and count. Nothing jumps under the reader.
 * - an entry with a key the list does not know WAITS in `pendingHead` and is only inserted
 *   while the list is scrolled to the top, which is the behaviour asked for: the box stays
 *   still while it is being read and catches up the moment the reader returns to the top.
 *
 * An activity joining a group counts as the second case, not the first: the server re-keys a
 * group to whichever activity formed it, so a grown entry arrives under a new key and belongs
 * at the top. Its previous version is still listed, which is why flushing REPLACES the head
 * rather than prepending: everything above the last entry the page and the list have in common
 * is dropped in favour of the page, and only what lies below it is kept.
 *
 * @since 1.20
 */
import { events, i18n, log } from '@humhub/vue';
import ActivityEntry from './components/ActivityEntry.vue';
import { fetchActivities } from './components/activityApi.js';

const LIVE_EVENT = 'humhub:modules:activity:live:NewActivity';

// Activities arrive in bursts (one content change can dispatch several), and every one of them
// would otherwise cost a request.
const LIVE_DEBOUNCE_MS = 1000;

export default {
    components: { ActivityEntry },
    i18nCategories: ['ActivityModule.base', 'base'],
    props: {
        // First page as `ActivityWindowService::window()` returns it: {results, nextCursor}.
        initial: { type: Object, default: () => ({ results: [], nextCursor: null }) },
        // Guid of the container the box is scoped to, empty on the dashboard.
        containerGuid: { type: String, default: '' },
        // Entries requested per page after the first.
        pageSize: { type: Number, default: 10 },
        // Rendered `PanelMenu` widget.
        panelMenuHtml: { type: String, default: '' },
    },
    data() {
        return {
            entries: Array.isArray(this.initial.results) ? [...this.initial.results] : [],
            cursor: this.initial.nextCursor ?? null,
            loading: false,
            observer: null,
            // Head page waiting for the list to be scrolled to the top, `null` when there is
            // nothing new to show.
            pendingHead: null,
            liveTimer: null,
        };
    },
    computed: {
        hasMore() {
            return this.cursor !== null;
        },
        headingLabel() {
            return i18n.t('ActivityModule.base', '<strong>Latest</strong> activities');
        },
        emptyLabel() {
            return i18n.t('ActivityModule.base', 'There are no activities yet.');
        },
        loadingLabel() {
            return i18n.t('base', 'Loading...');
        },
    },
    mounted() {
        this.armObserver();
        events.on(LIVE_EVENT, this.onLiveActivity);
    },
    beforeUnmount() {
        this.disconnectObserver();
        events.off(LIVE_EVENT, this.onLiveActivity);
        this.clearLiveTimer();
    },
    methods: {
        /**
         * (Re)observes the sentinel. Re-arming rather than observing once is deliberate: the
         * callback then runs against the CURRENT intersection state, so a short page that
         * leaves the sentinel in view keeps the list loading instead of stalling.
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
                    if (entries.some((entry) => entry.isIntersecting)) {
                        this.loadMore();
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
        loadMore() {
            if (this.loading || !this.cursor) {
                return;
            }

            this.loading = true;

            fetchActivities({
                cursor: this.cursor,
                limit: this.pageSize,
                containerGuid: this.containerGuid || null,
            }).then(({ results, nextCursor }) => {
                this.entries.push(...results);
                this.cursor = nextCursor;
            }).catch((error) => {
                log.error(error, true);
            }).finally(() => {
                this.loading = false;
                this.armObserver();
            });
        },
        /**
         * A live event only tells us that something happened - what exactly is the server's
         * answer, so a burst of events costs one debounced request, not one each.
         */
        onLiveActivity(event, liveEvents) {
            const concerns = (liveEvents || []).some((liveEvent) => {
                const guid = (liveEvent.data || {}).containerGuid || null;

                // A container-scoped box ignores everything happening elsewhere; the dashboard
                // box takes what the live system routed to it.
                return !this.containerGuid || guid === this.containerGuid;
            });

            if (!concerns) {
                return;
            }

            this.clearLiveTimer();
            this.liveTimer = setTimeout(this.refreshHead, LIVE_DEBOUNCE_MS);
        },
        clearLiveTimer() {
            if (this.liveTimer) {
                clearTimeout(this.liveTimer);
                this.liveTimer = null;
            }
        },
        /**
         * Fetches the current head of the list and reconciles it: entries already listed are
         * updated where they are, anything new waits for the list to be at the top.
         */
        refreshHead() {
            this.liveTimer = null;

            return fetchActivities({
                limit: this.pageSize,
                containerGuid: this.containerGuid || null,
            }).then(({ results }) => {
                const listed = new Set(this.entries.map((entry) => entry.key));

                this.entries = this.entries.map(
                    (entry) => results.find((fresh) => fresh.key === entry.key) || entry,
                );

                if (results.some((fresh) => !listed.has(fresh.key))) {
                    this.pendingHead = results;

                    if (this.isAtTop()) {
                        this.flushPending();
                    }
                }
            }).catch((error) => {
                log.error(error, true);
            });
        },
        onScroll() {
            if (this.pendingHead && this.isAtTop()) {
                this.flushPending();
            }
        },
        isAtTop() {
            return !this.$refs.list || this.$refs.list.scrollTop === 0;
        },
        /**
         * Puts the waiting head page in front of the entries below it. Everything up to and
         * including the last entry the page and the list have in common is REPLACED by the
         * page: an activity that joined another group leaves an entry behind which no longer
         * exists server-side, and dropping it needs a page of truth rather than a diff.
         */
        flushPending() {
            const head = this.pendingHead;
            this.pendingHead = null;

            if (!head) {
                return;
            }

            const keys = new Set(head.map((entry) => entry.key));
            let lastCommon = -1;

            this.entries.forEach((entry, index) => {
                if (keys.has(entry.key)) {
                    lastCommon = index;
                }
            });

            const tail = this.entries
                .slice(lastCommon + 1)
                .filter((entry) => !keys.has(entry.key));

            this.entries = [...head, ...tail];
        },
    },
};
</script>
