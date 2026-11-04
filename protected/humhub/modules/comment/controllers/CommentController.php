<?php

namespace humhub\modules\comment\controllers;

use humhub\components\Controller;
use humhub\modules\comment\widgets\Comments;
use humhub\modules\content\models\Content;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Serves the one UI route of the comment section that is not part of the platform's HTTP API:
 * the comment form's props (`form`).
 * All JSON read/write traffic of the comment Vue island goes through the platform's
 * HTTP API ({@see \humhub\modules\comment\controllers\api\CommentController}) — the
 * former JSON actions of this controller were removed with that switch, and the delete
 * dialogs (plain confirm AND the admin notify/reason mode) are a native Vue modal now
 * (`CommentDeleteModal.vue`), so the former `get-admin-delete-modal` action is gone too.
 * The comments popup (`CommentLink::MODE_POPUP`) mounts the island client-side as well
 * (`comment.showComments` in humhub.comment.js), so the former `show` action is gone.
 */
class CommentController extends Controller
{
    private ?Content $content = null;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $contentId = (int)Yii::$app->request->get('contentId');
        $this->content = $contentId ? Content::findOne(['id' => $contentId]) : null;

        if (!$this->content) {
            throw new NotFoundHttpException();
        }

        if (!$this->content->canView()) {
            throw new ForbiddenHttpException();
        }

        return true;
    }

    /**
     * The comment form's props for a content (see {@see Comments::formProps()}), for a
     * `CommentSection` island mounted by `contentId` alone - e.g. in the comments popup or a
     * file's details pane -, which loads its comments through the HTTP API but has no widget
     * to hand it the form.
     *
     * Deliberately a UI route and not part of `/api/v2`: the form is server-rendered markup
     * (the legacy richtext editor's shell, the file handlers' menu entries), which has no
     * place in the API other clients use.
     */
    public function actionForm()
    {
        return $this->asJson(Comments::formProps($this->content));
    }
}
