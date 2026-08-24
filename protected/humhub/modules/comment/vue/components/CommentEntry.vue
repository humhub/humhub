<template>
    <div
        v-if="comment.blocked && !revealed"
        :id="'comment_' + comment.id"
        class="d-flex comment-blocked-user"
    >
        <div class="flex-shrink-0 me-2"></div>
        <div class="flex-grow-1 overflow-hidden">
            {{ blockedLabel }}
            <a href="#" class="text-primary" @click.prevent="revealed = true">{{ showLabel }}</a>
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
            :permalink="comment.url"
            :can-edit="permissions ? permissions.canEdit : false"
            :can-delete="permissions ? permissions.canDelete : false"
            :can-admin-delete="canAdminDelete"
            :loading-permissions="permissionsBusy"
            @open="loadPermissions"
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
                    <time class="tt time timeago" data-ui-addition="timeago" :datetime="createdAtIso" :title="absoluteTime">{{ absoluteTime }}</time>
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
                        :upload-options="uploadOptions"
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
                    <CommentAttachments v-if="comment.files.length" :files="comment.files" :context-id="comment.id" />
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
                <template v-if="showReplyToggle && likeState && likeState.canLike">
                    &middot;
                </template>
                <LikeButton
                    v-if="likeState && likeState.canLike"
                    :record-id="comment.recordId"
                    :like-count="likeState.total"
                    :current-user-liked="likeState.liked"
                />
                <ExtensionSlot name="comment.links" :context="{ comment }" />
            </div>

            <div v-if="comment.replies" class="nested-comments-root">
                <div class="bg-light p-2 mt-3 comment-container" :class="{ 'd-none': !childItems.length && !replyOpen }">
                    <div class="comment">
                        <div v-if="childHasMore" class="showMore">
                            <a href="#" :class="{ disabled: busyReplies }" @click.prevent="loadMoreReplies">{{ moreRepliesLabel }}</a>
                        </div>

                        <template v-for="child in childItems" :key="revisionKey(child)">
                            <hr class="comment-separator">
                            <CommentEntry
                                :comment="child"
                                :is-nested="true"
                                :can-comment="canComment"
                                :form-shell-html="formShellHtml"
                                :submit-icon-html="submitIconHtml"
                                :upload-options="uploadOptions"
                                :page-size="pageSize"
                                @entry-removed="onChildRemoved"
                                @entry-updated="onChildUpdated"
                            />
                        </template>
                    </div>

                    <CommentForm
                        v-if="replyOpen && formShellHtml"
                        ref="replyForm"
                        :shell-html="formShellHtml"
                        :content-id="comment.contentId"
                        :parent-comment-id="comment.id"
                        :submit-icon-html="submitIconHtml"
                        :upload-options="uploadOptions"
                        @created="onReplyCreated"
                    />
                </div>
            </div>

            <CommentDeleteModal
                v-model:show="deleteModalOpen"
                :admin-mode="canAdminDelete"
                @confirm="performDelete"
            />
        </div>
    </div>
</template>

