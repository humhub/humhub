<?php

namespace humhub\modules\comment\controllers;

use humhub\components\Controller;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Serves the one remaining HTML route of the comment UI (the `show` popup mode).
 * All JSON read/write traffic of the comment Vue island goes through the platform's
 * HTTP API ({@see \humhub\modules\comment\controllers\api\CommentController}) — the
 * former JSON actions of this controller were removed with that switch, and the delete
 * dialogs (plain confirm AND the admin notify/reason mode) are a native Vue modal now
 * (`CommentDeleteModal.vue`), so the former `get-admin-delete-modal` action is gone too.
 */
class CommentController extends Controller
{
    public ?Comment $comment = null;
    private ?Content $content = null;

    public ?Comment $parentComment = null;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $commentId = (int)Yii::$app->request->get('id', Yii::$app->request->post('id'));
        $parentCommentId = (int)Yii::$app->request->get(
            'parentCommentId',
            Yii::$app->request->post('parentCommentId'),
        );
        $contentId = (int)Yii::$app->request->get('contentId', Yii::$app->request->post('contentId'));

        if ($commentId) {
            $this->comment = Comment::findOne(['id' => $commentId]);
            $this->content = $this->comment?->content;
            $this->parentComment = $this->comment?->parentComment;
        } elseif ($parentCommentId) {
            $this->parentComment = Comment::findOne(['id' => $parentCommentId]);
            $this->content = $this->parentComment?->content;
        } elseif ($contentId) {
            $this->content = Content::findOne(['id' => $contentId]);
        }

        if (!$this->content) {
            throw new NotFoundHttpException();
        }

        if (!$this->content->canView()) {
            throw new ForbiddenHttpException();
        }

        return true;
    }

    /**
     * Renders the comment section (see {@see \humhub\modules\comment\widgets\Comments}) inside
     * a modal - the only remaining mode of this action, used by
     * {@see \humhub\modules\content\widgets\ContentObjectLinks}'s `CommentLink::MODE_POPUP`.
     * The comment island itself owns listing/pagination via the REST API, so this only
     * needs to set up the modal chrome around it. `renderAjax()` (no site layout, but still
     * the ajax asset/CSRF envelope - see `humhub\components\View::renderAjax()`) is what lets
     * the island's own asset bundle flow with this response, same as before.
     */
    public function actionShow()
    {
        if (Yii::$app->request->get('mode') !== 'popup') {
            throw new NotFoundHttpException();
        }

        return $this->renderAjax('showPopup', [
            'content' => $this->content,
            'parentComment' => $this->parentComment,
        ]);
    }
}
