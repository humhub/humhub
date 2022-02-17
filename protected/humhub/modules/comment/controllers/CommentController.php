<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\libs\Helpers;
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
use Yii;
use yii\base\BaseObject;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

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
     * @return array
     */
    public function getAccessRules()
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
        $modelClass = Yii::$app->request->get('objectModel', Yii::$app->request->post('objectModel'));
        $modelPk = (int)Yii::$app->request->get('objectId', Yii::$app->request->post('objectId'));

        Helpers::CheckClassType($modelClass, [Comment::class, ContentActiveRecord::class]);
        $this->target = $modelClass::findOne(['id' => $modelPk]);

        if (!$this->target) {
            throw new NotFoundHttpException('Could not find underlying content or content addon record!');
        }

        if (!$this->target->content->canView()) {
            throw new ForbiddenHttpException();
        }

        return parent::beforeAction($action);
    }


    /**
     * Returns a List of all Comments belong to this Model
     */
    public function actionShow()
    {
        //TODO: Dont use query logic in controller layer...

        $query = Comment::find();
        $query->orderBy('created_at DESC');
        $query->where(['object_model' => get_class($this->target), 'object_id' => $this->target->getPrimaryKey()]);

        $pagination = new Pagination([
            'totalCount' => Comment::GetCommentCount(get_class($this->target), $this->target->getPrimaryKey()),
            'pageSize' => Yii::$app->request->get('pageSize', $this->module->commentsBlockLoadSize)
        ]);

        // If need to load more than 1 page per request
        $pageNum = Yii::$app->request->get('pageNum', 1);

        $query->offset($pagination->offset)->limit($pagination->limit * $pageNum);
        $comments = array_reverse($query->all());

        if ($pageNum > 1) {
            $pagination->setPage($pagination->page + $pageNum - 1);
        }

        $output = ShowMore::widget(['pagination' => $pagination, 'object' => $this->target]);
        foreach ($comments as $comment) {
            $output .= CommentWidget::widget(['comment' => $comment]);
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
                'justEdited' => true
            ]));
        }

        if (Yii::$app->request->post()) {
            Yii::$app->response->statusCode = 400;
        }

        $submitUrl = Url::to(['/comment/comment/edit',
            'id' => $comment->id,
            'objectModel' => $comment->object_model,
            'objectId' => $comment->object_id
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

        if (!$comment->canRead()) {
            throw new ForbiddenHttpException();
        }

        return $this->renderAjaxContent(CommentWidget::widget([
            'comment' => $comment,
            'showBlocked' => Yii::$app->request->get('showBlocked'),
        ]));
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
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
                    'send_web_notifications' => 1
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
                'model' => new AdminDeleteCommentForm()
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
