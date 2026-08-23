<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\serializers;

use humhub\components\api\Format;
use humhub\components\api\SerializeEvent;
use humhub\models\RecordMap;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\Module;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\file\serializers\FileSerializer;
use humhub\modules\user\serializers\UserSerializer;
use Yii;

/**
 * Serializes {@see Comment} records and comment windows for the HTTP API (see
 * `docs/develop/concept-api.md`).
 *
 * Conventions of the current API version: camelCase field names, ISO-8601 timestamps with
 * offset, no rendered HTML.
 *
 * **The shape is caller-neutral**: every field is identical for everyone who may read the
 * content, which is what allows one serialization to be cached and served to all of them (see
 * `docs/develop/concept-api.md`). Anything that depends on WHO is asking lives elsewhere:
 *
 * - like state (`total`/`liked`/`canLike`) — batched for a whole window by
 *   `GET like/states`, and per record × per caller the only such value there is.
 * - `canEdit`/`canDelete` — `GET comment/<id>/permissions`, fetched when the entry's context
 *   menu opens, which is the only place they are used.
 * - the author's online status — presence is volatile and per viewer; a client resolves it
 *   separately.
 *
 * Deliberately NOT anywhere, because a client derives it from what is here:
 *
 * - "edited" — compare `updatedAt` with `createdAt`.
 * - "may moderate this comment" — `canDelete` (from the permissions call) on a comment whose
 *   author is not the caller.
 * - blocked-author masking — a client masks authors on its own block list (which it can read
 *   from the API), so the same payload serves a masked and an unmasked view without a second
 *   request. Masking is a display concern, never an access control one.
 *
 * The same rule binds {@see SerializeEvent} handlers: data a module attaches to `extensions`
 * must be caller-neutral too, or the cached payload would be wrong for the next reader.
 * Caller-specific module state belongs in that module's own endpoint.
 *
 * {@see SerializeEvent} fires once per response batch (a window's roots plus their loaded
 * reply previews in one firing), so modules can attach namespaced `extensions` data without
 * running a query per comment.
 *
 * @since 1.19
 */
class CommentSerializer
{
    /**
     * Serializes one comment.
     *
     * @param array<int, array<string, array>>|null $extensionData recordId => namespace => data
     *        from {@see SerializeEvent::collectFor()} — pass it when the caller already
     *        collected a batch; `null` fires the event for this comment (plus its reply
     *        previews when it is a root comment).
     * @param Comment[]|null $replyPreview this root comment's already-fetched reply previews,
     *        so {@see self::replies()} does not query them again
     */
    public static function comment(Comment $comment, ?array $extensionData = null, ?array $replyPreview = null): array
    {
        if ($comment->parent_comment_id === null) {
            $replyPreview ??= static::replyPreviewItems($comment);
        }

        $extensionData ??= SerializeEvent::collectFor(
            Comment::class,
            array_merge([$comment], $replyPreview ?? []),
        );

        // Raw markdown plus the options a client needs to reproduce the platform's rich-text
        // rendering (mentions, oembeds, …) — see RichText::outputMarkdownAndRenderOptions()
        // and docs/develop/ui-js-vuejs-interop.md.
        $rendered = RichText::outputMarkdownAndRenderOptions($comment->message, ['record' => $comment]);
        $extensions = $extensionData[$comment->id] ?? [];

        return [
            'id' => $comment->id,
            'message' => $rendered['markdown'],
            // (object) so an empty option set serializes as `{}` rather than `[]`.
            'messageRenderOptions' => (object)$rendered['options'],
            'contentId' => $comment->content_id,
            'parentCommentId' => $comment->parent_comment_id,
            // Platform-wide record id — the addressing the like endpoints accept.
            'recordId' => RecordMap::getId($comment),
            'createdBy' => UserSerializer::short($comment->createdBy),
            'createdAt' => Format::dateTime($comment->created_at),
            'updatedAt' => Format::dateTime($comment->updated_at),
            'url' => $comment->getUrl(true),
            'files' => FileSerializer::forRecord($comment),
            'childCount' => $comment->getChildCount(),
            'replies' => $comment->parent_comment_id === null
                ? static::replies($comment, $extensionData, $replyPreview)
                : null,
            // (object) so "nothing attached" serializes as `{}` rather than `[]`.
            'extensions' => $extensions === [] ? (object)[] : $extensions,
        ];
    }

