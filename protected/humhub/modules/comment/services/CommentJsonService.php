<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\services;

use humhub\models\RecordMap;
use humhub\modules\comment\components\SerializeCommentsEvent;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\Module;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\file\widgets\ShowFiles;
use humhub\modules\like\services\LikeService;
use humhub\modules\user\models\User;
use humhub\modules\user\services\IsOnlineService;
use Yii;
use yii\base\Event;
use yii\web\ForbiddenHttpException;

/**
 * Serializes {@see Comment} records and comment list windows into the JSON
 * shape consumed by the comment module's Vue frontend.
 *
 * Blocked-author masking and the `guestHideComments` module setting used to
 * only be enforced by the legacy HTML rendering path (`widgets\Comments` and
 * `widgets\Comment`). Since the JSON API can be called directly, both are
 * enforced here instead, mirroring the semantics of those widgets.
 *
 * @since 1.19
 */
class CommentJsonService
{
    /**
     * @see SerializeCommentsEvent
     */
    public const EVENT_SERIALIZE_COMMENTS = 'serializeComments';

    public function __construct(
        private readonly Content $content,
        private readonly ?Comment $parentComment = null,
    ) {
    }

    public static function create(Content|ContentActiveRecord|Comment $object): self
    {
        $content = match (true) {
            $object instanceof Comment => $object->content,
            $object instanceof Content => $object,
            default => $object->content,
        };

        return new self($content, $object instanceof Comment ? $object : null);
    }

    /**
     * Serializes a single comment, applying blocked-author masking and the
     * `guestHideComments` gate.
     *
     * @throws ForbiddenHttpException if comments are hidden from the current guest
     */
    public function serializeComment(Comment $comment, bool $showBlocked = false): array
    {
        $this->assertGuestAllowed();

        // A root comment's own loaded child previews are batched into the same single
        // event firing as the comment itself (see EVENT_SERIALIZE_COMMENTS' own docblock) -
        // there is no second, separate firing once serializeChildren() gets to them below.
        $childItems = $comment->parent_comment_id === null ? $this->getChildPreviewItems($comment) : null;
        $extensionData = $this->fireSerializeCommentsEvent(array_merge([$comment], $childItems ?? []));

        return $this->serialize($comment, $showBlocked, $extensionData, $childItems);
    }

    /**
     * Serializes a cursor- or anchor-based window of sibling comments.
     *
     * A `$direction` of `previous`/`next` pages from `$commentId` via
     * {@see CommentListService::getSiblings()} (real "show more" pagination).
     * Without a direction, `$commentId` is treated as a permalink anchor (or
     * omitted for the newest window) via {@see CommentListService::getLimited()},
     * sized to `$limit` (defaulting to `commentsPreviewMax` - the widget passes
     * the view-mode-aware size for the initial, non-anchored window, see
     * `widgets\Comments::getLimit()`).
     *
     * @throws ForbiddenHttpException if comments are hidden from the current guest
     */
    public function serializeWindow(
        ?int $commentId = null,
        ?string $direction = null,
        ?int $pageSize = null,
        ?int $limit = null,
    ): array {
        $this->assertGuestAllowed();

        $listService = new CommentListService($this->content, $this->parentComment);

        if ($direction === CommentListService::LIST_DIR_PREV || $direction === CommentListService::LIST_DIR_NEXT) {
            $comments = $listService->getSiblings(
                $commentId ?? 0,
                $this->clampPageSize($pageSize ?? $this->getModule()->commentsBlockLoadSize),
                $direction,
            );
        } else {
            $comments = $listService->getLimited($limit ?? $this->getModule()->commentsPreviewMax, $commentId);
        }

        $firstId = $comments[0]->id ?? null;
        $lastId = $comments[array_key_last($comments)]->id ?? null;

        // Every root's loaded child previews are fetched up front and folded into the SAME
        // batch as the roots, so the whole window - roots and previewed replies alike -
        // fires EVENT_SERIALIZE_COMMENTS exactly once (see its own docblock), instead of
        // once per root the way a naive per-comment implementation would.
        $childItemsByRoot = [];
        $allComments = $comments;
        foreach ($comments as $comment) {
            if ($comment->parent_comment_id === null) {
                $childItemsByRoot[$comment->id] = $this->getChildPreviewItems($comment);
                $allComments = array_merge($allComments, $childItemsByRoot[$comment->id]);
            }
        }

        $extensionData = $this->fireSerializeCommentsEvent($allComments);

        return [
            // Blocked-author reveal is only ever exposed per-comment (actionInfo's
            // showBlocked=1); a list window is never reachable with it from HTTP.
            'comments' => array_map(
                fn(Comment $c) => $this->serialize($c, false, $extensionData, $childItemsByRoot[$c->id] ?? null),
                $comments,
            ),
            'prevCount' => $firstId !== null ? $listService->getSiblingsCount($firstId, CommentListService::LIST_DIR_PREV) : 0,
            'nextCount' => $lastId !== null ? $listService->getSiblingsCount($lastId, CommentListService::LIST_DIR_NEXT) : 0,
            'total' => $listService->getCount(),
        ];
    }