<script>
/**
 * Renders one comment entry - root or one-level-nested reply, recursively
 * (though a reply's own `replies` is always null server-side, so recursion
 * bottoms out after one level; see `comment\serializers\CommentSerializer`).
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
 * "Extension slots". `CommentControls` (the `⋮` dropdown) exposes its items as a
 * `comment.controls` menu (`registerMenuEntry()`/`removeMenuEntry()`, see
 * docs/develop/ui-js-vuejs-extensions.md, "Menu entries") rather than a slot - see its own
 * docblock.
 *
 * ## Visual parity fixes (browser-verified against the legacy UI)
 *
 * - Entry links render Reply before Like (`CommentEntryLinks`' sort order:
 *   `CommentLink` 100, `LikeLink` 200 - see `widgets\BaseStack::run()`), with
 *   the `&middot;` separator only between the two when BOTH are rendered,
 *   mirroring `BaseStack`'s own "join non-empty widgets only" behavior.
 * - The avatar is a `<UserImage v-bind="comment.author" :size="25" />` (see
 *   `protected/humhub/modules/user/vue/UserImage.vue` - provided by the user module,
 *   resolved through the global registry the same way `RichTextOutput` is, not
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
 * Total-count bookkeeping (`adjustTotal`, `adjustRootTotal`), the revision map
 * (`bumpCommentRevision`/`pruneCommentRevision`) and live-update dedup
 * (`registerKnownId`/`isKnownId`) are injected straight from CommentSection
 * instead of bubbling through CommentList as a third event: only the entry
 * itself knows its own delta (`-(1 + childTotal)` for a root delete, `-1`
 * for a reply, `+1` for an accepted reply) at the moment it happens, and
 * calling the section directly avoids a `CommentList` passthrough that would
 * otherwise exist for no reason other than forwarding a number. `adjustRootTotal`
 * additionally only ever fires for a ROOT delete (`!isNested`, delta always `-1`,
 * never `-(1 + childTotal)`) - see CommentSection's own docblock, "Root-only
 * remaining count": replies are never part of `rootTotal` in the first place, on
 * either side of a delete.
 * `onReplyCreated()` also uses `isKnownId()` to guard the same own-create-
 * vs-live race `CommentSection.onMainCreated()` guards for the root form.
 *
 * Every injection carries a safe no-op/empty default so this component
 * tolerates being mounted without a providing ancestor (e.g. in isolation).
 *
 * ## Previous-direction pagination fix ("Show next N comments" that never advances)
 *
 * The reply preview (`comment.replies.items`, from
 * `CommentSerializer::replyPreviewItems()` -> `CommentListService::getLimited()` ->
 * `getSiblings(0, limit)` with the default `LIST_DIR_PREV`) always shows the NEWEST `limit`
 * replies. The hidden replies are therefore always the OLDER ones - but `loadMoreReplies()`
 * used to paginate with `direction=next` from the newest loaded reply's id, a dead end by
 * construction: there is never anything newer than the newest loaded reply, so the server
 * always returned an empty window and the link ("Show next N comments") stayed dead forever,
 * no matter how many older replies remained hidden (browser-verified: comment id 4 with 4
 * replies, preview showing the newest 2 (ids 15/16), "Show next 2 comments" that did nothing
 * on click). The legacy nested widget avoided this by rendering its ShowMore ABOVE the reply
 * list with `LIST_DIR_PREV` semantics (see `ShowMore.php`/`comments.php`) - the same
 * "Show previous/next {count} comments" message keys were always reused for both directions
 * and both nesting levels, so the mismatch was purely a client wiring bug, not a message-key
 * gap.
 *
 * Fixed by mirroring CommentList's own root-level `loadPrev()` exactly, instead of its
 * `loadNext()` (which this used to copy):
 *  - `direction: 'previous'`, cursored from `childFirstCursorId` - the id of the FIRST
 *    (oldest) item of the last SERVER-PAGINATED window (initial hydration or a
 *    `loadMoreReplies()` response), deliberately never touched by `onReplyCreated()`'s
 *    own/live append or by `onChildRemoved()` (an append always adds at the TAIL - see
 *    `onReplyCreated()` - and a delete only ever removes an existing entry, never shifts the
 *    window backwards).
 *  - The response is PREPENDED (`[...response.results, ...this.childItems]`), same as
 *    `loadPrev()`: `response.results` for `direction=previous` already comes back ascending
 *    by id/`created_at` (`CommentListService::getSiblings()`'s own `array_reverse()`), so
 *    this is a straight, order-correct prepend - no splice-by-cursor-position logic needed.
 *  - No `isKnownId()`/`registerKnownId()` dedup, same as `loadPrev()`: the cursor is the
 *    HEAD of `childItems` and only ever moves further back, while every append (own or live)
 *    only ever happens at the TAIL (past the head) - so a `direction=previous` fetch can
 *    structurally never re-return an id already present in `childItems`, unlike the old
 *    `direction=next` scheme this replaces.
 *  - `childTotal` is deliberately NOT resynced from the response here (unlike `loadPrev()`/
 *    `loadNext()` resyncing `total`/`rootTotal`): `serializeWindow()`'s `total`/`rootTotal`
 *    are content-global, not scoped to this one parent's replies, so there is no reply-scoped
 *    total in the response to resync from - and none is needed anyway, since revealing
 *    already-existing hidden replies never changes how many replies exist for this parent;
 *    the value `onReplyCreated()`/`onChildRemoved()` already maintain stays correct through a
 *    load-more.
 *  - The show-more link moved ABOVE the child list in the template (chronologically correct:
 *    older replies belong above the newest-loaded preview) and now shares the "Show previous
 *    {count} comments" key with the root list's own `prevLabel`, matching the legacy widget's
 *    placement/wording for a `LIST_DIR_PREV` ShowMore.
 *
 * `childRemaining`/`childHasMore` stay the single derived computed pair (`childTotal -
 * childItems.length`) introduced for the childRemaining/gate desync this same click used to
 * also cause - see `childRemaining`'s own comment below.
 */
import { getConfig, i18n, log } from '@humhub/vue';
import CommentAttachments from './CommentAttachments.vue';
import CommentControls from './CommentControls.vue';
import CommentDeleteModal from './CommentDeleteModal.vue';
import CommentForm from './CommentForm.vue';
import { deleteComment, fetchComment, fetchCommentPermissions, fetchWindow, isAdminDelete } from './commentApi.js';

