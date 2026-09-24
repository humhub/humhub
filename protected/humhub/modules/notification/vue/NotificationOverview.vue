<template>
    <div class="row">
        <div class="col-lg-9 layout-content-container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <!-- eslint-disable-next-line vue/no-v-html -- translated heading with markup, as server-side -->
                    <span v-html="headingLabel"></span>
                    <div class="float-end">
                        <button
                            v-if="unseenCount > 0"
                            type="button"
                            id="notification_overview_markseen"
                            class="btn-light btn btn-icon-only btn-sm"
                            :aria-label="markSeenLabel"
                            :title="markSeenLabel"
                            @click="markAsSeen"
                        ><span v-html="icons.check"></span></button>
                        <a
                            class="btn-light btn btn-icon-only btn-sm"
                            :href="settingsUrl"
                            :aria-label="settingsLabel"
                            :title="settingsLabel"
                        ><span v-html="icons.cog"></span></a>
                    </div>
                </div>
                <div class="panel-body">
                    <NotificationList
                        ref="list"
                        id="notification_overview_list"
                        :initial="initial"
                        :page-size="pageSize"
                        :categories="requestCategories"
                        :seen="seen || null"
                        :show-more-button="true"
                        :empty-text="emptyLabel"
                        @loaded="onLoaded"
                    />
                </div>
            </div>
        </div>
        <aside class="col-lg-3 layout-sidebar-container" :aria-label="sidebarLabel">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>{{ filterLabel }}</strong>
                    <hr style="margin-bottom:0">
                </div>
                <div class="panel-body">
                    <NotificationFilter
                        :categories="categories"
                        :selected="selectedCategories"
                        :seen="seen"
                        :icons="icons"
                        @change="onFilterChange"
                    />
                </div>
            </div>
        </aside>
    </div>
</template>

<script>
/**
 * The notification overview page — mounted by `notification\controllers\OverviewController`'s
 * view as `<notification-overview>`, and the second consumer of `NotificationList` (the
 * top-menu dropdown is the other).
 *
 * The island covers both columns of the page, because the filter in the sidebar and the list in
 * the main panel are one piece of state: a filter change is a new query. The panel/column
 * markup mirrors the former `views/overview/index.php` so the page looks unchanged, and the
 * legacy element ids (`#notification_overview_list`, `#notification_overview_markseen`) are
 * kept for theme CSS.
 *
 * **Deliberate change:** the list pages with a "show more" button instead of numbered pages.
 * The endpoint pages by cursor (see its own docblock on why), and numbered pages over a list
 * that reorders as notifications arrive is exactly the skipping the cursor exists to prevent.
 *
 * @since 1.20
 */
import { events, i18n, log } from '@humhub/vue';
import NotificationFilter from './components/NotificationFilter.vue';
import NotificationList from './components/NotificationList.vue';
import { markAllAsSeen } from './components/notificationApi.js';

// The count channel the notification menu owns (see `NotificationMenu.vue`): both islands live
// on this page, so marking everything as seen here has to reach the badge up there - and the
// other way round.
const SET_COUNT_EVENT = 'humhub:notification:setCount';

export default {
    components: { NotificationFilter, NotificationList },
    i18nCategories: ['NotificationModule.base', 'UserModule.base', 'base'],
    props: {
        // First page, inlined by the controller: {results, unseenCount, nextCursor}.
        initial: { type: Object, default: null },
        // [{id, title}] of every category the caller can filter by (localized).
        categories: { type: Array, default: () => [] },
        // Server-rendered icon markup: {check, cog, all, unseen, seen}.
        icons: { type: Object, default: () => ({}) },
        settingsUrl: { type: String, required: true },
        pageSize: { type: Number, default: 20 },
    },
    data() {
        return {
            // Everything selected initially, like the server-rendered filter's own default.
            selectedCategories: this.categories.map((category) => category.id),
            seen: '',
            unseenCount: this.initial ? Number(this.initial.unseenCount || 0) : 0,
        };
    },
    computed: {
        // No filter at all while every category is selected: it would only narrow the list to
        // classes the modules currently register (see the endpoint's own docblock).
        requestCategories() {
            return this.selectedCategories.length === this.categories.length ? null : this.selectedCategories;
        },
        headingLabel() {
            return i18n.t('NotificationModule.base', '<strong>Notification</strong> Overview');
        },
        filterLabel() {
            return i18n.t('NotificationModule.base', 'Filter');
        },
        markSeenLabel() {
            return i18n.t('NotificationModule.base', 'Mark all as seen');
        },
        settingsLabel() {
            return i18n.t('NotificationModule.base', 'Notification Settings');
        },
        emptyLabel() {
            return i18n.t('NotificationModule.base', 'No notifications found!');
        },
        sidebarLabel() {
            return i18n.t('base', 'Sidebar');
        },
    },
    mounted() {
        events.on(SET_COUNT_EVENT, this.onSetCount);
    },
    beforeUnmount() {
        events.off(SET_COUNT_EVENT, this.onSetCount);
    },
    methods: {
        /** The menu marked everything as seen - this list's unread markers are stale. */
        onSetCount(event, count) {
            if (Number(count) === 0 && this.unseenCount !== 0) {
                this.unseenCount = 0;
                this.$refs.list.reload();
            }
        },
        onFilterChange({ categories, seen }) {
            this.selectedCategories = categories;
            this.seen = seen;
            // The props reach the list through the same reactive update, so the refetch has to
            // wait for it - otherwise it would send the previous filter.
            this.$nextTick(() => this.$refs.list.reload());
        },
        onLoaded(response) {
            this.unseenCount = Number(response.unseenCount) || 0;
        },
        markAsSeen() {
            return markAllAsSeen().then(() => {
                this.unseenCount = 0;
                // Tells the notification menu's badge and the document title, which are owned
                // by the other island on this page.
                events.trigger(SET_COUNT_EVENT, [0]);
                return this.$refs.list.reload();
            }).catch((response) => {
                log.error(response, true);
            });
        },
    },
};
</script>
