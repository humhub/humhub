<template>
    <div class="bg-light p-2 mt-3 comment-container" :class="{ 'd-none': isCollapsed }">
        <CommentList
            v-if="loaded"
            ref="list"
            :content-id="contentId"
            :comments="comments"
            :prev-count="prevCount"
            :next-count="nextCount"
            :page-size="pageSize"
            :can-comment="showForm"
            :form-shell-html="formShellHtml"
            :anchor-comment-id="anchorCommentId"
        />
        <CommentForm
            v-if="showForm && formShellHtml"
            ref="form"
            :shell-html="formShellHtml"
            :content-id="contentId"
            @created="onMainCreated"
        />
    </div>
</template>

<script>
/**
 * Top-level Vue island for a content's comment thread. Auto-registered as
 * <comment-section> (see vue.build.mjs). Owns the read-path list window,
 * total count, collapse/toggle state and the top-level create form; per-entry
 * concerns (children preview, blocked reveal, reply forms) live in
 * CommentEntry.
 *
 * ## Revision map (P2-5, binding for future in-place entry swaps)
 *
 * CommentList/CommentEntry key each rendered entry by `id + ':' + revision`
 * rather than plain `id` (see their own `revisionKey()`). Reveal, edit-save
 * and a live-update reply-append all replace an entry OBJECT in whichever
 * array owns it (CommentList's `items`, or a parent CommentEntry's
 * `childItems`) under the SAME id — without a key bump, Vue would reuse the
 * existing component instance and its `data()` (which seeds several fields
 * from the `comment` prop once, e.g. CommentEntry's `childItems`/`childTotal`)
 * would silently go stale. `revisions` (id -> integer) is provided down the
 * whole tree together with `bumpCommentRevision()`/`pruneCommentRevision()`
 * (the latter called once an id leaves an owning array for good, on delete -
 * see `CommentList.removeRoot()`/`CommentEntry.onChildRemoved()` - so the map
 * does not grow forever across a long-lived session), so any descendant can
 * force its own or a sibling's remount without prop/event-drilling a map
 * through every intermediate level. `adjustTotal()`, `registerKnownId()` and
 * `isKnownId()` are provided the same way and for the same reason:
 * CommentEntry is the only place that knows a delete/reply-create's count
 * delta or a newly-seen comment id at the moment it happens, several levels
 * below this component.
 *
 * ## Live updates (P2-5)
 *
 * Subscribes to `humhub:modules:comment:live:NewComment` (see
 * `live/resources/js/humhub.live.poll.js` `triggerEventUpdates()` — the
 * handler receives `(evt, liveEvents, response)`, where each `liveEvents[]`
 * entry is a `LiveEvent::getData()` shape: `{type, data: {commentId,
 * contentId}}`, NOT a flat `{commentId, contentId}`). Events for a foreign
 * `contentId` or an already-known `commentId` are ignored via the SAME
 * `knownIds` guard `onMainCreated()`/`CommentEntry.onReplyCreated()` check -
 * this also covers the race where a live event for the poster's own
 * just-created comment arrives before (or after) that comment's own create
 * response, so the comment is never appended twice regardless of which path
 * wins. `knownIds` is deliberately append-only (never pruned on delete, see
 * `data()` below) - otherwise a live event for an id whose delete response
 * just hasn't been processed yet would slip past the guard, attempt an
 * `info` fetch for an id that (from the server's perspective) may already be
 * gone, and either 404 or, worse, resurrect a just-deleted entry. Otherwise
 * the comment is fetched via `info` and appended - to the root window if it
 * has no parent, or spliced into the parent's `children` (revision-bumped,
 * per above) if the parent is currently loaded, else only the count is
 * bumped (documented pragmatic gap: a reply under a currently-unloaded
 * parent has nowhere to preview into). Counts update even while the section
 * is collapsed - the list stays mounted underneath the `d-none` toggle, so a
 * newly appended entry is already in place once expanded.
 *
 * ## Island mount id contract (BINDING for P2-6)
 *
 * The PHP side renders the island's mount element with the LEGACY id
 * `comment_<IdHelper-format>` - i.e. `comment_` followed by
 * `\humhub\modules\comment\helpers\IdHelper::getId($content, null)`
 * (e.g. `comment_C42P` for content id 42, no parent comment - the root
 * section always has no parent, hence the trailing empty `P`). That is the
 * element this component listens on for `humhub:comment:toggle` and
 * dispatches `humhub:comment:countChanged` from (see `mounted()`/
 * `dispatchCountChanged()` below) - NOT a `#comment_<contentId>` id as an
 * earlier draft of this comment assumed. `countChanged`'s `detail` is
 * `{contentId, total}`.
 *
 * @since 1.19
 */
