<?php

namespace humhub\modules\like\controllers;

use Yii;
use humhub\modules\like\models\Like;
use humhub\models\Setting;

/**
 * Like Controller
 *
 * Handles requests by the like widgets. (e.g. like, unlike, show likes)
 *
 * @package humhub.modules_core.like.controllers
 * @since 0.5
 */
class LikeController extends \humhub\modules\content\components\ContentAddonController
{

    /**
     * Creates a new like
     */
    public function actionLike()
    {
        $this->forcePostRequest();

        $like = Like::findOne(['object_model' => $this->contentModel, 'object_id' => $this->contentId, 'created_by' => Yii::$app->user->id]);
        if ($like === null) {

            // Create Like Object
            $like = new Like();
            $like->object_model = $this->contentModel;
            $like->object_id = $this->contentId;
            $like->save();
        }

        return $this->actionShowLikes();
    }

    /**
     * Unlikes an item
     */
    public function actionUnlike()
    {
        $this->forcePostRequest();

        if (!Yii::$app->user->isGuest) {
            $like = Like::findOne(['object_model' => $this->contentModel, 'object_id' => $this->contentId, 'created_by' => Yii::$app->user->id]);
            if ($like !== null) {
                $like->delete();
            }
        }

        return $this->actionShowLikes();
    }

    /**
     * Returns an JSON with current like informations about a target
     */
    public function actionShowLikes()
    {
        Yii::$app->response->format = 'json';

        // Some Meta Infos
        $currentUserLiked = false;

        $likes = Like::GetLikes($this->contentModel, $this->contentId);

        foreach ($likes as $like) {
            if ($like->user->id == Yii::$app->user->id) {
                $currentUserLiked = true;
            }
        }

        return [
            'currentUserLiked' => $currentUserLiked,
            'likeCounter' => count($likes)
        ];
    }

    /**
     * Returns a user list which contains all users who likes it
     */
    public function actionUserList()
    {

        $query = \humhub\modules\user\models\User::find();
        $query->leftJoin('like', 'like.created_by=user.id');
        $query->where([
            'like.object_id' => $this->contentId,
            'like.object_model' => $this->contentModel,
        ]);
        $query->orderBy('like.created_at DESC');

        $countQuery = clone $query;
        $pagination = new \yii\data\Pagination(['totalCount' => $countQuery->count(), 'pageSize' => Setting::Get('paginationSize')]);
        $query->offset($pagination->offset)->limit($pagination->limit);

        return $this->renderAjax("@humhub/modules/user/views/_listUsers", [
                    'title' => Yii::t('LikeModule.controllers_LikeController', "<strong>Users</strong> who like this"),
                    'users' => $query->all(),
                    'pagination' => $pagination
        ]);

        /*
          $page = (int) Yii::$app->request->getParam('page', 1);

          $total = Like::model()->count('object_model=:omodel AND object_id=:oid', array(':omodel' => $this->contentModel, 'oid' => $this->contentId));

          $usersPerPage = Setting::Get('paginationSize');

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

          Yii::$app->clientScript->render($output);
          echo $output;
          Yii::$app->end();
         *
         */
    }

}
