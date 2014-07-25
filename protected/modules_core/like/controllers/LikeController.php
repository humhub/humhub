<?php

/**
 * Like Controller
 *
 * Handles requests by the like widgets. (e.g. like, unlike, show likes)
 *
 * @package humhub.modules_core.like.controllers
 * @since 0.5
 */
class LikeController extends Controller
{

    /**
     * The Object to be liked
     * Must be a subclass of HActiveRecordContent or HActiveRecordContentAddon
     *
     * @var Object
     */
    protected $targetObject = null;

    /**
     * @var string Model of Record to be liked (e.g. Post)
     */
    protected $model;

    /**
     * @var integer Primary Key Model of Record (e.g. Post) to be liked (e.g. 1)
     */
    protected $id;

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
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Loads the target object, checks rights & co
     */
    protected function loadTarget()
    {
        $this->model = Yii::app()->request->getQuery('className', '');
        $this->id = (int) Yii::app()->request->getQuery('id', '');

        $this->model = Yii::app()->input->stripClean(trim($this->model));

        // Check if like class exists
        if (!class_exists($this->model)) {
            throw new CHttpException(500, Yii::t('LikeModule.controllers_LikeController', 'Could not find target class!'));
        }

        $model = $this->model;
        $this->targetObject = $model::model()->findByPk($this->id);

        // Error Target not found
        if ($this->targetObject == null) {
            throw new CHttpException(500, Yii::t('LikeModule.controllers_LikeController', 'Could not find target record!'));
        }

        // Error Target not found
        if (is_subclass_of($this->targetObject, 'HActiveRecordContent')) {
            if (!$this->targetObject->content->canRead())
                throw new CHttpException(401, Yii::t('LikeModule.controllers_LikeController', 'Access denied!'));
        } elseif (is_subclass_of($this->targetObject, 'HActiveRecordContentAddon')) {
            if (!$this->targetObject->content->canRead())
                throw new CHttpException(401, Yii::t('LikeModule.controllers_LikeController', 'Access denied!'));
        } else {
            throw new CHttpException(500, Yii::t('LikeModule.controllers_LikeController', 'Invalid class given!'));
        }
    }

    /**
     * Creates a new like
     */
    public function actionLike()
    {

        $this->forcePostRequest();
        $this->loadTarget();

        $attributes = array('object_model' => $this->model, 'object_id' => $this->id, 'created_by' => Yii::app()->user->id);
        $like = Like::model()->findByAttributes($attributes);
        if ($like == null) {

            // Create Like Object
            $like = new Like();
            $like->object_model = $this->model;
            $like->object_id = $this->id;
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
        $this->loadTarget();

        $attributes = array('object_model' => $this->model, 'object_id' => $this->id, 'created_by' => Yii::app()->user->id);
        $like = Like::model()->findByAttributes($attributes);
        $like->delete();

        $this->actionShowLikes();
    }

    /**
     * Returns an JSON with current like informations about a target
     */
    public function actionShowLikes()
    {

        $this->loadTarget();

        // Some Meta Infos
        $currentUserLiked = false;
        $likes = Like::GetLikes($this->model, $this->id);
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

        $this->loadTarget();

        $page = (int) Yii::app()->request->getParam('page', 1);
        $total = Like::model()->count('object_model=:omodel AND object_id=:oid', array(':omodel' => $this->model, 'oid' => $this->id));

        $usersPerPage = HSetting::Get('paginationSize');

        $sql = "SELECT u.* FROM `like` l " .
                "LEFT JOIN user u ON l.created_by = u.id " .
                "WHERE l.object_model=:omodel AND l.object_id=:oid AND u.status=" . User::STATUS_ENABLED . " " .
                "ORDER BY l.created_at DESC " .
                "LIMIT " . intval(($page - 1) * $usersPerPage) . "," . intval($usersPerPage);
        $params = array(':omodel' => $this->model, ':oid' => $this->id);

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
