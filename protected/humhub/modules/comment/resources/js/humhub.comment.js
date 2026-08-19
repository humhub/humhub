/**
 * Thin legacy bridge for the comment section Vue island (`comment/vue/CommentSection.vue`).
 *
 * Comments themselves render entirely client-side from JSON now (see
 * `humhub\modules\comment\widgets\Comments`) - this module only keeps the handful of
 * `data-action-*` hooks still reachable from plain server-rendered markup that never goes
 * through the island's own Vue template:
 *
 *  - `toggleComment` - bound via `data-action-click="comment.toggleComment"` in
 *    `comment/widgets/views/link.php` (the "Comment (3)"/"Reply" link rendered by
 *    `CommentLink`, which stays a plain PHP widget - see the plan's non-goal on islandizing
 *    it). Dispatches a `humhub:comment:toggle` CustomEvent on the resolved island mount
 *    element; `CommentSection.vue` listens for it (see its own `mounted()`) to lift the
 *    `d-none` collapse and focus the form.
 *  - `scrollActive`/`scrollInactive` - bound via the `events` option of the `RichTextField`
 *    rendered into the comment form shell (see `comment/widgets/views/commentFormShell.php`);
 *    toggles the scroll-shadow class on the editor's wrapper exactly like the pre-Vue form did.
 *
 * Count bookkeeping used to be this module's own job (`Form.prototype.incrementCommentCount`
 * walking up to the nearest `.stream-entry-addons`/`[data-action-component="comment.Comment"]`
 * ancestor). A reply's own badge is now owned entirely by `CommentEntry.vue` (reactive
 * `childTotal`), so the only remaining DOM outside the island is the wall entry's overall
 * "Comment (n)" link rendered by `CommentLink` - `onCountChanged()` below updates that badge
 * when the island dispatches `humhub:comment:countChanged` (bubbling `{contentId, total}`).
 */
humhub.module('comment', function (module, require, $) {

    var toggleComment = function (evt) {
        var target = evt.$target && evt.$target.get(0);
        if (!target) {
            return;
        }

        target.dispatchEvent(new CustomEvent('humhub:comment:toggle', {bubbles: true}));
    };

    var scrollActive = function (evt) {
        evt.$trigger.closest('.richtext-create-input-group').addClass('scrollActive');
    };

    var scrollInactive = function (evt) {
        evt.$trigger.closest('.richtext-create-input-group').removeClass('scrollActive');
    };

    /**
     * Updates the `.comment-count` badge of the `CommentLink` rendered alongside the island
     * that dispatched the event - i.e. the "Comment (n)" link inside the same wall entry's
     * `.stream-entry-addons` (see `content/widgets/views/wallEntry.php` -
     * `WallEntryAddons::widget()` renders both `WallEntryLinks` - which carries the badge -
     * and `Comments` - the island - as siblings). No-op when the island isn't inside a wall
     * entry (e.g. a standalone `Comments::widget()` embed) or carries no such badge.
     */
    var onCountChanged = function (evt) {
        var addons = evt.target.closest && evt.target.closest('.stream-entry-addons');
        if (!addons) {
            return;
        }

        var $commentCount = $(addons).find('.wall-entry-controls:first .comment-count');
        if (!$commentCount.length) {
            return;
        }

        var total = evt.detail.total;
        $commentCount
            .text(' (' + total + ')')
            .attr('data-count', total)
            .data('count', total)
            .toggle(total > 0);
    };

    module.init = function () {
        // `document` is never replaced by pjax (only `#layout-content` is - see
        // humhub.vue.js `module.unload`), so a single document-level listener keeps
        // receiving the bubbling event from every island for the lifetime of the page.
        // Guarded so re-running init() (defensive - this module does not set
        // `initOnPjaxLoad`, so the core only calls it once) never double-binds.
        if (module._countChangedBound) {
            return;
        }
        module._countChangedBound = true;

        document.addEventListener('humhub:comment:countChanged', onCountChanged);
    };

    module.export({
        toggleComment: toggleComment,
        scrollActive: scrollActive,
        scrollInactive: scrollInactive
    });
});
