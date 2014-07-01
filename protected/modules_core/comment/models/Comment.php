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
class Comment extends HActiveRecordContentAddon {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Comment the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'comment';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('created_by, updated_by, space_id', 'numerical', 'integerOnly' => true),
            array('message, created_at, space_id, updated_at', 'safe'),
        );
    }

    /**
     * Before Delete, remove LikeCount (Cache) of target object.
     * Remove activity
     */
    protected function beforeDelete() {

        $this->flushCache();
        Notification::remove('Comment', $this->id);
        return parent::beforeDelete();
    }

    /**
     * Returns user of this comment
     *
     * @return User
     */
    public function getUser() {
        return User::model()->findByPk($this->created_by);
    }

    /**
     * Flush comments cache
     */
    public function flushCache() {
        Yii::app()->cache->delete('commentCount_' . $this->object_model . '_' . $this->object_id);
        Yii::app()->cache->delete('commentsLimited_' . $this->object_model . '_' . $this->object_id);

        // delete workspace comment stats cache
        if ($this->space_id != "") {
            Yii::app()->cache->delete('workspaceCommentCount_' . $this->space_id);
        }
    }

    /**
     * After Saving of comments, fire an activity
     *
     * @return type
     */
    protected function afterSave() {

        $this->flushCache();

        parent::afterSave();

        $activity = Activity::CreateForContent($this);
        $activity->type = "CommentCreated";
        $activity->module = "comment";
        $activity->save();
        $activity->fire();

        // Send Notifications
        NewCommentNotification::fire($this);
        AlsoCommentedNotification::fire($this);

        return true;
    }

    /**
     * Returns a limited amount of comments
     *
     * @param type $model
     * @param type $id
     * @param type $limit
     * @return type
     */
    public static function GetCommentsLimited($model, $id, $limit = 2) {
        $cacheId = "commentsLimited_" . $model . "_" . $id;
        $cacheValue = Yii::app()->cache->get($cacheId);

        if ($cacheValue === false) {

            $commentCount = self::GetCommentCount($model, $id);

            $criteria = new CDbCriteria;
            $criteria->order = "updated_at ASC";
            $criteria->offset = ($commentCount - 2);
            $criteria->limit = "2";

            $newCacheValue = Comment::model()->findAllByAttributes(array('object_model' => $model, 'object_id' => $id), $criteria);
            Yii::app()->cache->set($cacheId, $newCacheValue, HSetting::Get('expireTime', 'cache'));
            return $newCacheValue;
        } else {
            return $cacheValue;
        }
    }

    /**
     * Count number comments for this target object
     *
     * @param type $model
     * @param type $id
     * @return type
     */
    public static function GetCommentCount($model, $id) {
        $cacheId = "commentCount_" . $model . "_" . $id;
        $cacheValue = Yii::app()->cache->get($cacheId);

        if ($cacheValue === false) {
            $newCacheValue = Comment::model()->countByAttributes(array('object_model' => $model, 'object_id' => $id));
            Yii::app()->cache->set($cacheId, $newCacheValue, HSetting::Get('expireTime', 'cache'));
            return $newCacheValue;
        } else {
            return $cacheValue;
        }
    }

    /**
     * Returns a title/text which identifies this IContent.
     * e.g. Post: foo bar 123...
     *
     * @return String
     */
    public function getContentTitle() {
        return "Comment \"" . Helpers::truncateText($this->message, 40) . "\"";
    }

}