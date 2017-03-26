<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\controllers;

use Yii;
use humhub\modules\comment\models\Comment;
use \humhub\modules\comment\widgets\ShowMore;
use yii\web\HttpException;

/**
 * CommentController provides all comment related actions.
 *
 * @package humhub.modules_core.comment.controllers
 * @since 0.5
 */
class CommentController extends \humhub\modules\content\components\ContentAddonController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['show']
            ]
        ];
    }

    /**
     * Returns a List of all Comments belong to this Model
     */
    public function actionShow()
    {
        $content = $this->parentContent;

        $query = Comment::find();
        $query->orderBy('created_at DESC');
        $query->where([
            'object_model' => $content->className(),
            'object_id' => $content->getPrimaryKey(),
        ]);
        
        $pagination = new \yii\data\Pagination([
            'totalCount' => Comment::GetCommentCount($content->className(), $content->getPrimaryKey()),
            'pageSize' => $this->module->commentsBlockLoadSize
        ]);
        
        $query->offset($pagination->offset)->limit($pagination->limit);
        $comments = array_reverse($query->all());

        $output = ShowMore::widget(['pagination' => $pagination, 'object' => $content]);
        foreach ($comments as $comment) {
            $output .= \humhub\modules\comment\widgets\Comment::widget(['comment' => $comment]);
        }

        if (Yii::$app->request->get('mode') == 'popup') {
            return $this->renderAjax('showPopup', ['object' => $content, 'output' => $output, 'id' => $content->getUniqueId()]);
        } else {
            return $this->renderAjaxContent($output);
        }
    }

    /**
     * Handles AJAX Post Request to submit new Comment
     */
    public function actionPost()
    {
        $this->forcePostRequest();

        if (Yii::$app->user->isGuest) {
            throw new HttpException(403, 'Guests can not comment.');
        }

        if (!Yii::$app->getModule('comment')->canComment($this->parentContent->content)) {
            throw new HttpException(403, 'You are not allowed to comment.');
        }

        $message = Yii::$app->request->post('message');
        $files = Yii::$app->request->post('fileList');


        if (empty(trim($message)) && empty($files)) {
            // do not create empty comments
            return '';
        }

        $comment = new Comment(['message' => $message]);
        $comment->setPolyMorphicRelation($this->parentContent);
        $comment->save();
        $comment->fileManager->attach($files);

        // Reload comment to get populated created_at field
        $comment->refresh();

        return $this->renderAjaxContent(\humhub\modules\comment\widgets\Comment::widget(['comment' => $comment]));
    }

    public function actionEdit()
    {
        $this->loadContentAddon(Comment::className(), Yii::$app->request->get('id'));

        if (!$this->contentAddon->canWrite()) {
            throw new HttpException(403, Yii::t('CommentModule.controllers_CommentController', 'Access denied!'));
        }

        if ($this->contentAddon->load(Yii::$app->request->post()) && $this->contentAddon->validate() && $this->contentAddon->save()) {

            // Reload comment to get populated updated_at field
            $this->contentAddon = Comment::findOne(['id' => $this->contentAddon->id]);

            return $this->renderAjaxContent(\humhub\modules\comment\widgets\Comment::widget([
                                'comment' => $this->contentAddon,
                                'justEdited' => true
            ]));
        }

        return $this->renderAjax('edit', array(
                    'comment' => $this->contentAddon,
                    'contentModel' => $this->contentAddon->object_model,
                    'contentId' => $this->contentAddon->object_id
        ));
    }

    public function actionLoad()
    {
        $this->loadContentAddon(Comment::className(), Yii::$app->request->get('id'));

        if (!$this->contentAddon->canRead()) {
            throw new HttpException(403, Yii::t('CommentModule.controllers_CommentController', 'Access denied!'));
        }

        return $this->renderAjaxContent(\humhub\modules\comment\widgets\Comment::widget(['comment' => $this->contentAddon]));
    }

    /**
     * Handles AJAX Request for Comment Deletion.
     * Currently this is only allowed for the Comment Owner.
     */
    public function actionDelete()
    {
        $this->forcePostRequest();
        $this->loadContentAddon(Comment::className(), Yii::$app->request->get('id'));
        Yii::$app->response->format = 'json';

        if ($this->contentAddon->canDelete()) {
            $this->contentAddon->delete();
            return ['success' => true];
        } else {
            throw new HttpException(500, Yii::t('CommentModule.controllers_CommentController', 'Insufficent permissions!'));
        }
    }

}
