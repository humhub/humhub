<template>
    <div class="comment" :class="{ 'guest-mode': guest }" :id="commentsAreaId">
        <div v-if="remainingPrev > 0 && items.length > 0" class="showMore">
            <a href="#" :class="{ disabled: busyPrev }" @click.prevent="loadPrev">{{ prevLabel }}</a>
        </div>

        <template v-for="comment in items" :key="revisionKey(comment)">
            <hr class="comment-separator">
            <CommentEntry
                :comment="comment"
                :can-comment="canComment"
                :form-shell-html="formShellHtml"
                :submit-icon-html="submitIconHtml"
                :upload-options="uploadOptions"
                :page-size="pageSize"
                :highlighted="anchorCommentId !== null && comment.id === anchorCommentId"
                @entry-removed="removeRoot"
                @entry-updated="onEntryUpdated"
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
 * the rest CommentDefinitions::getCommentWindow()). Owns its own working copy of the
 * window so show-more prepend/append never mutates the parent's props array
 * directly - total count is unaffected by paging, so nothing needs to be
 * emitted upward for it.
 *
 * Also owns the root-level slice of the mutation surface (create/delete/edit/
 * live-append all resolve here for top-level comments): `appendRoot()`/
 * `removeRoot()`/`replaceRoot()`/`findRoot()` are called directly via
 * `ref` by CommentSection, while `entry-removed`/`entry-updated` bubble up
 * from each root `<CommentEntry>` for delete and edit/reveal respectively.
 *
 * `commentRevisions`/`bumpCommentRevision`/`pruneCommentRevision` are
 * injected from CommentSection - see its own docblock for the
 * `id + ':' + revision` remount-on-swap mechanism this and CommentEntry
 * share. Defaults are provided for every injection so this component
 * survives being mounted in isolation (e.g. a future standalone unit test)
 * without a providing ancestor.
 *
 * ## Next-pagination gap fix ("Show next 0 comments")
 *
 * `appendRoot()` (own create, called by CommentSection's `onMainCreated()`) and the
 * root-append branch of CommentSection's own live-update handler both push straight onto
 * the tail of `items` without going through a real window fetch. Before
 * this fix, `loadNext()` used that tail item as its pagination cursor - once such an
 * append had happened, the cursor was the just-appended comment itself, so a "next"
 * request paged forward from THERE instead of from the last comment the server actually
 * confirmed as loaded, skipping over whatever the true gap was. The server dutifully
 * returned zero matching comments (`nextCount: 0`) while `total` (bumped by the append)
 * still exceeded `items.length`, leaving a permanent, dead "Show next 0 comments" link.
 *
 * Fixed by:
 *  - Deriving `remainingNext` (gate AND label, one value) from `rootTotal - items.length -
 *    remainingPrev` instead of trusting the server's per-request `nextCount` directly -
 *    `rootTotal` is kept correct by every mutation (CommentSection's `adjustRootTotal()`),
 *    so this can never desync the way two independently-updated fields could.
 *  - Tracking `lastCursorId` separately from `items`' tail: seeded from the initial window
 *    and updated ONLY by a real `loadNext()` response, never by `appendRoot()`/
 *    `replaceRoot()`. `loadNext()` always cursors from `lastCursorId`.
 *  - Since the cursor no longer moves past an appended tail, a `loadNext()` response can
 *    legitimately include comments already present in `items` (that same appended tail,
 *    now confirmed by the server too) - deduped via the shared `isKnownId()`/
 *    `registerKnownId()` mechanism (see CommentSection's "Live updates" docblock section),
 *    with the genuinely new ones spliced in right before the first item newer than the
 *    cursor, preserving chronological order instead of appending past the tail again.
 *
 * ## Root-vs-all total ("phantom show-next-N-replies" fix)
 *
 * `remainingNext` originally keyed off `total` (the badge count, counting ALL comments of
 * the content INCLUDING replies - see `the rest CommentDefinitions::getCommentWindow()`'s own
 * docblock note). `items`/`remainingPrev` here only ever cover ROOT comments, so on any
 * thread with replies `total - items.length - remainingPrev` overcounted by exactly the
 * reply count, rendering a permanently-dead "Show next N comments" link (N = reply count,
 * since the "next" fetch from the true last root cursor legitimately returns nothing).
 * Fixed by keying `remainingNext` off `rootTotal` (see CommentSection's own docblock,
 * "Root-only remaining count") instead - a prop refreshed the same way `total` already was,
 * via `adjustRootTotal()`.
 */
import { getConfig, i18n, log } from '@humhub/vue';
import CommentEntry from './CommentEntry.vue';
import { fetchWindow } from './commentApi.js';
import { getId } from './commentIdHelper.js';

export default {
    components: { CommentEntry },
    inject: {
        commentRevisions: { default: () => ({}) },
        bumpCommentRevision: { default: () => () => {} },
        pruneCommentRevision: { default: () => () => {} },
        adjustTotal: { default: () => () => {} },
        adjustRootTotal: { default: () => () => {} },
        registerKnownId: { default: () => () => {} },
        isKnownId: { default: () => () => false },
        // See CommentEntry's own inject block - the like state travels beside the comments,
        // not inside them, and has to be loaded for every window this component pages in.
        ensureLikeStates: { default: () => () => {} },
    },
    props: {
        contentId: { type: Number, required: true },
        comments: { type: Array, required: true },
        prevCount: { type: Number, required: true },
        // Badge count (CommentSection's own `total`, ALL comments including replies) - only
        // used here to keep that badge in sync on a real fetch (see loadPrev()/loadNext()),
        // NOT for `remainingNext` (see `rootTotal` below and this component's own docblock,
        // "Root-vs-all total" section).
        total: { type: Number, required: true },
        // Authoritative ROOT-comment count (CommentSection's own `rootTotal`) - drives the
        // `remainingNext` computed below instead of the server's per-request `nextCount` or
        // the all-comments `total`, see this component's own docblock, "Next-pagination gap
        // fix" and "Root-vs-all total".
        rootTotal: { type: Number, required: true },
        pageSize: { type: Number, default: 10 },
        canComment: { type: Boolean, default: false },
        formShellHtml: { type: String, default: null },
        submitIconHtml: { type: String, default: null },
        // Upload field settings, handed down to every form this list can open.
        uploadOptions: { type: Object, default: null },
        anchorCommentId: { type: Number, default: null },
    },
    data() {
        return {
            items: [...this.comments],
            remainingPrev: this.prevCount,
            // Id of the last item of the last SERVER-PAGINATED window (initial hydration or a
            // loadNext() response) - deliberately never touched by appendRoot()/replaceRoot()
            // (own/live creates), see the docblock below.
            lastCursorId: this.comments.length ? this.comments[this.comments.length - 1].id : null,
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
        // Single derived count (see this component's own docblock) instead of a second,
        // independently-mutated `remainingNext` field: `rootTotal` already accounts for
        // every ROOT create/delete/live mutation (CommentSection's `adjustRootTotal()`), so
        // subtracting the hidden-before (`remainingPrev`) and loaded (`items.length`) counts
        // always yields the true hidden-after count, with no separate bookkeeping that can
        // drift out of sync with the "show more" gate. Deliberately `rootTotal`, not `total`
        // (all comments including replies) - see this component's own docblock, "Root-vs-all
        // total".
        remainingNext() {
            return Math.max(0, this.rootTotal - this.items.length - this.remainingPrev);
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
            this.lastCursorId = value.length ? value[value.length - 1].id : null;
        },
        prevCount(value) {
            this.remainingPrev = value;
        },
    },
    methods: {
        revisionKey(comment) {
            return comment.id + ':' + (this.commentRevisions[comment.id] || 0);
        },
        /** Appended at the end, mirroring the legacy Form.prototype.addComment placement. */
        appendRoot(comment) {
            this.items.push(comment);
        },
        removeRoot(id) {
            this.items = this.items.filter((comment) => comment.id !== id);
            this.pruneCommentRevision(id);
        },
        replaceRoot(id, comment) {
            const index = this.items.findIndex((item) => item.id === id);
            if (index !== -1) {
                this.items.splice(index, 1, comment);
            }
        },
        findRoot(id) {
            return this.items.find((comment) => comment.id === id) || null;
        },
        onEntryUpdated({ id, comment }) {
            this.replaceRoot(id, comment);
            this.bumpCommentRevision(id);
        },
        loadPrev() {
            if (this.busyPrev || this.items.length === 0) {
                return;
            }
            this.busyPrev = true;
            const cursor = this.items[0].id;

            fetchWindow({
                contentId: this.contentId,
                commentId: cursor,
                direction: 'previous',
                pageSize: this.pageSize,
            }).then((response) => {
                this.items = [...response.results, ...this.items];
                this.ensureLikeStates(response.results);
                this.remainingPrev = response.prevCount;
                this.adjustTotal(response.total - this.total);
                // Refreshes the ROOT-only counterpart the same way, falling back to
                // `response.total` for a caller/fixture that predates `rootTotal` - see
                // CommentSection's own docblock, "Root-only remaining count".
                this.adjustRootTotal((response.rootTotal ?? response.total) - this.rootTotal);
            }).catch((e) => {
                log.error(e, true);
            }).finally(() => {
                this.busyPrev = false;
            });
        },
        // See this component's own docblock, "Next-pagination gap fix": the cursor is the
        // last known-good SERVER cursor (`lastCursorId`), never the tail of `items` (which may
        // be an own/live-appended comment past an unloaded gap). The response can therefore
        // re-return comments already present in `items` (that same appended tail) - deduped via
        // the shared `isKnownId()`/`registerKnownId()` mechanism - and genuinely new ones are
        // spliced in right before the first item newer than the cursor, i.e. before that
        // appended tail, not blindly at the end.
        loadNext() {
            if (this.busyNext || this.items.length === 0 || this.lastCursorId === null) {
                return;
            }
            this.busyNext = true;
            const cursor = this.lastCursorId;

            fetchWindow({
                contentId: this.contentId,
                commentId: cursor,
                direction: 'next',
                pageSize: this.pageSize,
            }).then((response) => {
                const newComments = response.results.filter((comment) => !this.isKnownId(comment.id));
                newComments.forEach((comment) => this.registerKnownId(comment.id));
                this.ensureLikeStates(newComments);

                let insertIndex = this.items.findIndex((item) => item.id > cursor);
                if (insertIndex === -1) {
                    insertIndex = this.items.length;
                }
                this.items.splice(insertIndex, 0, ...newComments);

                if (response.results.length > 0) {
                    this.lastCursorId = response.results[response.results.length - 1].id;
                }
                this.adjustTotal(response.total - this.total);
                // See loadPrev()'s own comment above for the `?? response.total` fallback.
                this.adjustRootTotal((response.rootTotal ?? response.total) - this.rootTotal);
            }).catch((e) => {
                log.error(e, true);
            }).finally(() => {
                this.busyNext = false;
            });
        },
    },
};
</script>
