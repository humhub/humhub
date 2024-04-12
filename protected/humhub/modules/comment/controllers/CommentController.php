<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\helpers\DataTypeHelper;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\models\forms\AdminDeleteCommentForm;
use humhub\modules\comment\models\forms\CommentForm;
use humhub\modules\comment\Module;
use humhub\modules\comment\notifications\CommentDeleted;
use humhub\modules\comment\widgets\AdminDeleteModal;
use humhub\modules\comment\widgets\Comment as CommentWidget;
use humhub\modules\comment\widgets\Form;
use humhub\modules\comment\widgets\ShowMore;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\file\handler\FileHandlerCollection;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CommentController provides all comment related actions.
 *
 * @package humhub.modules_core.comment.controllers
 * @property Module $module
 * @since 0.5
 */
class CommentController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['post', 'edit', 'delete']],
            [ControllerAccess::RULE_POST => ['post']],
        ];
    }

    /**
     * @var Comment|ContentActiveRecord The model to comment
     */
    public $target;


    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $modelClass = Yii::$app->request->get('objectModel', Yii::$app->request->post('objectModel'));
            $modelPk = (int)Yii::$app->request->get('objectId', Yii::$app->request->post('objectId'));

            /** @var Comment|ContentActiveRecord $modelClass */
            $modelClass = DataTypeHelper::matchClassType($modelClass, [Comment::class, ContentActiveRecord::class], true);
            $this->target = $modelClass::findOne(['id' => $modelPk]);

            if (!$this->target) {
                throw new NotFoundHttpException('Could not find underlying content or content addon record!');
            }

            if (!$this->target->content->canView()) {
                throw new ForbiddenHttpException();
            }

            return true;
        }

        return false;
    }


    /**
     * Returns a List of all Comments belong to this Model
     */
    public function actionShow()
    {
        $commentId = (int)Yii::$app->request->get('commentId');
        $type = Yii::$app->request->get('type', ShowMore::TYPE_PREVIOUS);
        $pageSize = (int)Yii::$app->request->get('pageSize', $this->module->commentsBlockLoadSize);
        if ($pageSize > $this->module->commentsBlockLoadSize) {
            $pageSize = $this->module->commentsBlockLoadSize;
        }

        $comments = Comment::getMoreComments($this->target, $commentId, $type, $pageSize);

        $output = '';
        if ($type === ShowMore::TYPE_PREVIOUS) {
            $output .= ShowMore::widget([
                'object' => $this->target,
                'pageSize' => $pageSize,
                'commentId' => isset($comments[0]) ? $comments[0]->id : null,
                'type' => $type,
            ]);
        }
        foreach ($comments as $comment) {
            $output .= CommentWidget::widget(['comment' => $comment]);
        }
        if ($type === ShowMore::TYPE_NEXT && count($comments) > 1) {
            $output .= ShowMore::widget([
                'object' => $this->target,
                'pageSize' => $pageSize,
                'commentId' => $comments[count($comments) - 1]->id,
                'type' => $type,
            ]);
        }

        if (Yii::$app->request->get('mode') === 'popup') {
            return $this->renderAjax('showPopup', ['object' => $this->target, 'output' => $output, 'id' => $this->target->content->getUniqueId()]);
        } else {
            return $this->renderAjaxContent($output);
        }
    }

    /**
     * Handles AJAX Post Request to submit new Comment
     */
    public function actionPost()
    {
        if (!$this->module->canComment($this->target)) {
            throw new ForbiddenHttpException();
        }

        return Comment::getDb()->transaction(function ($db) {

            $form = new CommentForm($this->target);

            if ($form->load(Yii::$app->request->post()) && $form->save()) {
                return $this->renderAjaxContent(CommentWidget::widget(['comment' => $form->comment]));
            }

            Yii::$app->response->statusCode = 400;

            return $this->renderAjaxContent(Form::widget([
                'object' => $this->target,
                'model' => $form->comment,
                'isHidden' => false,
            ]));
        });
    }


    public function actionEdit($id)
    {
        $comment = $this->getComment($id);

        if (!$comment->canEdit()) {
            throw new ForbiddenHttpException();
        }

        $form = new CommentForm($this->target, $comment);

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            return $this->renderAjaxContent(CommentWidget::widget([
                'comment' => $form->comment,
                'justEdited' => true,
            ]));
        }

        if (Yii::$app->request->post()) {
            Yii::$app->response->statusCode = 400;
        }

        $submitUrl = Url::to(['/comment/comment/edit',
            'id' => $comment->id,
            'objectModel' => $comment->object_model,
            'objectId' => $comment->object_id,
        ]);

        return $this->renderAjax('edit', [
            'comment' => $comment,
            'objectModel' => $comment->object_model,
            'objectId' => $comment->object_id,
            'submitUrl' => $submitUrl,
            'fileHandlers' => FileHandlerCollection::getByType([FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE]),
        ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionLoad($id)
    {
        $comment = $this->getComment($id);

        if (!$comment->canView()) {
            throw new ForbiddenHttpException();
        }

        return $this->renderAjaxContent(CommentWidget::widget([
            'comment' => $comment,
            'showBlocked' => Yii::$app->request->get('showBlocked'),
        ]));
    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->forcePostRequest();

        $comment = $this->getComment($id);

        if (!$comment->canDelete()) {
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
                    ->about($comment->getCommentedRecord())
                    ->payload(['commentText' => (new CommentDeleted())->getContentPreview($comment, 30), 'reason' => $form->message]);
                $commentDeleted->saveRecord($comment->createdBy);

                $commentDeleted->record->updateAttributes([
                    'send_web_notifications' => 1,
                ]);
            }
        }

        return $this->asJson(['success' => $comment->delete()]);
    }

    /**
     * Returns modal content for admin to delete comment
     *
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGetAdminDeleteModal($id)
    {
        Yii::$app->response->format = 'json';

        $comment = $this->getComment($id);

        if (!$comment->canDelete()) {
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

    /**
     * @param $id
     * @return Comment
     * @throws NotFoundHttpException
     */
    private function getComment($id)
    {
        $comment = Comment::findOne(['id' => $id]);

        if (!$comment) {
            throw new NotFoundHttpException();
        }

        return $comment;
    }

}
