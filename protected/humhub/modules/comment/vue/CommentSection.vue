<template>
    <div class="bg-light p-2 mt-3 comment-container" :class="{ 'd-none': isCollapsed }">
        <CommentList
            v-if="loaded"
            ref="list"
            :content-id="contentId"
            :comments="comments"
            :prev-count="prevCount"
            :total="total"
            :root-total="rootTotal"
            :page-size="pageSize"
            :can-comment="showForm"
            :form-shell-html="formShellHtml"
            :submit-icon-html="submitIconHtml"
            :anchor-comment-id="anchorCommentId"
        />
        <CommentForm
            v-if="showForm && formShellHtml"
            ref="form"
            :shell-html="formShellHtml"
            :content-id="contentId"
            :submit-icon-html="submitIconHtml"
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
 * through every intermediate level. `adjustTotal()`, `adjustRootTotal()`,
 * `registerKnownId()` and `isKnownId()` are provided the same way and for the
 * same reason: CommentEntry is the only place that knows a delete/reply-create's
 * count delta or a newly-seen comment id at the moment it happens, several
 * levels below this component.
 *
 * ## Root-only remaining count (`rootTotal`)
 *
 * `total` (this component's own field, driving the badge/`countChanged` event) counts
 * EVERY comment of the content, replies included - it doubles as the comment-count badge,
 * matching what the legacy widget counted (see `CommentSerializer::window()`'s docblock note on `total` vs. `rootTotal`). The root list's own "show next" gate must not
 * use it directly: `items`/`prevCount`/`nextCount` in that same window only ever cover ROOT
 * comments, so any thread with replies would make `total - items.length - remainingPrev`
 * (CommentList's `remainingNext`) overcount by exactly the reply count, rendering a
 * permanently-dead "Show next N comments" link (N = reply count) - the bug this field fixes.
 *
 * `rootTotal` is this component's separately-tracked root-only counterpart, seeded from
 * `initial.rootTotal` (falling back to `initial.total` if a caller/fixture predates this
 * field - see the field's `data()` comment for why that fallback is deliberately the OLD,
 * possibly-buggy formula rather than e.g. 0), refreshed from every list response
 * (`fetchInitial()`/the expand fetch in `onToggle()`, same `?? total` fallback), and passed
 * down to CommentList as its own prop for `remainingNext` to key off instead of `total`.
 * Mutated in lockstep with a ROOT-level create/delete only: `+1` in `onMainCreated()` and the
 * root branch of `appendLiveComment()`, `-1` via `adjustRootTotal()` called from
 * CommentEntry's `performDelete()` when `!isNested`. A reply create/delete never touches it
 * (`onReplyCreated()`/`onChildRemoved()` only ever adjust the per-parent `childTotal` and,
 * for creates, the badge `total` - never `rootTotal`).
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
import { events, getConfig, log } from '@humhub/vue';
import CommentList from './components/CommentList.vue';
import CommentForm from './components/CommentForm.vue';
import {
    collectRecordIds, fetchComment, fetchLikeStates, fetchWindow, mapWindow,
} from './components/commentApi.js';

// The live event this component subscribes to for new comments/replies (see
// the class docblock's "Live updates" section).
const LIVE_NEW_COMMENT = 'humhub:modules:comment:live:NewComment';

/** Seeds the dedup set from an already-hydrated window: root ids + their loaded child ids. */
const collectKnownIds = (comments) => {
    const ids = [];
    (comments || []).forEach((comment) => {
        ids.push(comment.id);
        if (comment.replies && comment.replies.items) {
            comment.replies.items.forEach((reply) => ids.push(reply.id));
        }
    });
    return ids;
};

export default {
    // 'ContentModule.base' is preloaded here (rather than declared again on
    // CommentForm, which isn't a directly-mounted island and so has no
    // i18nCategories of its own) for CommentForm's submit button label - see
    // that component's own docblock for why it reuses this category instead
    // of a CommentModule.base key. 'UserModule.base' is preloaded the same
    // way for CommentEntry's online-status overlay label (`onlineLabel`), and 'base'
    // for the profile-image alt phrase `UserImage` builds itself (see its docblock),
    // matching the exact keys `user\widgets\Image::run()` uses.
    i18nCategories: ['CommentModule.base', 'ContentModule.base', 'UserModule.base', 'base'],
    components: { CommentList, CommentForm },
    props: {
        contentId: { type: Number, required: true },
        // RAW window payload ({results, prevCount, nextCount, total, rootTotal} — the
        // shape of the comment window endpoint, exactly what this island's own fetches
        // return), mapped once via mapWindow() below.
        initial: { type: Object, default: null },
        // `recordId => {total, liked, canLike}` for the embedded initial window, handed over
        // by the widget: the window payload itself is caller-neutral (and therefore
        // cacheable, see docs/develop/concept-api.md) while THIS page render is per user
        // anyway, so inlining them saves the island its first `like/states` request.
        initialLikeStates: { type: Object, default: () => ({}) },
        canComment: { type: Boolean, default: false },
        // __VUEFORM__ shell token template, see LegacyFormWrapper.vue
        formShellHtml: { type: String, default: null },
        // Server-rendered submit-button icon HTML - see CommentForm.vue's own docblock.
        submitIconHtml: { type: String, default: null },
        pageSize: { type: Number, default: 10 },
        // permalink highlight target
        anchorCommentId: { type: Number, default: null },
        // stream preview: section hidden until toggled via humhub:comment:toggle
        collapsed: { type: Boolean, default: false },
    },
    data() {
        // The raw window payload is mapped ONCE here — everything below this
        // component works with the adapted shape (see commentApi.js's mapComment()).
        const initialWindow = this.initial ? mapWindow(this.initial) : null;

        return {
            comments: initialWindow ? initialWindow.results : [],
            // recordId => like state, for the whole section (see `initialLikeStates`). The
            // only per-record value that depends on who is asking, hence kept beside the
            // comments rather than inside them; `ensureLikeStates()` fills it for comments
            // that enter the tree later (paging, replies, own creates, live updates).
            likeStates: { ...this.initialLikeStates },
            prevCount: initialWindow ? initialWindow.prevCount : 0,
            // Mirrors the raw server payload shape for API completeness, but is no longer
            // read for gating - CommentList derives its own "next" remaining count from
            // `total`/`items.length`/`remainingPrev` instead, see its own docblock ("Next-
            // pagination gap fix") for why the server's per-request `nextCount` alone isn't
            // enough once own/live appends can move the pagination cursor past a real gap.
            nextCount: initialWindow ? initialWindow.nextCount : 0,
            total: initialWindow ? initialWindow.total : 0,
            // Root-only counterpart of `total` (see the class docblock's "Root-only
            // remaining count" section) - falls back to `total` itself when a caller/fixture
            // predates this field, i.e. the OLD (buggy-for-threads-with-replies) formula
            // rather than 0, so an unmigrated payload degrades no worse than before this fix.
            rootTotal: initialWindow ? (initialWindow.rootTotal ?? initialWindow.total) : 0,
            loaded: !!initialWindow,
            isCollapsed: this.collapsed,
            // id -> revision counter, bumped whenever an entry object is
            // swapped in place under the same id (edit-save/live-append) —
            // see the class docblock's "Revision map" section.
            revisions: {},
            // Dedup set for own-create-vs-live races and live-update replay —
            // append-only by design, see the class docblock's "Live updates"
            // section for why entries are never removed on delete.
            knownIds: new Set(initialWindow ? collectKnownIds(initialWindow.results) : []),
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
            adjustRootTotal: this.adjustRootTotal,
            registerKnownId: this.registerKnownId,
            isKnownId: this.isKnownId,
            likeStates: this.likeStates,
            ensureLikeStates: this.ensureLikeStates,
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
            return;
        }

        // Normally a no-op: the widget hands the embedded window's like states over as
        // `initialLikeStates` (see that prop), so nothing is missing. A caller that embeds a
        // window without them - any consumer that is not the PHP widget - gets them fetched
        // instead of silently losing every like link.
        this.ensureLikeStates(this.comments);
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
            // No explicit window size: the server defaults to `commentsPreviewMax`,
            // exactly like the embedded initial window the widget ships.
            fetchWindow({ contentId: this.contentId })
                .then((response) => {
                    this.comments = response.results;
                    this.prevCount = response.prevCount;
                    this.nextCount = response.nextCount;
                    this.total = response.total;
                    this.rootTotal = response.rootTotal ?? response.total;
                    this.knownIds = new Set(collectKnownIds(response.results));
                    this.ensureLikeStates(response.results);
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
                fetchWindow({ contentId: this.contentId })
                    .then((response) => {
                        this.comments = response.results;
                        this.prevCount = response.prevCount;
                        this.nextCount = response.nextCount;
                        this.total = response.total;
                        this.rootTotal = response.rootTotal ?? response.total;
                        collectKnownIds(response.results).forEach((id) => this.knownIds.add(id));
                        this.ensureLikeStates(response.results);
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
        // Root-only counterpart of adjustTotal() - see the class docblock's "Root-only
        // remaining count" section for the full mutation matrix (root create/delete only,
        // never a reply).
        adjustRootTotal(delta) {
            this.rootTotal += delta;
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
            this.ensureLikeStates([comment]);
            // The main form only ever creates a ROOT comment - see the class docblock's
            // "Root-only remaining count" section for why this stays separate from `total`.
            this.rootTotal += 1;
            if (this.$refs.list) {
                this.$refs.list.appendRoot(comment);
            }
        },
        /**
         * Loads the like states of comments that just entered the tree, in ONE request for
         * the whole batch, and only for records not already in the map (a re-render, an edit
         * or a reveal never refetches). Failures are logged and leave the affected entries
         * without a like link rather than breaking the list.
         */
        ensureLikeStates(comments) {
            const missing = collectRecordIds(comments).filter((recordId) => !this.likeStates[recordId]);

            if (missing.length === 0) {
                return;
            }

            fetchLikeStates(missing)
                .then((states) => {
                    Object.keys(states).forEach((recordId) => {
                        this.likeStates[recordId] = states[recordId];
                    });
                })
                .catch((e) => {
                    log.error(e, true);
                });
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

            fetchComment(commentId)
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
            this.ensureLikeStates([comment]);

            // Determined before the ref check below so `rootTotal` bumps in lockstep with
            // `total` above regardless of whether the list is currently mounted - see the
            // class docblock's "Root-only remaining count" section.
            const isRoot = comment.parentCommentId === null || comment.parentCommentId === undefined;
            if (isRoot) {
                this.rootTotal += 1;
            }

            if (!this.$refs.list) {
                return;
            }

            if (isRoot) {
                this.$refs.list.appendRoot(comment);
                return;
            }

            const parent = this.$refs.list.findRoot(comment.parentCommentId);
            if (!parent || !parent.replies) {
                // Parent not currently loaded in the window — nothing to
                // preview into, the bumped total above is all that changes.
                return;
            }

            const items = [...parent.replies.items, comment];
            const total = parent.replies.total + 1;
            this.$refs.list.replaceRoot(parent.id, {
                ...parent,
                replies: { total, items, hasMore: total > items.length },
            });
            this.bumpCommentRevision(parent.id);
        },
    },
};
</script>
