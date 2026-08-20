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
            :comment="comment"
            :permalink="comment.permalink"
            :can-edit="comment.canEdit"
            :can-delete="comment.canDelete"
            :can-admin-delete="comment.canAdminDelete"
            @edit="onEdit"
            @delete="onDelete"
            @admin-delete="onAdminDelete"
        />

        <div class="flex-shrink-0 comment-header-image">
            <UserImage v-bind="comment.author" :size="25" />
        </div>

        <div class="flex-grow-1">
            <h4 class="comment-heading">
                <a :href="comment.author.url" :data-contentcontainer-id="comment.author.contentContainerId" :data-guid="comment.author.guid">{{ comment.author.displayName }}</a>
                <small>
                    &middot;
                    <time class="tt time timeago" data-ui-addition="timeago" :datetime="comment.createdAt" :title="absoluteTime">{{ absoluteTime }}</time>
                    <template v-if="comment.isEdited">
                        &middot; <i class="tt fa fa-clock-o text-body-secondary" :title="updatedAtTitle" aria-hidden="true"></i>
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
                    <RichTextOutput
                        class="comment-message"
                        data-ui-markdown
                        data-ui-show-more
                        :data-read-more-text="readMoreLabel"
                        :message="comment.message"
                        :render-options="comment.messageRenderOptions"
                    />
                    <div v-if="comment.attachmentsHtml" v-html="comment.attachmentsHtml"></div>
                </template>
            </div>

            <div class="wall-entry-controls">
                <template v-if="showReplyToggle">
                    <a href="#" @click.prevent="toggleReply">{{ replyLabel }}<span
                        class="comment-count"
                        :data-count="childTotal"
                        :style="childTotal > 0 ? null : 'display:none'"
                    > ({{ childTotal }})</span></a>
                </template>
                <template v-if="showReplyToggle && comment.likes">
                    &middot;
                </template>
                <LikeButton
                    v-if="comment.likes"
                    :record-id="comment.recordId"
                    :like-count="comment.likes.count"
                    :current-user-liked="comment.likes.liked"
                />
                <ExtensionSlot name="comment.links" :context="{ comment }" />
            </div>

            <div v-if="comment.children" class="nested-comments-root">
                <div class="bg-light p-2 mt-3 comment-container" :class="{ 'd-none': !childItems.length && !replyOpen }">
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
 * `<ExtensionSlot name="comment.links">` at the end of `.wall-entry-controls` lets a
 * module add its own entry link (e.g. a reaction/report action) alongside the core
 * Reply/Like links above, without forking this template - see docs/develop/ui-js-vuejs.md,
 * "Extension slots". `CommentControls` (the `⋮` dropdown) has its own `comment.controls`
 * slot for menu-style extensions - see its own docblock.
 *
 * ## Visual parity fixes (browser-verified against the legacy UI)
 *
 * - Entry links render Reply before Like (`CommentEntryLinks`' sort order:
 *   `CommentLink` 100, `LikeLink` 200 - see `widgets\BaseStack::run()`), with
 *   the `&middot;` separator only between the two when BOTH are rendered,
 *   mirroring `BaseStack`'s own "join non-empty widgets only" behavior.
 * - The avatar is a `<UserImage v-bind="comment.author" :size="25" />` (see
 *   `protected/humhub/vue/UserImage.vue` - core component set, resolved
 *   through the global registry the same way `RichTextOutput` is, not
 *   imported here) rather than hand-rolled markup: it owns the
 *   `data-contentcontainer-id` popover hook, the `imageAlt` fallback and the
 *   online-status overlay parity with `user\widgets\Image::run()` that used
 *   to live inline in this file. The author-name link below is separate and
 *   still hand-rolled here - it additionally carries `data-guid` (mirroring
 *   `Html::containerLink()`, which sets both attributes on the NAME link,
 *   never on the avatar image itself).
 * - The edited marker (`comment.isEdited`) gets a real tooltip (`.tt` +
 *   `title`, the same mechanism `absoluteTime`'s time tag already uses -
 *   see `updatedAtTitle` below) with the edit time, mirroring
 *   `UpdatedIcon::getByDated($comment->updated_at)` - client-formatted like
 *   `absoluteTime`, the same documented parity gap vs. the server/profile-
 *   timezone-formatted legacy tooltip text.
 * - `RichTextOutput` now receives `comment-message`/`data-ui-markdown`/
 *   `data-ui-show-more`/`data-read-more-text` directly (Vue attribute
 *   fallthrough onto its own template root) instead of via an extra
 *   wrapping `<div>` this component used to render around it - the legacy
 *   markup has no such extra div between `.comment-message` and the
 *   richtext envelope, and the extra level broke `.comment-message > ...`
 *   child-combinator theme CSS.
 * - The nested reply list + reply form are wrapped in a
 *   `.bg-light.p-2.mt-3.comment-container` div (`d-none` while there's
 *   nothing to show), matching the `.comment-container` the legacy
 *   `Comments::widget()` template (`comments.php`) renders around the exact
 *   same content at every nesting level, including this one - dropped
 *   during the initial island port (P2-6), which cost the nested block its
 *   padding/background AND left `_comment.scss`'s own
 *   `.nested-comments-root .comment-container .showMore` rule dead code.
 *   `CommentSection.vue` already reproduces this same legacy wrapper one
 *   level up (its `isCollapsed`-driven `.comment-container`), just not this
 *   nested one.
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
 *
 * ## Next-pagination gap fix ("Show next 0 comments")
 *
 * `onReplyCreated()` pushes the viewer's own newly-created reply straight onto the tail of
 * `childItems` without a real `/comment/comment/list` fetch. Before this fix,
 * `loadMoreReplies()` used that tail item as its pagination cursor, the label
 * (`childRemainingNext`) and the gate (`childHasMore`) were two independently-mutated
 * fields, and `onReplyCreated()`/`onChildRemoved()` only ever updated the latter - so after
 * an own-reply append, the label kept showing whatever count was last fetched (stale) while
 * the gate correctly stayed open. Clicking it then paged forward from the just-appended
 * reply instead of from the last comment the server actually confirmed as loaded, skipping
 * over the real gap: the server returned zero matching replies (`nextCount: 0`), which got
 * written straight into the label, producing a permanent "Show next 0 comments" dead link
 * (the gate stayed open too, since `childTotal` still exceeded `childItems.length`).
 *
 * Fixed the same way as CommentList's own root-level pagination (see its docblock, same
 * section header, for the shared reasoning):
 *  - `childRemaining`/`childHasMore` are now ONE derived computed pair (`childTotal -
 *    childItems.length`) instead of two independently-mutated fields.
 *  - `childLastCursorId` tracks the last SERVER-confirmed reply separately from
 *    `childItems`' tail, updated only by a real `loadMoreReplies()` response.
 *  - A `loadMoreReplies()` response may re-return a reply already present in `childItems`
 *    (deduped via `isKnownId()`/`registerKnownId()`), with genuinely new ones spliced in
 *    right before the first item newer than the cursor rather than appended past it again.
 */
