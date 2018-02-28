<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\models;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Exception;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Mentioning;
use humhub\modules\post\models\Post;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\space\models\Space;
use humhub\modules\comment\activities\NewComment;
use humhub\modules\comment\notifications\NewComment as NewCommentNotification;
use humhub\modules\comment\live\NewComment as NewCommentLive;

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
 * @property Post[] $posts
 *
 * @package humhub.modules_core.comment.models
 * @since 0.5
 */
class Comment extends ContentAddonActiveRecord implements ContentOwner
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
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::className(),
                'mustBeInstanceOf' => [
                    ActiveRecord::className(),
                ]
            ]
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
        } catch (Exception $ex) {
            Yii::error($ex);
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
     * @param bool $insert
     * @param array $changedAttributes
     * @return bool
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->flushCache();

        // Creating activity
        NewComment::instance()->about($this)->save();

        // Handle mentioned users
        // Execute before NewCommentNotification to avoid double notification when mentioned.
        $processResult = RichText::postProcess($this->message, $this);
        $mentionedUsers = (isset($processResult['mentioning'])) ? $processResult['mentioning'] : [];

        if ($insert) {
            $followers = $this->getCommentedRecord()->getFollowers(null, true);
            $this->filterMentionings($followers, $mentionedUsers);

            // Update updated_at etc..
            $this->refresh();

            NewCommentNotification::instance()
                    ->from(Yii::$app->user->getIdentity())
                    ->about($this)
                    ->sendBulk($followers);

            if ($this->content->container) {
                Yii::$app->live->send(new NewCommentLive([
                    'contentContainerId' => $this->content->container->id,
                    'visibility' => $this->content->visibility,
                    'contentId' => $this->content->id,
                    'commentId' => $this->id
                ]));
            }
        }

        $this->updateContentSearch();

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Filters out all users contained in $mentionedUsers from $followers
     *
     * @param \humhub\modules\user\models\User[] $followers
     * @param \humhub\modules\user\models\User[] $mentionedUsers
     */
    private function filterMentionings(&$followers, $mentionedUsers)
    {
        if (empty($mentionedUsers)) {
            return;
        }

        foreach($followers as $i => $follower) {
            foreach($mentionedUsers as $mentioned) {
                if ($follower->is($mentioned)) {
                    unset($followers[$i]);
                    continue 2;
                }
            }
        }
    }

    /**
     * Force search update of underlying content object.
     * (This has also indexed the comments.)
     */
    protected function updateContentSearch()
    {
        if ($this->getCommentedRecord() instanceof Searchable) {
            Yii::$app->search->update($this->getCommentedRecord());
        }
    }

    /**
     * Returns the commented record e.g. a Post
     *
     * @return \humhub\modules\content\components\ContentActiveRecord
     */
    public function getCommentedRecord()
    {
        return $this->content->getPolymorphicRelation();
    }

    /**
     * Returns a limited amount of comments
     *
     * @param $model
     * @param $id
     * @param int $limit
     *
     * @return array|mixed|\yii\db\ActiveRecord[]
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
     * @param $model
     * @param $id
     *
     * @return int|mixed|string
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

    public function canDelete($userId = '')
    {

        if ($userId == '') {
            $userId = Yii::$app->user->id;
        }

        if ($this->created_by == $userId) {
            return true;
        }

        if (Yii::$app->user->isAdmin()) {
            return true;
        }

        if ($this->content->container instanceof Space && $this->content->container->isAdmin($userId)) {
            return true;
        }

        return false;
    }

}
