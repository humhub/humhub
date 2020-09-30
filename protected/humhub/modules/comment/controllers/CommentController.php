<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers;

use humhub\components\Controller;
use humhub\libs\Helpers;
use humhub\modules\comment\models\forms\CommentForm;
use humhub\modules\comment\Module;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use Yii;
use yii\data\Pagination;
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\components\behaviors\AccessControl;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\Comment as CommentWidget;
use humhub\modules\comment\widgets\ShowMore;
use yii\web\ForbiddenHttpException;

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
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
                'guestAllowedActions' => ['show']
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'post' => ['POST'],
                ],
            ],
        ];
    }


    /**
     * @var Comment|ContentActiveRecord The model to comment
     */
    public $target;


    /**
     * @var Content
     */
    public $content;


    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        $modelClass = Yii::$app->request->get('objectModel', Yii::$app->request->post('objectModel'));
        $modelPk = (int)Yii::$app->request->get('objectId', Yii::$app->request->post('objectId'));

        Helpers::CheckClassType($modelClass, [Comment::class, ContentActiveRecord::class]);
        $this->target = $modelClass::findOne(['id' => $modelPk]);

        if ($this->target === null) {
            throw new HttpException(500, 'Could not find underlying content or content addon record!');
        }

        $this->content = $this->target->content;

        if (!$this->content->canView()) {
            throw new HttpException(403, 'Access denied!');
        }

        return parent::beforeAction($action);
    }


    /**
     * Returns a List of all Comments belong to this Model
     */
    public function actionShow()
    {
        $query = Comment::find();
        $query->orderBy('created_at DESC');
        $query->where(['object_model' => get_class($this->target), 'object_id' => $this->target->getPrimaryKey()]);

        $pagination = new Pagination([
            'totalCount' => Comment::GetCommentCount(get_class($this->target), $this->target->getPrimaryKey()),
            'pageSize' => $this->module->commentsBlockLoadSize
        ]);

        $query->offset($pagination->offset)->limit($pagination->limit);
        $comments = array_reverse($query->all());

        $output = ShowMore::widget(['pagination' => $pagination, 'object' => $this->target]);
        foreach ($comments as $comment) {
            $output .= CommentWidget::widget(['comment' => $comment]);
        }

        if (Yii::$app->request->get('mode') == 'popup') {
            return $this->renderAjax('showPopup', ['object' => $this->target, 'output' => $output, 'id' => $this->content->getUniqueId()]);
        } else {
            return $this->renderAjaxContent($output);
        }
    }

    /**
     * Handles AJAX Post Request to submit new Comment
     */
    public function actionPost()
    {
        if (Yii::$app->user->isGuest || !$this->module->canComment($this->target)) {
            throw new ForbiddenHttpException(Yii::t('CommentModule.base', 'You are not allowed to comment.'));
        }

        return Comment::getDb()->transaction(function ($db) {

            $form = new CommentForm($this->target);

            if ($form->load(Yii::$app->request->post()) && $form->create()) {
                return $this->renderAjaxContent(CommentWidget::widget(['comment' => $form->comment]));
            } else {
                return false;
            }
        });
    }


    public function actionEdit()
    {
        $comment = Comment::findOne(['id' => Yii::$app->request->get('id')]);

        // Find all file attached to this comment
        $files = $comment->fileManager->find()->asArray()->all();
        $fileList = [];
        $i = 0;

        // Fill up files array
        foreach ($files as $file) {
            $fileList[$i] = $file['guid'];
            $i++;
        }

        if (!$comment->canEdit()) {
            throw new HttpException(403, Yii::t('CommentModule.base', 'Access denied!'));
        }

        $form = new CommentForm($this->target, $fileList);

        if ($form->load(Yii::$app->request->post()) && $form->update($comment)) {
            return $this->renderAjaxContent(CommentWidget::widget([
                'comment' => $form->comment,
                'justEdited' => true
            ]));
        } else if (Yii::$app->request->post()) {
            Yii::$app->response->statusCode = 400;
        }

        $submitUrl = Url::to(['/comment/comment/edit',
            'id' => $comment->id, 'objectModel' => $comment->object_model, 'objectId' => $comment->object_id]);

        return $this->renderAjax('edit', [
            'comment' => $comment,
            'objectModel' => $comment->object_model,
            'objectId' => $comment->object_id,
            'submitUrl' => $submitUrl
        ]);
    }

    public function actionLoad()
    {
        $comment = Comment::findOne(['id' => Yii::$app->request->get('id')]);

        if (!$comment->canRead()) {
            throw new HttpException(403, Yii::t('CommentModule.base', 'Access denied!'));
        }

        return $this->renderAjaxContent(CommentWidget::widget(['comment' => $comment]));
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        $comment = Comment::findOne(['id' => Yii::$app->request->get('id')]);
        if ($comment !== null && $comment->canDelete()) {
            $comment->delete();
            return $this->asJson(['success' => true]);
        } else {
            throw new HttpException(500, Yii::t('CommentModule.base', 'Insufficent permissions!'));
        }
    }

}
