<?php

/**
 * CommentController provides all comment related actions.
 *
 * @package humhub.modules_core.comment.controllers
 * @since 0.5
 */
class CommentController extends ContentAddonController
{

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@', (HSetting::Get('allowGuestAccess', 'authentication_internal')) ? "?" : "@"),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Returns a List of all Comments belong to this Model
     */
    public function actionShow()
    {

        $criteria = new CDbCriteria();
        $criteria->order = "created_at DESC";
        $criteria->condition = "object_model=:model AND object_id=:id";
        $criteria->params = array(':model' => get_class($this->parentContent), ':id' => $this->parentContent->getPrimaryKey());

        $pagination = new CPagination(Comment::GetCommentCount(get_class($this->parentContent), $this->parentContent->getPrimaryKey()));
        $pagination->setPageSize($this->module->commentsBlockLoadSize);
        $pagination->applyLimit($criteria);

        $comments = array_reverse(Comment::model()->findAll($criteria));

        $output = $this->widget('application.modules_core.comment.widgets.CommentsShowMoreWidget', array('pagination' => $pagination, 'object' => $this->parentContent), true);
        foreach ($comments as $comment) {
            $output .= $this->widget('application.modules_core.comment.widgets.ShowCommentWidget', array('comment' => $comment), true);
        }

        if (Yii::app()->request->getParam('mode') == 'popup') {
            $id = get_class($this->parentContent) . "_" . $this->parentContent->getPrimaryKey();
            $this->renderPartial('showPopup', array('object' => $this->parentContent, 'output' => $output, 'id' => $id), false, true);
        } else {
            Yii::app()->clientScript->render($output);
            echo $output;
            Yii::app()->end();
        }
    }

    /**
     * Handles AJAX Post Request to submit new Comment
     */
    public function actionPost()
    {
        $this->forcePostRequest();

        $message = Yii::app()->request->getParam('message', "");
        $message = Yii::app()->input->stripClean(trim($message));

        if ($message != "" && !Yii::app()->user->isGuest) {

            $comment = new Comment;
            $comment->message = $message;
            $comment->object_model = get_class($this->parentContent);
            $comment->object_id = $this->parentContent->getPrimaryKey();
            $comment->save();

            File::attachPrecreated($comment, Yii::app()->request->getParam('fileList'));

            // Reload comment to get populated created_at field
            $comment = Comment::model()->findByPk($comment->id);

            $output = $this->widget('application.modules_core.comment.widgets.ShowCommentWidget', array(
                'comment' => $comment,
                'justEdited' => true
                    ), true);
            Yii::app()->clientScript->render($output);
            echo $output;
        }
    }

    public function actionEdit()
    {

        $this->loadContentAddon('Comment', Yii::app()->request->getParam('id'));

        if ($this->contentAddon->canWrite()) {

            if (isset($_POST['Comment'])) {
                $_POST['Comment'] = Yii::app()->input->stripClean($_POST['Comment']);
                $this->contentAddon->attributes = $_POST['Comment'];
                if ($this->contentAddon->validate()) {
                    $this->contentAddon->save();

                    // Reload comment to get populated updated_at field
                    $this->contentAddon = Comment::model()->findByPk($this->contentAddon->id);

                    // Return the new comment
                    $output = $this->widget('application.modules_core.comment.widgets.ShowCommentWidget', array(
                        'comment' => $this->contentAddon,
                        'justEdited' => true
                            ), true);
                    Yii::app()->clientScript->render($output);
                    echo $output;
                    return;
                }
            }

            $this->renderPartial('edit', array(
                'comment' => $this->contentAddon,
                'contentModel' => $this->contentAddon->object_model,
                'contentId' => $this->contentAddon->object_id
                    ), false, true);
        } else {
            throw new CHttpException(403, Yii::t('CommentModule.controllers_CommentController', 'Access denied!'));
        }
    }

    /**
     * Handles AJAX Request for Comment Deletion.
     * Currently this is only allowed for the Comment Owner.
     */
    public function actionDelete()
    {

        $this->forcePostRequest();
        $this->loadContentAddon('Comment', Yii::app()->request->getParam('id'));

        if ($this->contentAddon->canDelete()) {
            $this->contentAddon->delete();
        } else {
            throw new CHttpException(500, Yii::t('CommentModule.controllers_CommentController', 'Insufficent permissions!'));
        }
    }

}
