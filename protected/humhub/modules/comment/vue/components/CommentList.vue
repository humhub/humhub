<template>
    <div class="comment" :class="{ 'guest-mode': guest }" :id="commentsAreaId">
        <div v-if="remainingPrev > 0 && items.length > 0" class="showMore">
            <a href="#" :class="{ disabled: busyPrev }" @click.prevent="loadPrev">{{ prevLabel }}</a>
        </div>

        <template v-for="comment in items" :key="comment.id">
            <hr class="comment-separator">
            <CommentEntry
                :comment="comment"
                :can-comment="canComment"
                :form-shell-html="formShellHtml"
                :page-size="pageSize"
                :highlighted="anchorCommentId !== null && comment.id === anchorCommentId"
            />
        </template>

        <div v-if="remainingNext > 0 && items.length > 0" class="showMore">
            <hr class="comment-separator">
            <a href="#" :class="{ disabled: busyNext }" @click.prevent="loadNext">{{ nextLabel }}</a>
        </div>
    </div>
</template>

<script>
/**
 * Renders the root-level comment window and both "show more" directions
 * (real remaining counts from the list endpoint, see
 * CommentJsonService::serializeWindow()). Owns its own working copy of the
 * window so show-more prepend/append never mutates the parent's props array
 * directly - total count is unaffected by paging, so nothing needs to be
 * emitted upward for it.
 */
import { client, getConfig, i18n, log, url } from '@humhub/vue';
import CommentEntry from './CommentEntry.vue';
import { getId } from './commentIdHelper.js';

export default {
    components: { CommentEntry },
    props: {
        contentId: { type: Number, required: true },
        comments: { type: Array, required: true },
        prevCount: { type: Number, required: true },
        nextCount: { type: Number, required: true },
        pageSize: { type: Number, default: 10 },
        canComment: { type: Boolean, default: false },
        formShellHtml: { type: String, default: null },
        anchorCommentId: { type: Number, default: null },
    },
    data() {
        return {
            items: [...this.comments],
            remainingPrev: this.prevCount,
            remainingNext: this.nextCount,
            busyPrev: false,
            busyNext: false,
        };
    },
    computed: {
        // Legacy markup: comments.php renders `.comment.guest-mode` /
        // `#comments_area_<id>` around this exact list - reproduced so
        // existing theme CSS (`&.guest-mode` in _comment.scss) keeps
        // applying and P2-6's PHP-rendered id contract still resolves.
        guest() {
            return getConfig('user').isGuest === true;
        },
        commentsAreaId() {
            return 'comments_area_' + getId(this.contentId, null);
        },
        prevLabel() {
            return i18n.t('CommentModule.base', 'Show previous {count} comments', { count: this.remainingPrev });
        },
        nextLabel() {
            return i18n.t('CommentModule.base', 'Show next {count} comments', { count: this.remainingNext });
        },
    },
    watch: {
        // Re-syncs if the parent ever hydrates a genuinely new window (e.g. a
        // future re-fetch) - not exercised by show-more itself, which only
        // ever touches the local copies above.
        comments(value) {
            this.items = [...value];
        },
        prevCount(value) {
            this.remainingPrev = value;
        },
        nextCount(value) {
            this.remainingNext = value;
        },
    },
    methods: {
        loadPrev() {
            if (this.busyPrev || this.items.length === 0) {
                return;
            }
            this.busyPrev = true;
            const cursor = this.items[0].id;

            client.get(url('/comment/comment/list', {
                contentId: this.contentId,
                commentId: cursor,
                direction: 'previous',
                pageSize: this.pageSize,
            })).then((response) => {
                this.items = [...response.comments, ...this.items];
                this.remainingPrev = response.prevCount;
            }).catch((e) => {
                log.error(e, true);
            }).finally(() => {
                this.busyPrev = false;
            });
        },
        loadNext() {
            if (this.busyNext || this.items.length === 0) {
                return;
            }
            this.busyNext = true;
            const cursor = this.items[this.items.length - 1].id;

            client.get(url('/comment/comment/list', {
                contentId: this.contentId,
                commentId: cursor,
                direction: 'next',
                pageSize: this.pageSize,
            })).then((response) => {
                this.items = [...this.items, ...response.comments];
                this.remainingNext = response.nextCount;
            }).catch((e) => {
                log.error(e, true);
            }).finally(() => {
                this.busyNext = false;
            });
        },
    },
};
</script>
