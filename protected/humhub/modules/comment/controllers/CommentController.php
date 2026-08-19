<?php

namespace humhub\modules\comment\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\comment\models\AdminDeleteCommentForm;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\notifications\CommentDeleted;
use humhub\modules\comment\services\CommentJsonService;
use humhub\modules\comment\widgets\AdminDeleteModal;
use humhub\modules\content\models\Content;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class CommentController extends Controller
{
    public ?Comment $comment = null;
    private ?Content $content = null;

    public ?Comment $parentComment = null;

    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['delete', 'create', 'update']],
            [ControllerAccess::RULE_POST => ['create']],
            [ControllerAccess::RULE_JSON => ['list', 'create', 'update', 'info']],
        ];
    }

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
     * The comment island itself now owns listing/pagination via the JSON API, so this only
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

    /**
     * Returns a window of comments (cursor pagination, or an anchored permalink window
     * when no `direction` is given) as JSON.
     *
     * @see CommentJsonService::serializeWindow()
     * @since 1.19
     */
    public function actionList()
    {
        $direction = Yii::$app->request->get('direction');
        $commentId = Yii::$app->request->get('commentId');
        $pageSize = Yii::$app->request->get('pageSize');

        $service = CommentJsonService::create($this->parentComment ?? $this->content);

        return $this->asJson($service->serializeWindow(
            $commentId !== null ? (int)$commentId : null,
            $direction,
            $pageSize !== null ? (int)$pageSize : null,
        ));
    }

    /**
     * Creates a comment from a JSON payload (`message`, `fileList`, `parentCommentId`).
     * Enforces at most one nesting level server-side, unlike the legacy `actionPost` form
     * which only enforced this in JS.
     *
     * @since 1.19
     */
    public function actionCreate()
    {
        if (!$this->module->canComment($this->content)) {
            throw new ForbiddenHttpException();
        }

        if ($this->parentComment !== null && $this->parentComment->parent_comment_id !== null) {
            Yii::$app->response->statusCode = 422;

            return $this->asJson([
                'errors' => [
                    'parentCommentId' => [Yii::t('CommentModule.base', 'Comments can only be nested one level deep.')],
                ],
            ]);
        }

        $model = new Comment();
        $model->content_id = $this->content->id;
        $model->parent_comment_id = $this->parentComment?->id;

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return $this->asJson(CommentJsonService::create($model)->serializeComment($model));
        }

        Yii::$app->response->statusCode = 422;

        return $this->asJson(['errors' => $model->errors]);
    }

    /**
     * GET returns the raw markdown message of an editable comment for the editor.
     * POST saves the comment from a JSON payload (`message`, `fileList`) and returns the
     * updated comment JSON.
     *
     * @since 1.19
     */
    public function actionUpdate()
    {
        if ($this->comment === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->comment->canEdit()) {
            throw new ForbiddenHttpException();
        }

        if (!Yii::$app->request->isPost) {
            return $this->asJson(['message' => $this->comment->message]);
        }

        if ($this->comment->load(Yii::$app->request->post(), '') && $this->comment->save()) {
            return $this->asJson(CommentJsonService::create($this->comment)->serializeComment($this->comment));
        }

        Yii::$app->response->statusCode = 422;

        return $this->asJson(['errors' => $this->comment->errors]);
    }

    /**
     * Returns a single comment as JSON. `showBlocked=1` lifts the blocked-author mask only
     * (guest/`guestHideComments` visibility still applies).
     *
     * @since 1.19
     */
    public function actionInfo()
    {
        if ($this->comment === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->comment->canView()) {
            throw new ForbiddenHttpException();
        }

        $showBlocked = (bool)Yii::$app->request->get('showBlocked');

        return $this->asJson(
            CommentJsonService::create($this->comment)->serializeComment($this->comment, $showBlocked),
        );
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        if ($this->comment === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->comment->canDelete()) {
            throw new ForbiddenHttpException();
        }

        $form = new AdminDeleteCommentForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if (!$form->validate()) {
                throw new BadRequestHttpException();
            }

            if ($form->notify) {
                $commentDeleted = CommentDeleted::instance()
                    ->from(Yii::$app->user->getIdentity())
                    ->about($this->comment->content->getPolymorphicRelation())
                    ->payload(
                        [
                            'commentText' => (new CommentDeleted())->getContentPreview($this->comment, 30),
                            'reason' => $form->message,
                        ],
                    );
                $commentDeleted->saveRecord($this->comment->createdBy);

                $commentDeleted->record->updateAttributes([
                    'send_web_notifications' => 1,
                ]);
            }
        }

        return $this->asJson(['success' => $this->comment->delete()]);
    }

    public function actionGetAdminDeleteModal($id)
    {
        Yii::$app->response->format = 'json';

        if ($this->comment === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->comment->canDelete()) {
            throw new ForbiddenHttpException();
        }

        return [
            'header' => Yii::t('CommentModule.base', '<strong>Delete</strong> comment?'),
            'body' => AdminDeleteModal::widget([
                'model' => new AdminDeleteCommentForm(),
            ]),
            'confirmText' => Yii::t('CommentModule.base', 'Confirm'),
            'cancelText' => Yii::t('CommentModule.base', 'Cancel'),
        ];
    }
}
