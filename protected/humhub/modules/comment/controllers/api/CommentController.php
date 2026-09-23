<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\Module;
use humhub\modules\comment\serializers\CommentSerializer;
use humhub\modules\comment\services\CommentDeleteService;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\comment\services\CommentPayloadCache;
use humhub\modules\content\models\Content;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The comment API (see `docs/develop/concept-api.md`), consumed by the comment Vue island
 * and available to any API client.
 *
 * Reads come as cursor-paginated windows rather than offset pages: comment lists grow while
 * a client pages through them, so offsets would duplicate and skip rows, and each response
 * carries the exact remaining counts a "show previous/next N comments" UI needs. A window is
 * addressed by `cursor` + `direction` (paging from a comment), by `focus` (the window around
 * a permalinked comment) or by nothing (the newest comments), and sized by `limit` — see
 * {@see self::window()}; {@see CommentSerializer::window()} has the count semantics.
 *
 * Writes follow the API's parameter rule: what a `POST` creates travels in the body
 * (`contentId`, `parentCommentId`, `message`, `fileList`), what a `DELETE` is told travels in
 * the query string (`notify`, `message`). The one update verb is `PATCH`, and it is partial.
 *
 * Guests may read windows and single comments of guest-visible content while guest access is
 * enabled platform-wide, subject to the comment module's `guestHideComments` setting.
 * Mutations are authenticated-only.
 *
 * @since 1.20
 */
