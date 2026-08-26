<template>
    <div id="panel-activities" class="panel panel-default panel-activities">
        <!-- eslint-disable-next-line vue/no-v-html -- server-rendered PanelMenu, see docblock -->
        <div v-if="panelMenuHtml" v-additions v-html="panelMenuHtml"></div>

        <!-- eslint-disable-next-line vue/no-v-html -- localized heading with markup, see docblock -->
        <div class="panel-heading" v-html="headingLabel"></div>

        <div class="panel-body p-0 pb-1 collapse show">
            <div id="activity-box-content" ref="list" class="hh-list activities" v-additions>
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
 * @since 1.20
 */
import { i18n, log } from '@humhub/vue';
import ActivityEntry from './components/ActivityEntry.vue';
import { fetchActivities } from './components/activityApi.js';

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
    },
    beforeUnmount() {
        this.disconnectObserver();
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
    },
};
</script>