    /**
     * Clamps to [1, commentsBlockLoadSize]. Guards against a zero/negative `$pageSize`
     * reaching {@see CommentListService::getSiblings()}: yii's QueryBuilder drops the
     * `LIMIT` clause entirely for non-positive values, which would otherwise fetch every
     * comment of the thread in one guest-reachable request.
     */
    public function clampPageSize(int $pageSize): int
    {
        return max(1, min($pageSize, $this->getModule()->commentsBlockLoadSize));
    }

    /**
     * @param array<int, array<string, array>> $extensionData commentId => namespace => data,
     *        as returned by {@see self::fireSerializeCommentsEvent()}
     * @param Comment[]|null $childItems this comment's already-fetched child previews (root
     *        comments only), so {@see self::serializeChildren()} does not re-query them -
     *        `null` lets it fetch them itself, for callers that never batched them up front
     */
    private function serialize(Comment $comment, bool $showBlocked, array $extensionData, ?array $childItems = null): array
    {
        $blocked = $this->isBlockedAuthor($comment, $showBlocked);
        $canDelete = $comment->canDelete();
        $extensions = $extensionData[$comment->id] ?? [];

        return [
            'id' => $comment->id,
            'contentId' => $comment->content_id,
            'parentCommentId' => $comment->parent_comment_id,
            'recordId' => RecordMap::getId($comment),
            'createdAt' => date(DATE_ATOM, strtotime($comment->created_at)),
            'isEdited' => $comment->isUpdated(),
            // Mirrors UpdatedIcon::getByDated($comment->updated_at)'s tooltip - only
            // meaningful once isEdited is true (same instant as createdAt otherwise).
            'updatedAt' => $comment->isUpdated() ? date(DATE_ATOM, strtotime($comment->updated_at)) : null,
            'author' => $blocked ? null : $this->serializeAuthor($comment->createdBy),
            'blocked' => $blocked,
            ...$this->serializeMessage($comment, $blocked),
            'attachmentsHtml' => $blocked ? null : ShowFiles::widget(['object' => $comment]),
            'likes' => $this->serializeLikes($comment),
            'canEdit' => $comment->canEdit(),
            'canDelete' => $canDelete,
            'canAdminDelete' => $canDelete && $comment->created_by !== Yii::$app->user->id,
            'permalink' => $comment->getUrl(true),
            'children' => $comment->parent_comment_id === null
                ? $this->serializeChildren($comment, $extensionData, $childItems)
                : null,
            // (object) forces `{}` on the wire for the common no-extension case, instead of
            // the `[]` an empty PHP array would otherwise serialize to - see
            // SerializeCommentsEvent's own docblock for the module-facing contract.
            'extensions' => $extensions === [] ? (object)[] : $extensions,
        ];
    }

    /**
     * Preview of direct child comments, one level deep (children of children are never
     * serialized: a child comment always gets `children: null`, see {@see self::serialize()}).
     *
     * The total uses `$comment->getChildCount()` rather than a fresh
     * `CommentListService::getCount()` query: the comment was fetched via a query that
     * already selects `child_count` as a subquery (see
     * `CommentListService::addScopeQueryCondition()`), so this is normally free - it only
     * falls back to its own query when `$comment` didn't come from such a query (e.g. a
     * just-created/updated comment returned from `actionCreate`/`actionUpdate`).
     *
     * @param array<int, array<string, array>> $extensionData see {@see self::serialize()}
     * @param Comment[]|null $items already-fetched child previews, see {@see self::serialize()}
     */
    private function serializeChildren(Comment $comment, array $extensionData, ?array $items = null): array
    {
        $items ??= $this->getChildPreviewItems($comment);
        $total = $comment->getChildCount();

        return [
            'total' => $total,
            'items' => array_map(fn(Comment $c) => $this->serialize($c, false, $extensionData), $items),
            'hasMore' => $total > count($items),
        ];
    }

    /**
     * @return Comment[]
     */
    private function getChildPreviewItems(Comment $comment): array
    {
        return (new CommentListService($this->content, $comment))->getLimited($this->getModule()->commentsPreviewMax);
    }

