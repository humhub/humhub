<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\models;

use Yii;
use humhub\modules\content\components\ContentAddonActiveRecord;

/**
 * This is the model class for table "comment".
 *
 * The followings are the available columns in table 'comment':
 * @property integer $id
 * @property string $message
 * @property integer $object_id
 * @property integer $space_id
 * @property string $object_model
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property PortfolioItem[] $portfolioItems
 * @property Post[] $posts
 *
 * @package humhub.modules_core.comment.models
 * @since 0.5
 */
class Comment extends ContentAddonActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array(['created_by', 'updated_by', 'space_id'], 'integer'),
            array(['message', 'created_at', 'space_id', 'updated_at'], 'safe'),
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->flushCache();
        return parent::beforeDelete();
    }

    /**
     * Flush comments cache
     */
    public function flushCache()
    {
        Yii::$app->cache->delete('commentCount_' . $this->object_model . '_' . $this->object_id);
        Yii::$app->cache->delete('commentsLimited_' . $this->object_model . '_' . $this->object_id);

        // delete workspace comment stats cache
        if (!empty($this->space_id)) {
            Yii::$app->cache->delete('workspaceCommentCount_' . $this->space_id);
        }
    }

    /**
     * After Saving of comments, fire an activity
     *
     * @return type
     */
    public function afterSave($insert, $changedAttributes)
    {
        // flush the cache
        $this->flushCache();

        $activity = new \humhub\modules\comment\activities\NewComment();
        $activity->source = $this;
        $activity->create();

        // Handle mentioned users
        // Execute before NewCommentNotification to avoid double notification when mentioned.
        \humhub\modules\user\models\Mentioning::parse($this, $this->message);

        if ($insert) {
            $notification = new \humhub\modules\comment\notifications\NewComment();
            $notification->source = $this;
            $notification->originator = $this->user;
            $notification->sendBulk($this->content->getUnderlyingObject()->getFollowers(null, true, true));
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Returns a limited amount of comments
     *
     * @param type $model
     * @param type $id
     * @param type $limit
     * @return type
     */
    public static function GetCommentsLimited($model, $id, $limit = 2)
    {
        $cacheID = sprintf("commentsLimited_%s_%s", $model, $id);
        $comments = Yii::$app->cache->get($cacheID);

        if ($comments === false) {
            $commentCount = self::GetCommentCount($model, $id);

            $query = Comment::find();
            $query->offset($commentCount - $limit);
            $query->orderBy('created_at ASC');
            $query->limit($limit);
            $query->where(['object_model' => $model, 'object_id' => $id]);

            $comments = $query->all();
            Yii::$app->cache->set($cacheID, $comments, \humhub\models\Setting::Get('expireTime', 'cache'));
        }

        return $comments;
    }

    /**
     * Count number comments for this target object
     *
     * @param type $model
     * @param type $id
     * @return type
     */
    public static function GetCommentCount($model, $id)
    {
        $cacheID = sprintf("commentCount_%s_%s", $model, $id);
        $commentCount = Yii::$app->cache->get($cacheID);

        if ($commentCount === false) {
            $commentCount = Comment::find()->where(['object_model' => $model, 'object_id' => $id])->count();
            Yii::$app->cache->set($cacheID, $commentCount, \humhub\models\Setting::Get('expireTime', 'cache'));
        }

        return $commentCount;
    }

    /**
     * @inheritdoc
     */
    public function getContentTitle()
    {
        return Yii::t('CommentModule.models_comment', 'Comment');
    }

    /**
     * @inheritdoc
     */
    public function getContentPreview($maxLength = 0)
    {
        if ($maxLength == 0) {
            return $this->message;
        }

        return \humhub\libs\Helpers::truncateText($this->message, $maxLength);
    }

    public function canDelete($userId = "")
    {

        if ($userId == "")
            $userId = Yii::$app->user->id;

        if ($this->created_by == $userId)
            return true;

        if (Yii::$app->user->isAdmin()) {
            return true;
        }

        if ($this->content->container instanceof \humhub\modules\space\models\Space && $this->content->container->isAdmin($userId)) {
            return true;
        }

        return false;
    }

}
