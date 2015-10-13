<?php

/**
 * Like Controller
 *
 * Handles requests by the like widgets. (e.g. like, unlike, show likes)
 *
 * @package humhub.modules_core.like.controllers
 * @since 0.5
 */
class LikeController extends ContentAddonController
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
     * Creates a new like
     */
    public function actionLike()
    {

        $this->forcePostRequest();

        $attributes = array('object_model' => $this->contentModel, 'object_id' => $this->contentId, 'created_by' => Yii::app()->user->id);
        $like = Like::model()->findByAttributes($attributes);
        if ($like == null && !Yii::app()->user->isGuest) {

            // Create Like Object
            $like = new Like();
            $like->object_model = $this->contentModel;
            $like->object_id = $this->contentId;
            $like->save();
        }

        $this->actionShowLikes();
    }

    /**
     * Unlikes an item
     */
    public function actionUnLike()
    {

        $this->forcePostRequest();

        if (!Yii::app()->user->isGuest) {
            $attributes = array('object_model' => $this->contentModel, 'object_id' => $this->contentId, 'created_by' => Yii::app()->user->id);
            $like = Like::model()->findByAttributes($attributes);
            if ($like != null) {
                $like->delete();
            }
        }

        $this->actionShowLikes();
    }

    /**
     * Returns an JSON with current like informations about a target
     */
    public function actionShowLikes()
    {

        // Some Meta Infos
        $currentUserLiked = false;
        $likes = Like::GetLikes($this->contentModel, $this->contentId);
        foreach ($likes as $like) {
            if ($like->getUser()->id == Yii::app()->user->id) {
                $currentUserLiked = true;
            }
        }

        $json = array();
        $json['currentUserLiked'] = $currentUserLiked;
        $json['likeCounter'] = count($likes);

        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * Returns a user list which contains all users who likes it
     */
    public function actionUserList()
    {
        $page = (int) Yii::app()->request->getParam('page', 1);
        $total = Like::model()->count('object_model=:omodel AND object_id=:oid', array(':omodel' => $this->contentModel, 'oid' => $this->contentId));

        $usersPerPage = HSetting::Get('paginationSize');

        $sql = "SELECT u.* FROM `like` l " .
                "LEFT JOIN user u ON l.created_by = u.id " .
                "WHERE l.object_model=:omodel AND l.object_id=:oid AND u.status=" . User::STATUS_ENABLED . " " .
                "ORDER BY l.created_at DESC " .
                "LIMIT " . intval(($page - 1) * $usersPerPage) . "," . intval($usersPerPage);
        $params = array(':omodel' => $this->contentModel, ':oid' => $this->contentId);

        $pagination = new CPagination($total);
        $pagination->setPageSize($usersPerPage);

        $users = User::model()->findAllBySql($sql, $params);

        $output = $this->renderPartial('application.modules_core.user.views._listUsers', array(
            'title' => Yii::t('LikeModule.controllers_LikeController', "<strong>Users</strong> who like this"),
            'users' => $users,
            'pagination' => $pagination
                ), true);

        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

}
