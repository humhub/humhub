<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\controllers;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\helpers\DataTypeHelper;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\like\models\Like;
use humhub\modules\like\Module;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserListBox;
use Yii;
use yii\db\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Like Controller
 *
 * Handles requests by the like widgets. (e.g. like, unlike, show likes)
 *
 * @property Module $module
 * @since 0.5
 */
class LikeController extends Controller
{
    protected ?string $objectModel = null;
    protected ?int $objectId = null;
    protected ContentAddonActiveRecord|ContentActiveRecord|ActiveRecord|null $object = null;

    /**
     * @param $action
     * @return bool
     * @throws HttpException
     */
    public function beforeAction($action)
    {
        if (!$this->module->isEnabled) {
            throw new NotFoundHttpException('The like module not enabled!');
        }

        $this->objectModel = Yii::$app->request->get('objectModel');
        $this->objectId = (int)Yii::$app->request->get('objectId');

        if (!$this->objectModel || !$this->objectId) {
            throw new ServerErrorHttpException('Model & ID parameter required!');
        }

        $this->objectModel = DataTypeHelper::matchClassType(
            $this->objectModel,
            [ContentAddonActiveRecord::class, ContentActiveRecord::class, ActiveRecord::class],
            true,
        );

        $this->object = $this->objectModel::findOne(['id' => $this->objectId]);
        if ($this->object === null) {
            throw new NotFoundHttpException('Could not find underlying object record!');
        }

        $content = $this->object->content ?? null;
        if (
            $content instanceof Content
            && !$content->canView()
        ) {
            throw new ForbiddenHttpException('Access denied!');
        }

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
                'guestAllowedActions' => ['show-likes'],
            ],
        ];
    }

    /**
     * Creates a new like
     * @throws HttpException
     * @throws Exception
     */
    public function actionLike()
    {
        if (!$this->module->canLike($this->object)) {
            throw new ForbiddenHttpException();
        }

        $this->forcePostRequest();

        $like = Like::findOne(['object_model' => $this->objectModel, 'object_id' => $this->objectId, 'created_by' => Yii::$app->user->id]);
        if ($like === null) {

            // Create Like Object
            $like = new Like([
                'object_model' => $this->objectModel,
                'object_id' => $this->objectId,
            ]);
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
            $like = Like::findOne(['object_model' => $this->objectModel, 'object_id' => $this->objectId, 'created_by' => Yii::$app->user->id]);
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

        $likes = Like::GetLikes($this->objectModel, $this->objectId);

        foreach ($likes as $like) {
            if ($like->user->id == Yii::$app->user->id) {
                $currentUserLiked = true;
            }
        }

        return [
            'currentUserLiked' => $currentUserLiked,
            'likeCounter' => count($likes),
        ];
    }

    /**
     * Returns a user list which contains all users who likes it
     */
    public function actionUserList()
    {

        $query = User::find();
        $query->leftJoin('like', 'like.created_by=user.id');
        $query->where([
            'like.object_id' => $this->objectId,
            'like.object_model' => $this->objectModel,
        ]);
        $query->orderBy('like.created_at DESC');

        $title = Yii::t('LikeModule.base', "<strong>Users</strong> who like this");

        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

}