import { client, i18n, log, modal, url } from '@humhub/vue';
import CommentControls from './CommentControls.vue';
import CommentForm from './CommentForm.vue';

// RichTextOutput/UserImage are NOT imported here — they live at protected/humhub/vue/ (see
// docs/develop/ui-js-vuejs.md) and resolve through the global Vue component registry
// (every registered component is available in every island, see humhub.vue.js's
// register()). CoreVueAsset must register before this island's own script runs — enforced
// via CommentVueAsset::$depends, not by import order here.
export default {
    name: 'CommentEntry',
    components: { CommentControls, CommentForm },
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
            // Id of the last item of the last SERVER-PAGINATED reply window (initial
            // hydration or a loadMoreReplies() response) - deliberately never touched by
            // onReplyCreated()'s own/live append, see "Next-pagination gap fix" below.
            childLastCursorId: this.comment.children && this.comment.children.items.length
                ? this.comment.children.items[this.comment.children.items.length - 1].id
                : null,
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
        // Single derived count driving BOTH the `v-if="childHasMore"` gate and this label's
        // `{count}` (previously two independently-mutated fields that could desync - see
        // "Next-pagination gap fix" below) - `childTotal` is kept correct by every mutation
        // (onReplyCreated/onChildRemoved/loadMoreReplies), so this can never show a nonzero
        // gate with a stale/zero label or vice versa.
        childRemaining() {
            return Math.max(0, this.childTotal - this.childItems.length);
        },
        childHasMore() {
            return this.childRemaining > 0;
        },
        // Same wording/category/placeholder as CommentList's own "show next"
        // link: the legacy nested Comments::widget() reused the exact same
        // ShowMore strings for children, there never was a distinct
        // "replies" message key.
        moreRepliesLabel() {
            return i18n.t('CommentModule.base', 'Show next {count} comments', { count: this.childRemaining });
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
        // Same client-side-formatting choice as `absoluteTime` above (no server-formatted
        // string in the payload - see CommentJsonService's own `updatedAt` docblock note),
        // for the same documented parity gap vs. UpdatedIcon::getByDated()'s server/profile-
        // timezone-formatted tooltip. `null` (no `title` attribute) whenever the comment
        // isn't edited, since `updatedAt` is only ever set in that case.
        updatedAtTitle() {
            return this.comment.updatedAt ? new Date(this.comment.updatedAt).toLocaleString() : null;
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
            this.adjustTotal(1);
            this.registerKnownId(comment.id);
        },
        onChildRemoved(id) {
            this.childItems = this.childItems.filter((child) => child.id !== id);
            this.childTotal = Math.max(0, this.childTotal - 1);
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
        // See this component's own docblock, "Next-pagination gap fix": cursors from
        // `childLastCursorId` (the last SERVER-confirmed reply), never from the tail of
        // `childItems` (which may be an own-appended reply past an unloaded gap). The
        // response can therefore legitimately re-return a reply already present in
        // `childItems` - deduped via the shared `isKnownId()`/`registerKnownId()` mechanism -
        // with genuinely new ones spliced in right before the first item newer than the
        // cursor, i.e. before that appended tail, preserving chronological order.
        loadMoreReplies() {
            if (this.busyReplies || this.childItems.length === 0 || this.childLastCursorId === null) {
                return;
            }
            this.busyReplies = true;
            const cursor = this.childLastCursorId;

            client.get(url('/comment/comment/list', {
                contentId: this.comment.contentId,
                parentCommentId: this.comment.id,
                commentId: cursor,
                direction: 'next',
                pageSize: this.pageSize,
            })).then((response) => {
                const newComments = response.comments.filter((comment) => !this.isKnownId(comment.id));
                newComments.forEach((comment) => this.registerKnownId(comment.id));

                let insertIndex = this.childItems.findIndex((item) => item.id > cursor);
                if (insertIndex === -1) {
                    insertIndex = this.childItems.length;
                }
                this.childItems.splice(insertIndex, 0, ...newComments);

                if (response.comments.length > 0) {
                    this.childLastCursorId = response.comments[response.comments.length - 1].id;
                }
                this.childTotal = response.total;
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