// RichTextOutput/UserImage are NOT imported here — RichTextOutput lives at
// protected/humhub/vue/ (core component set) and UserImage at
// protected/humhub/modules/user/vue/ (see docs/develop/ui-js-vuejs.md) — both resolve
// through the global Vue component registry (every registered component is available in
// every island, see humhub.vue.js's register()). CoreVueAsset/UserVueAsset must each
// register before this island's own script runs — enforced via CommentVueAsset::$depends,
// not by import order here.
export default {
    name: 'CommentEntry',
    components: { CommentAttachments, CommentControls, CommentDeleteModal, CommentForm },
    inject: {
        commentRevisions: { default: () => ({}) },
        bumpCommentRevision: { default: () => () => {} },
        pruneCommentRevision: { default: () => () => {} },
        adjustTotal: { default: () => () => {} },
        adjustRootTotal: { default: () => () => {} },
        registerKnownId: { default: () => () => {} },
        isKnownId: { default: () => () => false },
        // recordId => {total, liked, canLike} for every comment of this section, owned by
        // CommentSection: the like state is the one per-record value that depends on WHO is
        // asking, so it travels beside the (cacheable) comment payload rather than inside it
        // (see docs/develop/concept-api.md). `ensureLikeStates` loads the states of comments
        // that just entered the tree.
        likeStates: { default: () => ({}) },
        ensureLikeStates: { default: () => () => {} },
    },
    props: {
        comment: { type: Object, required: true },
        canComment: { type: Boolean, default: false },
        formShellHtml: { type: String, default: null },
        submitIconHtml: { type: String, default: null },
        // Upload field settings, handed down to the reply/edit forms this entry can open.
        uploadOptions: { type: Object, default: null },
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
            // Blocked-author masking is purely client-side (see commentApi.js's
            // `blocked` derivation): revealing is a local display toggle, no refetch —
            // the payload always carries the full comment.
            revealed: false,
            // `{canEdit, canDelete}` once the context menu was opened for the first time -
            // fetched then rather than shipped with the comment (see loadPermissions()).
            permissions: null,
            permissionsBusy: false,
            childItems: this.comment.replies ? [...this.comment.replies.items] : [],
            childTotal: this.comment.replies ? this.comment.replies.total : 0,
            // Id of the FIRST (oldest) item of the last SERVER-PAGINATED reply window (initial
            // hydration or a loadMoreReplies() response) - deliberately never touched by
            // onReplyCreated()'s own/live append, see "Previous-direction pagination fix" below.
            childFirstCursorId: this.comment.replies && this.comment.replies.items.length
                ? this.comment.replies.items[0].id
                : null,
            busyReplies: false,
            busyEdit: false,
            // Drives the native <CommentDeleteModal> (see its own docblock) — one modal
            // serving both the plain confirm and the admin-delete (notify/reason) mode,
            // selected by the adapted comment's canAdminDelete.
            deleteModalOpen: false,
        };
    },
    computed: {
        showReplyToggle() {
            return !this.isNested && this.canComment;
        },
        // The like state of THIS entry, out of the section's map (see the `likeStates` prop).
        // Absent until the section has it, which is why the like link renders conditionally.
        likeState() {
            return this.likeStates[this.comment.recordId] || null;
        },
        // Deleting someone else's comment is moderation — drives the delete modal's
        // reason/notify mode. Derived from the fetched permissions, so `false` until the menu
        // was opened; that is fine, since both delete paths start from that menu.
        canAdminDelete() {
            return isAdminDelete(this.comment, !!(this.permissions && this.permissions.canDelete));
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
        // `{count}` (previously two independently-mutated fields that could desync) -
        // `childTotal` is kept correct by every mutation (onReplyCreated/onChildRemoved),
        // so this can never show a nonzero gate with a stale/zero label or vice versa.
        childRemaining() {
            return Math.max(0, this.childTotal - this.childItems.length);
        },
        childHasMore() {
            return this.childRemaining > 0;
        },
        // Same wording/category/key as CommentList's own "show previous" link
        // (`prevLabel`), not "show next": the hidden replies are always the OLDER ones (the
        // preview shows the NEWEST N, see "Previous-direction pagination fix" above), so this
        // is symmetric with the root list's own loadPrev()/prevLabel, not loadNext()/
        // nextLabel. The legacy nested Comments::widget() reused these exact same ShowMore
        // strings for children too - there never was a distinct "replies" message key.
        moreRepliesLabel() {
            return i18n.t('CommentModule.base', 'Show previous {count} comments', { count: this.childRemaining });
        },
        // The adapted comment shape carries real `Date`s (DB-format wire timestamps
        // parsed with the announced server timezone, see commentApi.js/
        // parseServerDateTime). Formatted client-side via the browser locale/timezone
        // (documented parity gap vs. TimeAgo::getFullDateTime(), which uses the HumHub
        // profile timezone). This text is only ever visible for an instant: v-additions
        // runs the real `timeago` addition (registered selector-less in
        // humhub.ui.additions.js, dispatched per-element through the generic
        // `[data-ui-addition]` addition - see TimeAgo::renderTimeAgo()'s own
        // `data-ui-addition="timeago"` markup, reproduced above) on mount,
        // which immediately overwrites it with a live relative time.
        absoluteTime() {
            return this.comment.createdAt ? this.comment.createdAt.toLocaleString() : null;
        },
        // The timeago addition needs a machine-readable instant on the `datetime`
        // attribute — the adapted shape's Date serialized back to ISO.
        createdAtIso() {
            return this.comment.createdAt ? this.comment.createdAt.toISOString() : null;
        },
        // Same client-side-formatting choice as `absoluteTime` above, for the same
        // documented parity gap vs. UpdatedIcon::getByDated()'s server/profile-
        // timezone-formatted tooltip. `null` (no `title` attribute) whenever the
        // comment isn't edited — the marker itself is gated on `isEdited`.
        updatedAtTitle() {
            return this.comment.isEdited && this.comment.updatedAt
                ? this.comment.updatedAt.toLocaleString()
                : null;
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
        onEdit() {
            if (this.busyEdit) {
                return;
            }
            this.busyEdit = true;
            // The API shape already carries the raw markdown `message` —
            // a fresh single fetch just guards against editing a stale copy.
            fetchComment(this.comment.id)
                .then((comment) => {
                    this.editMessage = comment.message;
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
            this.ensureLikeStates([comment]);
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
        // Loads `{canEdit, canDelete}` the first time this entry's context menu opens. They
        // are deliberately not part of the comment payload — see fetchCommentPermissions() and
        // docs/develop/concept-api.md — and they are needed nowhere else, since both the edit
        // and the delete flow start from this menu. Guests never have permissions, so they
        // never trigger a request.
        loadPermissions() {
            if (this.permissions || this.permissionsBusy || getConfig('user').isGuest === true) {
                return;
            }

            this.permissionsBusy = true;

            return fetchCommentPermissions(this.comment.id)
                .then((permissions) => {
                    this.permissions = permissions;
                })
                .catch((e) => {
                    log.error(e, true);
                })
                .then(() => {
                    this.permissionsBusy = false;
                });
        },
        // Both CommentControls events land here: the same native <CommentDeleteModal>
        // serves the plain confirm and the admin-delete mode — its `adminMode` prop
        // reads `canAdminDelete` directly, so no per-event branching is needed.
        onDelete() {
            this.deleteModalOpen = true;
        },
        onAdminDelete() {
            this.deleteModalOpen = true;
        },
        performDelete(extraFields) {
            this.deleteModalOpen = false;
            return deleteComment(this.comment.id, extraFields)
                .then(() => {
                    // The endpoint answers 204 No Content — anything other than a
                    // resolved promise arrives in the catch() below.

                    // Mirrors Form.prototype.incrementCommentCount's
                    // `-1 - subComments` in humhub.comment.js: a reply can't have
                    // its own children (server enforces one nesting level), so
                    // only a root entry's own current reply count is subtracted.
                    this.adjustTotal(-(1 + (this.isNested ? 0 : this.childTotal)));
                    // rootTotal only ever counts roots - a reply delete leaves it alone (see
                    // CommentSection's own docblock, "Root-only remaining count").
                    if (!this.isNested) {
                        this.adjustRootTotal(-1);
                    }
                    this.$emit('entry-removed', this.comment.id);
                })
                .catch((e) => {
                    log.error(e, true);
                });
        },
        // See this component's own docblock, "Previous-direction pagination fix": cursors
        // from `childFirstCursorId` (the HEAD of the last SERVER-PAGINATED window), mirroring
        // CommentList's own loadPrev() exactly - a straight prepend, no dedup, no total
        // resync (see the docblock for why none of those are needed here).
        loadMoreReplies() {
            if (this.busyReplies || this.childItems.length === 0 || this.childFirstCursorId === null) {
                return;
            }
            this.busyReplies = true;
            const cursor = this.childFirstCursorId;

            fetchWindow({
                contentId: this.comment.contentId,
                parentCommentId: this.comment.id,
                commentId: cursor,
                direction: 'previous',
                pageSize: this.pageSize,
            }).then((response) => {
                this.childItems = [...response.results, ...this.childItems];
                this.ensureLikeStates(response.results);
                if (response.results.length > 0) {
                    this.childFirstCursorId = response.results[0].id;
                }
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
