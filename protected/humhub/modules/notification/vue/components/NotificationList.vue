<template>
    <div ref="list" class="hh-list" v-additions @scroll="onScroll">
        <NotificationEntry
            v-for="entry in items"
            :key="entry.id"
            :notification="entry"
        />
        <div v-if="!items.length && !loading" class="info">{{ emptyLabel }}</div>

        <div v-if="loading" class="text-center p-2">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <span class="visually-hidden" role="status">{{ loadingLabel }}</span>
        </div>

        <div v-if="showMoreButton && hasMore && !loading" class="text-center p-2">
            <button type="button" class="btn btn-light btn-sm" @click="loadMore">{{ showMoreLabel }}</button>
        </div>
    </div>
</template>

<script>
/**
 * The notification list both notification islands are built on — it owns the data (fetching,
 * cursor paging, live prepends), the islands own their chrome.
 *
 * ## Paging
 *
 * Cursor-based, see `notification\controllers\api\NotificationController`. Two ways to ask for
 * the next page, because the two consumers differ: the dropdown pages on scroll (its own list
 * element scrolls, like the legacy one did), the overview page shows a button
 * (`showMoreButton`). The legacy overview used numbered pages; a cursor cannot express those,
 * and mixing page numbers with a live-updating list reintroduces exactly the skipping the
 * cursor exists to avoid — a documented, deliberate change.
 *
 * ## API
 *
 * - `reload()` — fetches the first page again (a filter changed, the dropdown was opened).
 * - `loadMore()` — appends the next page; a no-op while loading or exhausted.
 * - `prepend(entry)` — puts a live-arrived notification on top, deduping by id and by
 *   `groupKey` (a grouped notification's later members must replace the entry, not stack under
 *   it — the same rule `humhub.notification.js` applied to its live events).
 * - `has(entry)` — whether an id/groupKey is already listed, so an island can decide whether a
 *   live event is new before it fetches anything.
 *
 * Emits `loaded` with the response (`{results, unseenCount, nextCursor}`) after every fetch, so
 * an island can keep its badge in sync without fetching itself.
 *
 * ## The root element IS the scroll container
 *
 * `.hh-list` sits on the component's own root rather than on an inner wrapper, so a class a
 * consumer passes lands on the same element - which matters for the menu: it passes
 * `dropdown-item`, and the legacy markup had both classes on ONE element
 * (`<div class="dropdown-item hh-list">`). With the two split across a wrapper and an inner
 * div, Bootstrap's `.dropdown-item` `:active`/`:focus` background covered the whole list on
 * every click on an entry.
 *
 * `v-additions` boots the platform's own legacy enhancers on the rendered entries - the
 * `timeago` addition each entry's `<time data-ui-addition="timeago">` needs (see
 * docs/develop/ui-js-vuejs-interop.md), and anything an entry's sentence html brings along.
 *
 * @since 1.20
 */
import { i18n, log } from '@humhub/vue';
import NotificationEntry from './NotificationEntry.vue';
import { fetchNotifications } from './notificationApi.js';

export default {
    // Internal building block of this module's islands (a `vue/components/` file is not
    // auto-registered platform-wide - see docs/develop/ui-js-vuejs-components.md), so it is
    // imported rather than resolved by tag.
    components: { NotificationEntry },
    props: {
        // Optional first page from the server (inlined by the widget), so the first paint of
        // the overview page costs no request.
        initial: { type: Object, default: null },
        // Filters, forwarded to the endpoint (see notificationApi.js).
        categories: { type: Array, default: null },
        seen: { type: String, default: null },
        pageSize: { type: Number, default: 6 },
        showMoreButton: { type: Boolean, default: false },
        // Scroll-paging (the dropdown): distance from the bottom, in pixels, that triggers the
        // next page.
        scrollThreshold: { type: Number, default: 20 },
        emptyText: { type: String, default: null },
    },
    emits: ['loaded'],
    data() {
        return {
            items: this.initial ? [...(this.initial.results || [])] : [],
            nextCursor: this.initial ? this.initial.nextCursor || null : null,
            loading: false,
            // Nothing fetched yet AND nothing handed over: the first `reload()` is the initial
            // load rather than a refresh.
            loaded: !!this.initial,
        };
    },
    computed: {
        hasMore() {
            return this.nextCursor !== null;
        },
        emptyLabel() {
            return this.emptyText || i18n.t('NotificationModule.base', 'There are no notifications yet.');
        },
        showMoreLabel() {
            return i18n.t('NotificationModule.base', 'Show all notifications');
        },
        loadingLabel() {
            return i18n.t('base', 'Loading...');
        },
    },
    methods: {
        /** Fetches the first page, replacing what is listed. */
        reload() {
            return this.fetch(null, true);
        },
        /** Appends the next page. */
        loadMore() {
            if (this.loading || !this.hasMore) {
                return Promise.resolve();
            }

            return this.fetch(this.nextCursor, false);
        },
        fetch(cursor, replace) {
            this.loading = true;

            return fetchNotifications({
                cursor,
                limit: this.pageSize,
                categories: this.categories,
                seen: this.seen,
            }).then((response) => {
                this.items = replace ? response.results : [...this.items, ...response.results];
                this.nextCursor = response.nextCursor;
                this.loaded = true;
                this.$emit('loaded', response);
            }).catch((response) => {
                log.error(response, true);
            }).finally(() => {
                this.loading = false;
            });
        },
        /**
         * Inserts a live-arrived notification at the top. Already-listed entries are replaced
         * in place (a grouped notification whose group grew keeps its position rather than
         * appearing twice).
         */
        prepend(entry) {
            const index = this.indexOf(entry);

            if (index === -1) {
                this.items = [entry, ...this.items];
                return;
            }

            const items = [...this.items];
            items.splice(index, 1);
            this.items = [entry, ...items];
        },
        /** @returns {boolean} whether this id or group is already listed. */
        has(entry) {
            return this.indexOf(entry) !== -1;
        },
        indexOf(entry) {
            return this.items.findIndex((item) => item.id === entry.id
                || (!!entry.groupKey && item.groupKey === entry.groupKey));
        },
        onScroll() {
            const element = this.$refs.list;

            if (!element || this.loading || !this.hasMore) {
                return;
            }

            if (element.scrollTop + element.clientHeight >= element.scrollHeight - this.scrollThreshold) {
                this.loadMore();
            }
        },
    },
};
</script>
