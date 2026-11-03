<template>
    <div class="content-activity">
        <p v-if="!entries.length && !loading" class="text-muted m-0">{{ emptyLabel }}</p>

        <div v-if="entries.length" class="hh-list content-activity-list" v-additions>
            <ActivityEntry
                v-for="entry in entries"
                :key="entry.key"
                :activity="entry"
                :show-space="false"
            />
        </div>

        <div v-if="loading" class="text-center p-2">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <span class="visually-hidden" role="status">{{ loadingLabel }}</span>
        </div>

        <div v-else-if="nextCursor" class="text-center p-2">
            <button type="button" class="btn btn-light btn-sm" @click="load">{{ moreLabel }}</button>
        </div>
    </div>
</template>

<script>
/**
 * The history of one content: the activities about it (created, commented, liked, …), newest
 * first and the viewer's own included — for a page that shows a single content on its own, such
 * as a file's details pane.
 *
 * Unlike the `ActivityBox` it has no panel and loads its first page itself, on mount: it is
 * meant to be opened client-side, after the page's first paint. Further pages come with "Show
 * more" rather than on scroll, as it sits in a container that scrolls as a whole.
 *
 * Endpoint: `GET api/v2/activity?contentId=` (see `ActivityController`).
 *
 * @since 1.20
 */
import { i18n, log } from '@humhub/vue';
import ActivityEntry from './components/ActivityEntry.vue';
import { fetchActivities } from './components/activityApi.js';

export default {
    i18nCategories: ['ActivityModule.base', 'base'],
    components: { ActivityEntry },
    props: {
        /** The content whose history this is. */
        contentId: { type: Number, required: true },
        /** Entries requested per page. */
        pageSize: { type: Number, default: 10 },
    },
    data() {
        return {
            entries: [],
            nextCursor: null,
            loading: false,
        };
    },
    computed: {
        emptyLabel: () => i18n.t('ActivityModule.base', 'There are no activities yet.'),
        loadingLabel: () => i18n.t('base', 'Loading...'),
        moreLabel: () => i18n.t('base', 'Show more'),
    },
    watch: {
        contentId() {
            this.entries = [];
            this.nextCursor = null;
            this.load();
        },
    },
    mounted() {
        this.load();
    },
    methods: {
        load() {
            const contentId = this.contentId;
            this.loading = true;

            return fetchActivities({ contentId, cursor: this.nextCursor, limit: this.pageSize })
                .then((page) => {
                    // Another content was opened meanwhile: its own load is on its way.
                    if (contentId !== this.contentId) {
                        return;
                    }
                    this.entries = this.entries.concat(page.results);
                    this.nextCursor = page.nextCursor;
                    this.loading = false;
                })
                .catch((e) => {
                    if (contentId === this.contentId) {
                        this.loading = false;
                    }
                    log.error(e, true);
                });
        },
    },
};
</script>