import { client, events, getConfig, log, url } from '@humhub/vue';
import CommentList from './components/CommentList.vue';
import CommentForm from './components/CommentForm.vue';

// The live event this component subscribes to for new comments/replies (see
// the class docblock's "Live updates" section).
const LIVE_NEW_COMMENT = 'humhub:modules:comment:live:NewComment';

/** Seeds the dedup set from an already-hydrated window: root ids + their loaded child ids. */
const collectKnownIds = (comments) => {
    const ids = [];
    (comments || []).forEach((comment) => {
        ids.push(comment.id);
        if (comment.children && comment.children.items) {
            comment.children.items.forEach((child) => ids.push(child.id));
        }
    });
    return ids;
};

export default {
    i18nCategories: ['CommentModule.base'],
    components: { CommentList, CommentForm },
    props: {
        contentId: { type: Number, required: true },
        // serializeWindow() payload: {comments, prevCount, nextCount, total}
        initial: { type: Object, default: null },
        canComment: { type: Boolean, default: false },
        // __VUEFORM__ shell token template, see LegacyFormWrapper.vue
        formShellHtml: { type: String, default: null },
        pageSize: { type: Number, default: 10 },
        // permalink highlight target
        anchorCommentId: { type: Number, default: null },
        // stream preview: section hidden until toggled via humhub:comment:toggle
        collapsed: { type: Boolean, default: false },
    },
    data() {
        return {
            comments: this.initial ? this.initial.comments : [],
            prevCount: this.initial ? this.initial.prevCount : 0,
            nextCount: this.initial ? this.initial.nextCount : 0,
            total: this.initial ? this.initial.total : 0,
            loaded: !!this.initial,
            isCollapsed: this.collapsed,
            // id -> revision counter, bumped whenever an entry object is
            // swapped in place under the same id (reveal/edit/live-append) —
            // see the class docblock's "Revision map" section.
            revisions: {},
            // Dedup set for own-create-vs-live races and live-update replay —
            // append-only by design, see the class docblock's "Live updates"
            // section for why entries are never removed on delete.
            knownIds: new Set(this.initial ? collectKnownIds(this.initial.comments) : []),
            // Guards the on-expand fetch in onToggle() against overlapping
            // requests from repeated toggle events (see its own comment).
            expandingBusy: false,
        };
    },
    provide() {
        return {
            commentRevisions: this.revisions,
            bumpCommentRevision: this.bumpCommentRevision,
            pruneCommentRevision: this.pruneCommentRevision,
            adjustTotal: this.adjustTotal,
            registerKnownId: this.registerKnownId,
            isKnownId: this.isKnownId,
        };
    },
    computed: {
        guest() {
            return getConfig('user').isGuest === true;
        },
        showForm() {
            return this.canComment && !this.guest;
        },
    },
    watch: {
        // Only fires on genuine changes (not the data() initial assignment),
        // so hydrating from `initial` never spuriously notifies the bridge of
        // a count it already rendered itself.
        total(value) {
            this.dispatchCountChanged(value);
        },
    },
    created() {
        if (!this.initial) {
            this.fetchInitial();
        }
    },
    mounted() {
        // The island runtime (humhub.vue.js) mounts INSIDE the original mount
        // point (the server-rendered `#comment_<IdHelper-format>` element,
        // e.g. `#comment_C42P` - see the class docblock above) — Vue 3's
        // app.mount() replaces the container's *content*, not the container
        // itself. So this component's own root node ends up as a CHILD of
        // that element, reachable via `this.$el.parentElement`.
        // `parentElement` works the same whether `this.$el` is currently a
        // real Element (the rendered <div class="comment-container">) or a
        // Comment-node render placeholder (would only happen for a top-level
        // v-if that resolves false) — both Node types expose it identically,
        // so no special-casing is needed even though this template has no
        // such v-if today.
        this.mountEl = this.$el.parentElement;
        if (this.mountEl) {
            this.mountEl.addEventListener('humhub:comment:toggle', this.onToggle);
        }
        events.on(LIVE_NEW_COMMENT, this.onLiveNewComment);
    },
    unmounted() {
        if (this.mountEl) {
            this.mountEl.removeEventListener('humhub:comment:toggle', this.onToggle);
        }
        events.off(LIVE_NEW_COMMENT, this.onLiveNewComment);
    },
    methods: {
        fetchInitial() {
            client.get(url('/comment/comment/list', { contentId: this.contentId, pageSize: this.pageSize }))
                .then((response) => {
                    this.comments = response.comments;
                    this.prevCount = response.prevCount;
                    this.nextCount = response.nextCount;
                    this.total = response.total;
                    this.knownIds = new Set(collectKnownIds(response.comments));
                    this.loaded = true;
                })
                .catch((e) => {
                    log.error(e, true);
                    // Mirrors LikeButton's fallback: don't hang forever on a
                    // failed self-fetch (e.g. guestHideComments 403) — settle
                    // into an empty-but-rendered state instead.
                    this.loaded = true;
                });
        },
        onToggle() {
            this.isCollapsed = false;

            // `commentsPreviewMax = 0` (see Comments::getLimit()) ships an initial window
            // with zero comments but a real `total` - the legacy behavior for this config
            // was to lazily load the default window on first expand (an auto "show more"
            // click, see the old humhub.comment.js toggleComment()) rather than never
            // showing anything at all. Reproduced here: an empty-but-nonzero window fetches
            // the default (unanchored) window exactly like fetchInitial() does, the moment
            // the section is actually opened instead of on mount.
            if (this.comments.length === 0 && this.total > 0 && !this.expandingBusy) {
                this.expandingBusy = true;
                client.get(url('/comment/comment/list', { contentId: this.contentId, pageSize: this.pageSize }))
                    .then((response) => {
                        this.comments = response.comments;
                        this.prevCount = response.prevCount;
                        this.nextCount = response.nextCount;
                        this.total = response.total;
                        collectKnownIds(response.comments).forEach((id) => this.knownIds.add(id));
                    })
                    .catch((e) => {
                        log.error(e, true);
                    })
                    .finally(() => {
                        this.expandingBusy = false;
                    });
            }

            this.$nextTick(() => {
                if (this.$refs.form) {
                    this.$refs.form.focus();
                }
            });
        },
        dispatchCountChanged(total) {
            if (!this.mountEl) {
                return;
            }
            this.mountEl.dispatchEvent(new CustomEvent('humhub:comment:countChanged', {
                bubbles: true,
                detail: { contentId: this.contentId, total },
            }));
        },
        bumpCommentRevision(id) {
            this.revisions[id] = (this.revisions[id] || 0) + 1;
        },
        // Called once an id leaves an owning array for good (delete) — keeps
        // `revisions` from growing forever across a long-lived session. Safe
        // unlike `knownIds`: a pruned id can never legitimately reappear
        // under the same `:key` scheme (the guard on `knownIds` — see
        // `isKnownId()` — stops a stale live/create event from ever
        // resurrecting it into an array in the first place).
        pruneCommentRevision(id) {
            delete this.revisions[id];
        },
        adjustTotal(delta) {
            this.total += delta;
        },
        registerKnownId(id) {
            this.knownIds.add(id);
        },
        isKnownId(id) {
            return this.knownIds.has(id);
        },
        onMainCreated(comment) {
            // Guards the same own-create-vs-live race as appendLiveComment():
            // a slow POST can resolve after the live poller already delivered
            // (and appended) the same comment.
            if (this.isKnownId(comment.id)) {
                return;
            }
            this.registerKnownId(comment.id);
            this.total += 1;
            if (this.$refs.list) {
                this.$refs.list.appendRoot(comment);
            }
        },
        onLiveNewComment(evt, liveEvents) {
            (liveEvents || []).forEach((liveEvent) => this.handleLiveEvent(liveEvent));
        },
        handleLiveEvent(liveEvent) {
            const data = (liveEvent && liveEvent.data) || {};
            if (Number(data.contentId) !== this.contentId) {
                return;
            }

            const commentId = Number(data.commentId);
            if (this.isKnownId(commentId)) {
                return;
            }

            client.get(url('/comment/comment/info', { id: commentId }))
                .then((comment) => this.appendLiveComment(comment))
                .catch((e) => {
                    log.error(e, true);
                });
        },
        appendLiveComment(comment) {
            if (this.isKnownId(comment.id)) {
                // Raced with another path (e.g. our own create) between the
                // dedup check in handleLiveEvent() and this info fetch resolving.
                return;
            }

            this.registerKnownId(comment.id);
            this.total += 1;

            if (!this.$refs.list) {
                return;
            }

            if (comment.parentCommentId === null || comment.parentCommentId === undefined) {
                this.$refs.list.appendRoot(comment);
                return;
            }

            const parent = this.$refs.list.findRoot(comment.parentCommentId);
            if (!parent || !parent.children) {
                // Parent not currently loaded in the window — nothing to
                // preview into, the bumped total above is all that changes.
                return;
            }

            const items = [...parent.children.items, comment];
            const total = parent.children.total + 1;
            this.$refs.list.replaceRoot(parent.id, {
                ...parent,
                children: { total, items, hasMore: total > items.length },
            });
            this.bumpCommentRevision(parent.id);
        },
    },
};
</script>
