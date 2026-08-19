<template>
    <div
        v-if="effective.blocked"
        :id="'comment_' + comment.id"
        class="d-flex comment-blocked-user"
    >
        <div class="flex-shrink-0 me-2"></div>
        <div class="flex-grow-1 overflow-hidden">
            {{ blockedLabel }}
            <a href="#" class="text-primary" @click.prevent="reveal">{{ showLabel }}</a>
        </div>
    </div>

    <div
        v-else
        :id="'comment_' + comment.id"
        class="single-comment d-flex p-2"
        :class="{ 'comment-current': highlighted }"
        v-additions
    >
        <CommentControls
            :permalink="effective.permalink"
            :can-edit="effective.canEdit"
            :can-delete="effective.canDelete"
            :can-admin-delete="effective.canAdminDelete"
            @edit="onEdit"
            @delete="onDelete"
            @admin-delete="onAdminDelete"
        />

        <div class="flex-shrink-0 comment-header-image">
            <a :href="effective.author.url">
                <img class="rounded" style="width: 25px; height: 25px" :src="effective.author.imageUrl" :alt="effective.author.displayName">
            </a>
        </div>

        <div class="flex-grow-1">
            <h4 class="comment-heading">
                <a :href="effective.author.url">{{ effective.author.displayName }}</a>
                <small>
                    &middot;
                    <time class="tt time timeago" data-ui-addition="timeago" :datetime="comment.createdAt" :title="absoluteTime">{{ absoluteTime }}</time>
                    <template v-if="comment.isEdited">
                        &middot; <i class="fa fa-clock-o text-body-secondary" aria-hidden="true"></i>
                    </template>
                </small>
            </h4>

            <!-- class comment_edit_content required since v1.2 -->
            <div class="content comment_edit_content" :id="'comment_editarea_' + comment.id">
                <div class="comment-message" data-ui-show-more :data-read-more-text="readMoreLabel">
                    <RichTextOutput :output="effective.messageOutput" />
                </div>
                <div v-if="effective.attachmentsHtml" v-html="effective.attachmentsHtml"></div>
            </div>

            <div class="wall-entry-controls">
                <LikeButton
                    v-if="effective.likes"
                    :record-id="effective.recordId"
                    :like-count="effective.likes.count"
                    :current-user-liked="effective.likes.liked"
                />
                <template v-if="showReplyToggle">
                    &middot;
                    <a href="#" @click.prevent="toggleReply">{{ replyLabel }}</a>
                </template>
            </div>

            <div v-if="comment.children" class="nested-comments-root">
                <div class="comment">
                    <template v-for="child in childItems" :key="child.id">
                        <hr class="comment-separator">
                        <CommentEntry
                            :comment="child"
                            :is-nested="true"
                            :can-comment="canComment"
                            :form-shell-html="formShellHtml"
                            :page-size="pageSize"
                        />
                    </template>

                    <div v-if="childHasMore" class="showMore">
                        <hr class="comment-separator">
                        <a href="#" :class="{ disabled: busyReplies }" @click.prevent="loadMoreReplies">{{ moreRepliesLabel }}</a>
                    </div>
                </div>

                <CommentForm
                    v-if="replyOpen && formShellHtml"
                    ref="replyForm"
                    :shell-html="formShellHtml"
                    :content-id="comment.contentId"
                    :parent-comment-id="comment.id"
                />
            </div>
        </div>
    </div>
</template>

<script>
/**
 * Renders one comment entry - root or one-level-nested reply, recursively
 * (though a reply's own `children` is always null server-side, so recursion
 * bottoms out after one level; see CommentJsonService::serialize()).
 *
 * Markup mirrors comment/widgets/views/comment.php and
 * commentBlockedUser.php so existing theme CSS applies unchanged. `name`
 * is set explicitly so <CommentEntry> can resolve itself recursively (Vue's
 * resolveComponent() self-reference check keys off the component's own
 * `name` option).
 */
import { client, i18n, log, url } from '@humhub/vue';
import RichTextOutput from './RichTextOutput.vue';
import CommentControls from './CommentControls.vue';
import CommentForm from './CommentForm.vue';

