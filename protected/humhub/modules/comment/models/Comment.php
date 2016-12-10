<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\models;

use humhub\modules\post\models\Post;
use humhub\modules\content\components\ContentAddonActiveRecord;
use Yii;

/**
 * This is the model class for table "comment".
 *
 * The followings are the available columns in table 'comment':
 * @property integer $id
 * @property string $message
 * @property integer $object_id
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
        return [
            [['message'], 'safe'],
        ];
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
     * @inheritdoc
     */
    public function afterDelete()
    {
        try {
            $this->updateContentSearch();
        } catch (\yii\base\Exception $ex) {
            ;
        }
        parent::afterDelete();
    }

    /**
     * Flush comments cache
     */
    public function flushCache()
    {
        Yii::$app->cache->delete('commentCount_' . $this->object_model . '_' . $this->object_id);
        Yii::$app->cache->delete('commentsLimited_' . $this->object_model . '_' . $this->object_id);
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
            $notification->sendBulk($this->content->getPolymorphicRelation()->getFollowers(null, true, true));
        }

        $this->updateContentSearch();

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Force search update of underlying content object.
     * (This has also indexed the comments.)
     */
    protected function updateContentSearch()
    {
        if ($this->content->getPolymorphicRelation() instanceof \humhub\modules\search\interfaces\Searchable) {
            Yii::$app->search->update($this->content->getPolymorphicRelation());
        }
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
            $query->joinWith('user');

            $comments = $query->all();
            Yii::$app->cache->set($cacheID, $comments, Yii::$app->settings->get('cache.expireTime'));
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
            Yii::$app->cache->set($cacheID, $commentCount, Yii::$app->settings->get('cache.expireTime'));
        }

        return $commentCount;
    }

    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        return Yii::t('CommentModule.models_comment', 'comment');
    }

    /**
     * @inheritdoc
     */
    public function getContentDescription()
    {
        return $this->message;
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
