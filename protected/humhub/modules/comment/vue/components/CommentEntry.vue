<template>
    <div
        v-if="comment.blocked"
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
            :permalink="comment.permalink"
            :can-edit="comment.canEdit"
            :can-delete="comment.canDelete"
            :can-admin-delete="comment.canAdminDelete"
            @edit="onEdit"
            @delete="onDelete"
            @admin-delete="onAdminDelete"
        />

        <div class="flex-shrink-0 comment-header-image">
            <a :href="comment.author.url">
                <img class="rounded" style="width: 25px; height: 25px" :src="comment.author.imageUrl" :alt="comment.author.displayName">
            </a>
        </div>

        <div class="flex-grow-1">
            <h4 class="comment-heading">
                <a :href="comment.author.url">{{ comment.author.displayName }}</a>
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
                <template v-if="editing && formShellHtml">
                    <CommentForm
                        ref="editForm"
                        :shell-html="formShellHtml"
                        :content-id="comment.contentId"
                        :edit-comment-id="comment.id"
                        :initial-message="editMessage"
                        :submit-icon-html="submitIconHtml"
                        @updated="onEditSaved"
                    />
                    <a href="#" class="comment-cancel-edit-link" @click.prevent="cancelEdit">{{ cancelEditLabel }}</a>
                </template>
                <template v-else>
                    <div class="comment-message" data-ui-show-more :data-read-more-text="readMoreLabel">
                        <RichTextOutput :output="comment.messageOutput" />
                    </div>
                    <div v-if="comment.attachmentsHtml" v-html="comment.attachmentsHtml"></div>
                </template>
            </div>

            <div class="wall-entry-controls">
                <LikeButton
                    v-if="comment.likes"
                    :record-id="comment.recordId"
                    :like-count="comment.likes.count"
                    :current-user-liked="comment.likes.liked"
                />
                <template v-if="showReplyToggle">
                    &middot;
                    <a href="#" @click.prevent="toggleReply">{{ replyLabel }}<span
                        class="comment-count"
                        :data-count="childTotal"
                        :style="childTotal > 0 ? null : 'display:none'"
                    > ({{ childTotal }})</span></a>
                </template>
            </div>

            <div v-if="comment.children" class="nested-comments-root">
                <div class="comment">
                    <template v-for="child in childItems" :key="revisionKey(child)">
                        <hr class="comment-separator">
                        <CommentEntry
                            :comment="child"
                            :is-nested="true"
                            :can-comment="canComment"
                            :form-shell-html="formShellHtml"
                            :submit-icon-html="submitIconHtml"
                            :page-size="pageSize"
                            @entry-removed="onChildRemoved"
                            @entry-updated="onChildUpdated"
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
                    :submit-icon-html="submitIconHtml"
                    @created="onReplyCreated"
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
 *
 * ## Mutation surface (P2-5)
 *
 * `comment` is read directly everywhere (no more local `revealed`/`effective`
 * override): reveal(), edit-save() and a parent's live-update reply-append
 * all swap the entry OBJECT in whichever array owns it (CommentList's
 * `items` for a root entry, the parent CommentEntry's `childItems` for a
 * reply) and bump a shared `commentRevisions[id]` counter (injected from
 * CommentSection) that feeds the `:key` both CommentList and this component
 * use for their `v-for` - see the CommentSection docblock. That forces a full
 * remount with the fresh `comment` prop, so `childItems`/`childTotal`/
 * `replyOpen`/etc (all seeded once in `data()`, see the P2-4 review note this
 * task retrofits) re-initialize correctly instead of going stale.
 *
 * Two bubbling events carry this upward, consumed by whichever direct parent
 * owns this entry's array (CommentList for a root entry, the parent
 * CommentEntry for a reply):
 *  - `entry-removed(id)` - this entry's own delete/admin-delete succeeded.
 *  - `entry-updated({id, comment})` - this entry's own reveal/edit succeeded.
 *
 * Total-count bookkeeping (`adjustTotal`), the revision map
 * (`bumpCommentRevision`/`pruneCommentRevision`) and live-update dedup
 * (`registerKnownId`/`isKnownId`) are injected straight from CommentSection
 * instead of bubbling through CommentList as a third event: only the entry
 * itself knows its own delta (`-(1 + childTotal)` for a root delete, `-1`
 * for a reply, `+1` for an accepted reply) at the moment it happens, and
 * calling the section directly avoids a `CommentList` passthrough that would
 * otherwise exist for no reason other than forwarding a number.
 * `onReplyCreated()` also uses `isKnownId()` to guard the same own-create-
 * vs-live race `CommentSection.onMainCreated()` guards for the root form.
 *
 * Every injection carries a safe no-op/empty default so this component
 * tolerates being mounted without a providing ancestor (e.g. in isolation).
 */