class CommentController extends BaseController
{
    /**
     * @inheritdoc
     *
     * This is core UI's own endpoint, so it accepts the browser session (a module may add
     * token methods on top, see {@see BaseController::EVENT_COLLECT_AUTH_METHODS}).
     */
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    protected array $guestAllowedActions = ['window-by-content', 'window-by-parent', 'view'];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'window-by-content' => ['GET', 'HEAD'],
                    'window-by-parent' => ['GET', 'HEAD'],
                    'view' => ['GET', 'HEAD'],
                    'permissions' => ['GET', 'HEAD'],
                    'create' => ['POST'],
                    'update' => ['PATCH'],
                    'delete' => ['DELETE'],
                ],
            ],
        ]);
    }

    /**
     * Root-comment window of a content.
     */
    public function actionWindowByContent($id)
    {
        return $this->window($this->findContent((int)$id), null);
    }

    /**
     * Reply window of one comment thread.
     */
    public function actionWindowByParent($id)
    {
        $parentComment = Comment::findOne(['id' => (int)$id]);

        if ($parentComment === null) {
            throw new NotFoundHttpException();
        }

        return $this->window($this->assertViewable($parentComment->content), $parentComment);
    }

    /**
     * A single comment.
     */
    public function actionView($id)
    {
        $comment = $this->findComment((int)$id);
        $this->assertViewable($comment->content);
        $this->assertGuestCommentsAllowed();

        // Cached: a live update has every client with this thread open fetch the same comment.
        // Authorization happened above, on this request — see CommentPayloadCache.
        return CommentPayloadCache::comment($comment);
    }

    /**
     * What the AUTHENTICATED CALLER may do with one comment.
     *
     * Deliberately not part of the comment shape: `canEdit`/`canDelete` are the only reason a
     * comment payload would have to be built per caller (see
     * `docs/develop/concept-api.md`), and they are needed at exactly one moment - when the
     * entry's context menu opens. A client fetches them then, rather than every window
     * carrying them for every comment.
     *
     * The same methods the mutating actions enforce, so a menu built from this can never
     * offer more than the endpoint allows.
     */
    public function actionPermissions($id)
    {
        $comment = $this->findComment((int)$id);
        $this->assertViewable($comment->content);
        $this->assertGuestCommentsAllowed();

        return [
            'canEdit' => $comment->canEdit(),
            'canDelete' => $comment->canDelete(),
        ];
    }

    /**
     * Creates a comment on the content named by `contentId`, from `message` and an optional
     * `fileList` of uploaded file guids — all in the body. Replies pass the parent through
     * `parentCommentId`; the model enforces that comments nest at most one level.
     *
     * Answers `201` with the created comment.
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $contentId = (int)$request->getBodyParam('contentId');

        if ($contentId <= 0) {
            return $this->missingParameter('contentId');
        }

        $content = $this->findContent($contentId);

        if (!$this->getCommentModule()->canComment($content)) {
            throw new ForbiddenHttpException();
        }

        $comment = new Comment();
        $comment->content_id = $content->id;
        // Passed through raw so the model validates it (same content, root comment) instead
        // of silently creating a root comment for a bogus parent id.
        $comment->parent_comment_id = (int)$request->getBodyParam('parentCommentId') ?: null;

        // load() returns false for an empty body — save() must still run so the response
        // carries the `required` errors instead of an empty error map.
        $comment->load($request->getBodyParams(), '');

        if (!$comment->save()) {
            return $this->validationErrors($comment);
        }

        Yii::$app->response->setStatusCode(201);

        return CommentSerializer::comment($comment);
    }

    /**
     * Updates a comment's `message` and/or `fileList` — partial: a field the body does not
     * carry keeps its value.
     */
    public function actionUpdate($id)
    {
        $comment = $this->findComment((int)$id);

        if (!$comment->canEdit()) {
            throw new ForbiddenHttpException();
        }

        $comment->load(Yii::$app->request->getBodyParams(), '');

        if (!$comment->save()) {
            return $this->validationErrors($comment);
        }

        return CommentSerializer::comment($comment);
    }

    /**
     * Deletes a comment. The optional `notify` / `message` query parameters trigger the
     * moderation flow: the author receives a notification carrying a preview of the removed
     * text and the given reason (see {@see CommentDeleteService}). Query rather than body,
     * because a `DELETE` body is dropped by enough clients and proxies not to build on.
     *
     * Answers `204 No Content` — there is nothing left to represent.
     */
    public function actionDelete($id)
    {
        $comment = $this->findComment((int)$id);

        if (!$comment->canDelete()) {
            throw new ForbiddenHttpException();
        }

        $request = Yii::$app->request;
        $deleted = (new CommentDeleteService($comment))->delete(
            (bool)$request->get('notify', false),
            (string)$request->get('message', ''),
        );

        if (!$deleted) {
            throw new \yii\web\ServerErrorHttpException('The comment could not be deleted.');
        }

        Yii::$app->response->setStatusCode(204);

        return null;
    }

    /**
     * One window, from the request's `cursor`, `focus`, `direction` and `limit`:
     *
     * - `cursor` + `direction` (`previous`/`next`) page from a comment the client already
     *   has;
     * - `focus` alone centres the window on a comment (a permalink);
     * - neither answers the newest comments.
     *
     * `limit` is the size of whichever window that is, clamped to the module's block load
     * size — the serializer trusts its in-process callers (the comment widget embeds a larger
     * initial window), so the clamp belongs here.
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function window(Content $content, ?Comment $parentComment): array
    {
        $this->assertGuestCommentsAllowed();

        $request = Yii::$app->request;
        $direction = $request->get('direction');
        $isPaging = $direction === CommentListService::LIST_DIR_PREV || $direction === CommentListService::LIST_DIR_NEXT;
        $anchor = $request->get($isPaging ? 'cursor' : 'focus');
        $limit = $request->get('limit');
        $limit = $limit !== null ? max(1, min((int)$limit, $this->getCommentModule()->commentsBlockLoadSize)) : null;

        return CommentPayloadCache::window(
            $content,
            $parentComment,
            $anchor !== null ? (int)$anchor : null,
            $isPaging ? $direction : null,
            $limit,
            $limit,
        );
    }

    protected function findComment(int $id): Comment
    {
        $comment = Comment::findOne(['id' => $id]);

        if ($comment === null) {
            throw new NotFoundHttpException();
        }

        return $comment;
    }

    protected function findContent(int $id): Content
    {
        $content = Content::findOne(['id' => $id]);

        if ($content === null) {
            throw new NotFoundHttpException();
        }

        return $this->assertViewable($content);
    }

    /**
     * @throws ForbiddenHttpException
     */
    protected function assertViewable(?Content $content): Content
    {
        if ($content === null) {
            throw new NotFoundHttpException();
        }

        if (!$content->canView()) {
            throw new ForbiddenHttpException();
        }

        return $content;
    }

    /**
     * The comment module's `guestHideComments` setting hides all comments from
     * non-authenticated visitors — the same gate the web UI enforces.
     *
     * @throws ForbiddenHttpException
     */
    protected function assertGuestCommentsAllowed(): void
    {
        if (Yii::$app->user->isGuest && $this->getCommentModule()->guestHideComments) {
            throw new ForbiddenHttpException();
        }
    }

    protected function getCommentModule(): Module
    {
        return Yii::$app->getModule('comment');
    }
}
