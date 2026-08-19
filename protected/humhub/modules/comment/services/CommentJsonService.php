<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\services;

use humhub\models\RecordMap;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\Module;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\file\widgets\ShowFiles;
use humhub\modules\like\services\LikeService;
use humhub\modules\user\models\User;
use Yii;
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

        return $this->serialize($comment, $showBlocked);
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

        return [
            // Blocked-author reveal is only ever exposed per-comment (actionInfo's
            // showBlocked=1); a list window is never reachable with it from HTTP.
            'comments' => array_map(fn(Comment $c) => $this->serialize($c, false), $comments),
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

    private function serialize(Comment $comment, bool $showBlocked): array
    {
        $blocked = $this->isBlockedAuthor($comment, $showBlocked);
        $canDelete = $comment->canDelete();

        return [
            'id' => $comment->id,
            'contentId' => $comment->content_id,
            'parentCommentId' => $comment->parent_comment_id,
            'recordId' => RecordMap::getId($comment),
            'createdAt' => date(DATE_ATOM, strtotime($comment->created_at)),
            'isEdited' => $comment->isUpdated(),
            'author' => $blocked ? null : $this->serializeAuthor($comment->createdBy),
            'blocked' => $blocked,
            'messageOutput' => $blocked ? null : RichText::output($comment->message, ['record' => $comment]),
            'attachmentsHtml' => $blocked ? null : ShowFiles::widget(['object' => $comment]),
            'likes' => $this->serializeLikes($comment),
            'canEdit' => $comment->canEdit(),
            'canDelete' => $canDelete,
            'canAdminDelete' => $canDelete && $comment->created_by !== Yii::$app->user->id,
            'permalink' => $comment->getUrl(true),
            'children' => $comment->parent_comment_id === null ? $this->serializeChildren($comment) : null,
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
     */
    private function serializeChildren(Comment $comment): array
    {
        $items = (new CommentListService($this->content, $comment))->getLimited($this->getModule()->commentsPreviewMax);
        $total = $comment->getChildCount();

        return [
            'total' => $total,
            'items' => array_map(fn(Comment $c) => $this->serialize($c, false), $items),
            'hasMore' => $total > count($items),
        ];
    }

    private function serializeAuthor(User $user): array
    {
        return [
            'guid' => $user->guid,
            'displayName' => $user->displayName,
            'url' => $user->getUrl(),
            'imageUrl' => $user->getProfileImage()->getUrl(),
        ];
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