    /**
     * The record ids of every comment in a serialized window, reply previews included.
     *
     * What a caller needs to ask for the window's like states in one request (see
     * {@see \humhub\modules\like\serializers\LikeSerializer::statesByRecordId()}) - kept
     * here so the shape's structure stays known in exactly one place.
     *
     * @param array $window as returned by {@see self::window()}
     * @return int[]
     */
    public static function recordIds(array $window): array
    {
        $recordIds = [];

        foreach ($window['results'] ?? [] as $comment) {
            $recordIds[] = (int)$comment['recordId'];

            foreach ($comment['replies']['items'] ?? [] as $reply) {
                $recordIds[] = (int)$reply['recordId'];
            }
        }

        return array_values(array_unique($recordIds));
    }

    /**
     * A cursor- or anchor-based window of sibling comments — the read model of the comment
     * window endpoints.
     *
     * A `$direction` of `previous`/`next` pages from `$commentId` (real "show more"
     * pagination, `$pageSize` clamped to the module's block load size). Without a direction,
     * `$commentId` is treated as a permalink anchor — the window is focused around it — or,
     * without any cursor, the newest `$limit` comments are returned.
     *
     * `total` counts ALL comments of the scope INCLUDING replies (what a comment-count badge
     * shows), while `results`, `prevCount` and `nextCount` cover the window's own level only.
     * `rootTotal` is the root-only complement a root-level list needs to compute its
     * remaining count — without it, a thread with replies would produce a "show more" link
     * that leads nowhere.
     *
     * @return array{results: array[], total: int, rootTotal: int, prevCount: int, nextCount: int}
     */
    public static function window(
        Content $content,
        ?Comment $parentComment = null,
        ?int $commentId = null,
        ?string $direction = null,
        ?int $pageSize = null,
        ?int $limit = null,
    ): array {
        $module = static::getModule();
        $listService = new CommentListService($content, $parentComment);

        if ($direction === CommentListService::LIST_DIR_PREV || $direction === CommentListService::LIST_DIR_NEXT) {
            // The clamp guards against a zero or negative page size reaching the query
            // builder, which drops the LIMIT clause entirely for non-positive values and
            // would fetch the whole thread in one request.
            $comments = $listService->getSiblings(
                $commentId ?? 0,
                max(1, min($pageSize ?? $module->commentsBlockLoadSize, $module->commentsBlockLoadSize)),
                $direction,
            );
        } else {
            // No clamp here: this serializer also serves trusted in-process callers (the
            // comment widget embedding a view-mode-sized initial window). Clamping
            // client-supplied sizes is the endpoint's job.
            $comments = $listService->getLimited($limit ?? $module->commentsPreviewMax, $commentId);
        }

        $firstId = $comments[0]->id ?? null;
        $lastId = $comments[array_key_last($comments)]->id ?? null;

        // Every root's reply previews are fetched up front and folded into the SAME batch as
        // the roots, so the whole window fires SerializeEvent exactly once.
        $previewByRoot = [];
        $batch = $comments;
        foreach ($comments as $comment) {
            if ($comment->parent_comment_id === null) {
                $previewByRoot[$comment->id] = static::replyPreviewItems($comment);
                $batch = array_merge($batch, $previewByRoot[$comment->id]);
            }
        }

        $extensionData = SerializeEvent::collectFor(Comment::class, $batch);

        return [
            'results' => array_map(
                fn(Comment $c) => static::comment($c, $extensionData, $previewByRoot[$c->id] ?? null),
                $comments,
            ),
            'total' => $listService->getCount(),
            'rootTotal' => $listService->getRootCount(),
            'prevCount' => $firstId !== null
                ? $listService->getSiblingsCount($firstId, CommentListService::LIST_DIR_PREV) : 0,
            'nextCount' => $lastId !== null
                ? $listService->getSiblingsCount($lastId, CommentListService::LIST_DIR_NEXT) : 0,
        ];
    }

    /**
     * Preview of a root comment's newest direct replies, one level deep (comments nest at
     * most one level, enforced by {@see Comment::validateParentComment()}, so a reply's own
     * `replies` is always `null`).
     *
     * @param array<int, array<string, array>> $extensionData see {@see self::comment()}
     * @param Comment[]|null $items already-fetched previews, see {@see self::comment()}
     */
    protected static function replies(Comment $comment, array $extensionData, ?array $items = null): array
    {
        $items ??= static::replyPreviewItems($comment);
        $total = $comment->getChildCount();

        return [
            'total' => $total,
            'items' => array_map(fn(Comment $c) => static::comment($c, $extensionData), $items),
            'hasMore' => $total > count($items),
        ];
    }

    /**
     * @return Comment[]
     */
    protected static function replyPreviewItems(Comment $comment): array
    {
        return (new CommentListService($comment->content, $comment))
            ->getLimited(static::getModule()->commentsPreviewMax);
    }

    protected static function getModule(): Module
    {
        return Yii::$app->getModule('comment');
    }
}
