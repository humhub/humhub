<?php

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
class Comment extends HActiveRecordContentAddon
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Comment the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'comment';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('created_by, updated_by, space_id', 'numerical', 'integerOnly' => true),
            array('message, created_at, space_id, updated_at', 'safe'),
        );
    }

    public function relations()
    {
        return array(
            'user' => array(static::BELONGS_TO, 'User', 'created_by')
        );
    }

    /**
     * Before Delete, remove LikeCount (Cache) of target object.
     * Remove activity
     */
    protected function beforeDelete()
    {
        $this->flushCache();
        Notification::remove('Comment', $this->id);
        return parent::beforeDelete();
    }

    /**
     * Flush comments cache
     */
    public function flushCache()
    {
        Yii::app()->cache->delete('commentCount_' . $this->object_model . '_' . $this->object_id);
        Yii::app()->cache->delete('commentsLimited_' . $this->object_model . '_' . $this->object_id);

        // delete workspace comment stats cache
        if (!empty($this->space_id)) {
            Yii::app()->cache->delete('workspaceCommentCount_' . $this->space_id);
        }
    }

    /**
     * After Saving of comments, fire an activity
     *
     * @return type
     */
    protected function afterSave()
    {
        // flush the cache
        $this->flushCache();

        $activity = Activity::CreateForContent($this);
        $activity->type = "CommentCreated";
        $activity->module = "comment";
        $activity->save();
        $activity->fire();

        // Handle mentioned users
        // Execute before NewCommentNotification to avoid double notification when mentioned.
        UserMentioning::parse($this, $this->message);

        if ($this->isNewRecord) {
            // Send Notifications
            NewCommentNotification::fire($this);
        }
        

        return parent::afterSave();
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
        $comments = Yii::app()->cache->get($cacheID);

        if ($comments === false) {
            $commentCount = self::GetCommentCount($model, $id);

            $criteria = new CDbCriteria;
            $criteria->order = "created_at ASC";
            $criteria->offset = ($commentCount - $limit);
            $criteria->limit = $limit;

            $comments = Comment::model()->findAllByAttributes(array('object_model' => $model, 'object_id' => $id), $criteria);
            Yii::app()->cache->set($cacheID, $comments, HSetting::Get('expireTime', 'cache'));
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
        $commentCount = Yii::app()->cache->get($cacheID);

        if ($commentCount === false) {
            $commentCount = Comment::model()->countByAttributes(array('object_model' => $model, 'object_id' => $id));
            Yii::app()->cache->set($cacheID, $commentCount, HSetting::Get('expireTime', 'cache'));
        }

        return $commentCount;
    }

    /**
     * Returns a title/text which identifies this IContent.
     * e.g. Post: foo bar 123...
     *
     * @return String
     */
    public function getContentTitle()
    {
        return Yii::t('CommentModule.models_comment', 'Comment') . " \"" . Helpers::truncateText($this->message, 40) . "\"";
    }

    public function canDelete($userId = "")
    {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->created_by == $userId)
            return true;

        if (Yii::app()->user->isAdmin()) {
            return true;
        }

        if ($this->content->container instanceof Space && $this->content->container->isAdmin($userId)) {
            return true;
        }

        return false;
    }

}