    /**
     * Fires {@see self::EVENT_SERIALIZE_COMMENTS} once for the given batch and collapses its
     * accumulated {@see SerializeCommentsEvent::addData()} calls into a plain `commentId =>
     * namespace => data` map for {@see self::serialize()} to read from.
     *
     * `CommentJsonService` instances are plain `new self(...)` objects (see
     * {@see self::create()}), not {@see \yii\base\Component} instances, so there is no
     * instance-level `$this->trigger()` to call here. The static {@see Event::trigger()}
     * still fires every handler attached via `Event::on(CommentJsonService::class,
     * self::EVENT_SERIALIZE_COMMENTS, ...)` regardless: class-level event handlers are
     * looked up by class name at trigger time, independently of how (or whether) any
     * particular instance was constructed.
     *
     * @param Comment[] $comments
     * @return array<int, array<string, array>> commentId => namespace => data
     */
    private function fireSerializeCommentsEvent(array $comments): array
    {
        $event = new SerializeCommentsEvent();
        $event->comments = $comments;
        Event::trigger(self::class, self::EVENT_SERIALIZE_COMMENTS, $event);

        $data = [];
        foreach ($comments as $comment) {
            $data[$comment->id] = $event->getData($comment->id);
        }

        return $data;
    }

    /**
     * `message`/`messageRenderOptions`: raw markdown plus the client-render options needed to
     * reproduce, entirely client-side, the exact envelope `RichText::output()`'s HTML string used
     * to carry - see `RichText::outputMarkdownAndRenderOptions()` and
     * `docs/develop/ui-js-vuejs-interop.md`, "RichTextOutput". Both null when the author is
     * blocked, same masking as every other message-derived field.
     *
     * @return array{message: string|null, messageRenderOptions: array|null}
     */
    private function serializeMessage(Comment $comment, bool $blocked): array
    {
        if ($blocked) {
            return ['message' => null, 'messageRenderOptions' => null];
        }

        $result = RichText::outputMarkdownAndRenderOptions($comment->message, ['record' => $comment]);

        return ['message' => $result['markdown'], 'messageRenderOptions' => (object)$result['options']];
    }

    /**
     * Mirrors the parts of `user\widgets\Image::run()` and `Html::containerLink()` the
     * island's avatar/author-link need for popover-card and online-status parity:
     * `contentContainerId` drives the user popover card on both the avatar and the name
     * link (`data-contentcontainer-id`), `guid` (already present) additionally goes on
     * the name link (`data-guid` - `Html::containerLink()` sets both), and `imageAlt` is
     * the exact `Image::run()` alt text so the avatar's accessible name matches legacy.
     */
    private function serializeAuthor(User $user): array
    {
        return [
            'guid' => $user->guid,
            'displayName' => $user->displayName,
            'url' => $user->getUrl(),
            'imageUrl' => $user->getProfileImage()->getUrl(),
            'contentContainerId' => $user->contentcontainer_id,
            'imageAlt' => Yii::t('base', 'Profile picture of {displayName}', ['displayName' => $user->displayName]),
            'online' => $this->serializeOnlineStatus($user),
        ];
    }

    /**
     * Mirrors `user\widgets\Image::run()`'s online-status gate (with the widget's
     * defaults, `hideOnlineStatus`/`showSelfOnlineStatus` both false, as used by the
     * legacy `comment.php` view): `null` when the viewer is looking at their own
     * comment or the feature is disabled (hidden by admin/user setting), a real status
     * otherwise - `CommentEntry.vue` only renders the online-status overlay when this
     * is non-null.
     */
    private function serializeOnlineStatus(User $user): ?bool
    {
        if ($user->id === Yii::$app->user->id) {
            return null;
        }

        $isOnlineService = new IsOnlineService($user);

        return $isOnlineService->isEnabled() ? $isOnlineService->getStatus() : null;
    }

    /**
     * Initial like props for the comment's `<LikeButton>`: `null` when likes aren't
     * available to the current viewer/content instead of a zeroed-out shape.
     *
     * Note: `LikeService::canLike()` already returns `false` for guests (no identity), so
     * a `guestHideComments` guest never reaches this: `assertGuestAllowed()` rejects the
     * request before serialization starts.
     */
    private function serializeLikes(Comment $comment): ?array
    {
        $likeService = new LikeService($comment);

        if (!$likeService->canLike()) {
            return null;
        }

        return [
            'count' => $likeService->getCount(),
            'liked' => $likeService->hasLiked(),
        ];
    }

    /**
     * Mirrors `widgets\Comment::isBlockedAuthor()`.
     */
    private function isBlockedAuthor(Comment $comment, bool $showBlocked): bool
    {
        if ($showBlocked || Yii::$app->user->isGuest) {
            return false;
        }

        return Yii::$app->user->getIdentity()->isBlockedForUser($comment->createdBy);
    }

    /**
     * Mirrors the guest gate in `widgets\Comments::run()`. Unlike the widget (which just
     * renders nothing), the JSON API rejects the request so it cannot be used to bypass
     * the setting.
     *
     * @throws ForbiddenHttpException
     */
    private function assertGuestAllowed(): void
    {
        if (Yii::$app->user->isGuest && $this->getModule()->guestHideComments) {
            throw new ForbiddenHttpException();
        }
    }

    private function getModule(): Module
    {
        return Yii::$app->getModule('comment');
    }
}