import { client, i18n, log, modal, url } from '@humhub/vue';
import RichTextOutput from './RichTextOutput.vue';
import CommentControls from './CommentControls.vue';
import CommentForm from './CommentForm.vue';

export default {
    name: 'CommentEntry',
    components: { RichTextOutput, CommentControls, CommentForm },
    inject: {
        commentRevisions: { default: () => ({}) },
        bumpCommentRevision: { default: () => () => {} },
        pruneCommentRevision: { default: () => () => {} },
        adjustTotal: { default: () => () => {} },
        registerKnownId: { default: () => () => {} },
        isKnownId: { default: () => () => false },
    },
    props: {
        comment: { type: Object, required: true },
        canComment: { type: Boolean, default: false },
        formShellHtml: { type: String, default: null },
        submitIconHtml: { type: String, default: null },
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
    emits: ['entry-removed', 'entry-updated'],
    data() {
        return {
            replyOpen: false,
            editing: false,
            editMessage: null,
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
            busyReveal: false,
            busyEdit: false,
        };
    },
    computed: {
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
        cancelEditLabel() {
            return i18n.t('CommentModule.base', 'Cancel Edit');
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
        revisionKey(comment) {
            return comment.id + ':' + (this.commentRevisions[comment.id] || 0);
        },
        reveal() {
            if (this.busyReveal) {
                return;
            }
            this.busyReveal = true;
            client.get(url('/comment/comment/info', { id: this.comment.id, showBlocked: 1 }))
                .then((comment) => {
                    this.$emit('entry-updated', { id: this.comment.id, comment });
                })
                .catch((e) => {
                    log.error(e, true);
                })
                .finally(() => {
                    this.busyReveal = false;
                });
        },
        onEdit() {
            if (this.busyEdit) {
                return;
            }
            this.busyEdit = true;
            client.get(url('/comment/comment/update', { id: this.comment.id }))
                .then((response) => {
                    this.editMessage = response.message;
                    this.editing = true;
                })
                .catch((e) => {
                    log.error(e, true);
                })
                .finally(() => {
                    this.busyEdit = false;
                });
        },
        cancelEdit() {
            // Discard before unmounting - see CommentForm.vue's/LegacyFormWrapper.vue's
            // "Unsaved-changes guard" docblock section (P2-7 fix): a form the user walks
            // away from without submitting must not leave a stale acknowledgeForm/backup
            // baseline armed for a later, unrelated pjax navigation to trip over.
            if (this.$refs.editForm) {
                this.$refs.editForm.clear();
            }
            this.editing = false;
            this.editMessage = null;
        },
        onEditSaved(comment) {
            this.editing = false;
            this.editMessage = null;
            this.$emit('entry-updated', { id: this.comment.id, comment });
        },
        onReplyCreated(comment) {
            // Guards the same own-create-vs-live race CommentSection's own
            // onMainCreated() guards for the root form: a slow reply POST can
            // resolve after the live poller already delivered (and appended)
            // the same comment under this same parent.
            if (this.isKnownId(comment.id)) {
                return;
            }
            this.childItems.push(comment);
            this.childTotal += 1;
            this.childHasMore = this.childTotal > this.childItems.length;
            this.adjustTotal(1);
            this.registerKnownId(comment.id);
        },
        onChildRemoved(id) {
            this.childItems = this.childItems.filter((child) => child.id !== id);
            this.childTotal = Math.max(0, this.childTotal - 1);
            this.childHasMore = this.childTotal > this.childItems.length;
            this.pruneCommentRevision(id);
        },
        onChildUpdated({ id, comment }) {
            const index = this.childItems.findIndex((child) => child.id === id);
            if (index !== -1) {
                this.childItems.splice(index, 1, comment);
            }
            this.bumpCommentRevision(id);
        },
        onDelete() {
            modal.confirm({
                header: i18n.t('CommentModule.base', '<strong>Confirm</strong> comment deleting'),
                body: i18n.t('CommentModule.base', 'Do you really want to delete this comment?'),
                confirmText: i18n.t('CommentModule.base', 'Delete'),
                cancelText: i18n.t('CommentModule.base', 'Cancel'),
            }).then((confirmed) => {
                if (confirmed) {
                    return this.performDelete();
                }
            }).catch((e) => {
                log.error(e, true);
            });
        },
        onAdminDelete() {
            client.get(url('/comment/comment/get-admin-delete-modal', { id: this.comment.id }))
                .then((response) => modal.confirm(response).then((confirmed) => {
                    if (!confirmed) {
                        return;
                    }

                    // The confirm modal's own footer buttons drive resolve/reject
                    // (see Content.prototype.adminDelete in humhub.content.js) -
                    // there is no "modal submit" action to hook into. Legacy reads
                    // the admin-delete reason/notify fields the same way, straight
                    // off the fixed #globalModalConfirm singleton's own form
                    // (`modal.globalConfirm.$.find('form')[0]`), since that is the
                    // one DOM node AdminDeleteModal::widget() just rendered `body`
                    // into. Not exposed via the `modal` bridge (only
                    // confirm()/load() are) - jQuery is already a documented
                    // direct dependency of this component tree (see
                    // LegacyFormWrapper), so reading the fixed singleton id
                    // directly here mirrors the legacy call site 1:1.
                    const fields = {};
                    jQuery('#globalModalConfirm form').serializeArray().forEach(({ name, value }) => {
                        fields[name] = value;
                    });

                    return this.performDelete(fields);
                }))
                .catch((e) => {
                    log.error(e, true);
                });
        },
        performDelete(extraFields) {
            const cfg = extraFields ? { data: extraFields } : undefined;

            return client.post(url('/comment/comment/delete', { id: this.comment.id }), cfg)
                .then((response) => {
                    if (!response || !response.success) {
                        // Distinct from the catch() below (a transport/HTTP
                        // failure): the request succeeded but the server
                        // reported `{success: false}` - same
                        // log.error(_, true) mechanism as everywhere else in
                        // this file for the visible status side effect, with
                        // a message specific enough to tell the two apart.
                        log.error('Comment delete failed', response, true);
                        return;
                    }

                    // Mirrors Form.prototype.incrementCommentCount's
                    // `-1 - subComments` in humhub.comment.js: a reply can't have
                    // its own children (server enforces one nesting level), so
                    // only a root entry's own current reply count is subtracted.
                    this.adjustTotal(-(1 + (this.isNested ? 0 : this.childTotal)));
                    this.$emit('entry-removed', this.comment.id);
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
            if (this.replyOpen) {
                // Closing without submitting - discard so a stale unsaved-changes
                // guard never fires for content the user is walking away from
                // (see CommentForm.vue's "Unsaved-changes guard" docblock section).
                if (this.$refs.replyForm) {
                    this.$refs.replyForm.clear();
                }
                this.replyOpen = false;
                return;
            }

            this.replyOpen = true;
            this.$nextTick(() => {
                if (this.$refs.replyForm) {
                    this.$refs.replyForm.focus();
                }
            });
        },
    },
};
</script>
