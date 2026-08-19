<template>
    <div class="bg-light p-2 mt-3 comment-container" :class="{ 'd-none': isCollapsed }">
        <CommentList
            v-if="loaded"
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
 * Mutations (create/edit/delete/live updates) are P2-5 — this task only
 * wires the read path + UI skeleton, per docs/superpowers/plans/2026-08-19-vuejs-comments.md.
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
import { client, getConfig, log, url } from '@humhub/vue';
import CommentList from './components/CommentList.vue';
import CommentForm from './components/CommentForm.vue';

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
    },
    unmounted() {
        if (this.mountEl) {
            this.mountEl.removeEventListener('humhub:comment:toggle', this.onToggle);
        }
    },
    methods: {
        fetchInitial() {
            client.get(url('/comment/comment/list', { contentId: this.contentId, pageSize: this.pageSize }))
                .then((response) => {
                    this.comments = response.comments;
                    this.prevCount = response.prevCount;
                    this.nextCount = response.nextCount;
                    this.total = response.total;
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
    },
};
</script>
