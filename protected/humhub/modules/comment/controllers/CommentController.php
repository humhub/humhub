<?php

namespace humhub\modules\comment\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\comment\helpers\IdHelper;
use humhub\modules\comment\models\AdminDeleteCommentForm;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\notifications\CommentDeleted;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\comment\widgets\AdminDeleteModal;
use humhub\modules\comment\widgets\Comment as CommentWidget;
use humhub\modules\comment\widgets\Form;
use humhub\modules\comment\widgets\ShowMore;
use humhub\modules\content\models\Content;
use humhub\modules\file\handler\FileHandlerCollection;
use Yii;
use yii\helpers\Url;
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
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['post', 'edit', 'delete']],
            [ControllerAccess::RULE_POST => ['post']],
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
            Yii::$app->request->post('parentCommentId')
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

    public function actionShow()
    {
        $commentId = (int)Yii::$app->request->get('commentId');
        $direction = Yii::$app->request->get('direction', CommentListService::LIST_DIR_PREV);
        $pageSize = (int)Yii::$app->request->get('pageSize', $this->module->commentsBlockLoadSize);
        if ($pageSize > $this->module->commentsBlockLoadSize) {
            $pageSize = $this->module->commentsBlockLoadSize;
        }

        $comments = (new CommentListService($this->content, $this->parentComment))->getSiblings(
            $commentId,
            $pageSize,
            $direction,
        );

        $output = '';
        if ($direction === CommentListService::LIST_DIR_PREV) {
            $output .= ShowMore::widget([
                'content' => $this->content,
                'parentComment' => $this->parentComment,
                'pageSize' => $pageSize,
                'commentId' => isset($comments[0]) ? $comments[0]->id : null,
                'direction' => $direction,
            ]);
        }
        foreach ($comments as $comment) {
            $output .= CommentWidget::widget(['comment' => $comment]);
        }
        if ($direction === CommentListService::LIST_DIR_NEXT && count($comments) > 1) {
            $output .= ShowMore::widget([
                'content' => $this->content,
                'parentComment' => $this->parentComment,
                'pageSize' => $pageSize,
                'commentId' => $comments[count($comments) - 1]->id,
                'direction' => $direction,
            ]);
        }

        if (Yii::$app->request->get('mode') === 'popup') {
            return $this->renderAjax(
                'showPopup',
                [
                    'content' => $this->content,
                    'output' => $output,
                    'id' => IdHelper::getId($this->content, $this->parentComment)
                ]
            );
        } else {
            return $this->renderAjaxContent($output);
        }
    }

    public function actionPost()
    {
        if (!$this->module->canComment($this->content)) {
            throw new ForbiddenHttpException();
        }

        $model = new Comment();
        $model->content_id = $this->content->id;
        $model->parent_comment_id = $this->parentComment?->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->renderAjaxContent(CommentWidget::widget(['comment' => $model]));
        }

        Yii::$app->response->statusCode = 400;

        return $this->renderAjaxContent(Form::widget([
            'content' => $this->content,
            'parentComment' => $this->parentComment,
            'model' => $model,
            'isHidden' => false,
        ]));
    }


    public function actionEdit()
    {
        if ($this->comment === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->comment->canEdit()) {
            throw new ForbiddenHttpException();
        }

        if ($this->comment->load(Yii::$app->request->post()) && $this->comment->save()) {
            return $this->renderAjaxContent(CommentWidget::widget([
                'comment' => $this->comment,
                'justEdited' => true,
            ]));
        }

        if (Yii::$app->request->post()) {
            Yii::$app->response->statusCode = 400;
        }

        return $this->renderAjax('edit', [
            'comment' => $this->comment,
            'submitUrl' => Url::to(['/comment/comment/edit', 'id' => $this->comment->id]),
            'fileHandlers' => FileHandlerCollection::getByType(
                [FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE]
            ),
        ]);
    }

    public function actionLoad()
    {
        if ($this->comment === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->comment->canView()) {
            throw new ForbiddenHttpException();
        }

        return $this->renderAjaxContent(CommentWidget::widget([
            'comment' => $this->comment,
            'showBlocked' => Yii::$app->request->get('showBlocked'),
        ]));
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
                            'reason' => $form->message
                        ]
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