export default {
    name: 'CommentEntry',
    components: { RichTextOutput, CommentControls, CommentForm },
    props: {
        comment: { type: Object, required: true },
        canComment: { type: Boolean, default: false },
        formShellHtml: { type: String, default: null },
        pageSize: { type: Number, default: 10 },
        // A reply (one level deep) never gets its own reply toggle or further
        // nesting - the server enforces at most one level (see
        // CommentController::actionCreate()).
        isNested: { type: Boolean, default: false },
        // Permalink anchor target - a persistent CSS affordance (the legacy
        // `comment-current` class set via Comments widget's
        // `$highlightCommentId`), not the same thing as the *temporary*
        // flash `additions.highlight()` applies after an inline edit
        // (Comment.prototype.editSubmit in humhub.comment.js) - that flash
        // is an edit-flow concern, out of scope until P2-5 wires editing.
        highlighted: { type: Boolean, default: false },
    },
    data() {
        return {
            // Reveal-blocked result replaces the masked comment locally; the
            // array item CommentList/CommentEntry-parent holds stays
            // untouched (revealing doesn't change any count).
            revealed: null,
            replyOpen: false,
            childItems: this.comment.children ? [...this.comment.children.items] : [],
            childTotal: this.comment.children ? this.comment.children.total : 0,
            childRemainingNext: this.comment.children
                ? this.comment.children.total - this.comment.children.items.length
                : 0,
            // Initial value trusts the server's own preview flag verbatim
            // (CommentJsonService::serializeChildren()'s `total > count($items)`);
            // recomputed with the same formula after each load-more below,
            // rather than solely trusting `nextCount`, so a concurrent
            // create/delete between requests can't leave a stale gate.
            childHasMore: this.comment.children ? this.comment.children.hasMore : false,
            busyReplies: false,
        };
    },
    computed: {
        effective() {
            return this.revealed || this.comment;
        },
        showReplyToggle() {
            return !this.isNested && this.canComment;
        },
        blockedLabel() {
            return i18n.t('CommentModule.base', 'Comment of blocked user.');
        },
        showLabel() {
            return i18n.t('CommentModule.base', 'Show');
        },
        readMoreLabel() {
            return i18n.t('CommentModule.base', 'Read full comment...');
        },
        replyLabel() {
            return i18n.t('CommentModule.base', 'Reply');
        },
        // Same wording/category/placeholder as CommentList's own "show next"
        // link: the legacy nested Comments::widget() reused the exact same
        // ShowMore strings for children, there never was a distinct
        // "replies" message key.
        moreRepliesLabel() {
            return i18n.t('CommentModule.base', 'Show next {count} comments', { count: this.childRemainingNext });
        },
        // No server-formatted absolute time in the JSON payload (only ISO
        // `createdAt` - see plan §"Timestamps") - formatted client-side via
        // the browser locale/timezone (documented parity gap vs.
        // TimeAgo::getFullDateTime(), which uses the HumHub profile
        // timezone). This text is only ever visible for an instant: v-additions
        // runs the real `timeago` addition (registered selector-less in
        // humhub.ui.additions.js, dispatched per-element through the generic
        // `[data-ui-addition]` addition - see TimeAgo::renderTimeAgo()'s own
        // `data-ui-addition="timeago"` markup, reproduced above) on mount,
        // which immediately overwrites it with a live relative time.
        absoluteTime() {
            return new Date(this.comment.createdAt).toLocaleString();
        },
    },
    mounted() {
        if (this.highlighted && typeof this.$el.scrollIntoView === 'function') {
            this.$el.scrollIntoView({ block: 'center' });
        }
    },
    methods: {
        reveal() {
            client.get(url('/comment/comment/info', { id: this.comment.id, showBlocked: 1 }))
                .then((response) => {
                    this.revealed = response;
                })
                .catch((e) => {
                    log.error(e, true);
                });
        },
        loadMoreReplies() {
            if (this.busyReplies || this.childItems.length === 0) {
                return;
            }
            this.busyReplies = true;
            const cursor = this.childItems[this.childItems.length - 1].id;

            client.get(url('/comment/comment/list', {
                contentId: this.comment.contentId,
                parentCommentId: this.comment.id,
                commentId: cursor,
                direction: 'next',
                pageSize: this.pageSize,
            })).then((response) => {
                this.childItems = [...this.childItems, ...response.comments];
                this.childTotal = response.total;
                this.childRemainingNext = response.nextCount;
                // Same shape as the server's own preview formula (`total >
                // count($items)`), recomputed with the freshly returned
                // total rather than the one captured at initial hydration -
                // see the childHasMore comment in data() above.
                this.childHasMore = this.childTotal > this.childItems.length;
            }).catch((e) => {
                log.error(e, true);
            }).finally(() => {
                this.busyReplies = false;
            });
        },
        toggleReply() {
            this.replyOpen = !this.replyOpen;
            if (this.replyOpen) {
                this.$nextTick(() => {
                    if (this.$refs.replyForm) {
                        this.$refs.replyForm.focus();
                    }
                });
            }
        },
        onEdit() {
            log.warn('TODO(P2-5): edit comment', this.comment.id);
        },
        onDelete() {
            log.warn('TODO(P2-5): delete comment', this.comment.id);
        },
        onAdminDelete() {
            log.warn('TODO(P2-5): admin-delete comment', this.comment.id);
        },
    },
};
</script>
