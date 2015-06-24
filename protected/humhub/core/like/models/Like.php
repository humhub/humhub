<?php

/**
 * This is the model class for table "like".
 *
 * The followings are the available columns in table 'like':
 * @property integer $id
 * @property integer $target_user_id
 * @property string $object_model
 * @property integer $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.like.models
 * @since 0.5
 */
class Like extends HActiveRecordContentAddon {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Like the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'like';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('object_model, object_id', 'required'),
            array('id, object_id, target_user_id created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('created_at', 'length', 'max' => 45),
            array('updated_at', 'safe')
        );
    }

    /**
     * Gets user for this like
     */
    public function getUser() {
        return User::model()->findByPk($this->created_by);
    }

    /**
     * Like Count for specifc model
     */
    public static function GetLikes($objectModel, $objectId) {
        $cacheId = "likes_" . $objectModel . "_" . $objectId;
        $cacheValue = Yii::app()->cache->get($cacheId);

        if ($cacheValue === false) {
            $newCacheValue = Like::model()->findAllByAttributes(array('object_model' => $objectModel, 'object_id' => $objectId));
            Yii::app()->cache->set($cacheId, $newCacheValue, HSetting::Get('expireTime', 'cache'));
            return $newCacheValue;
        } else {
            return $cacheValue;
        }
    }

    /**
     * After Save, delete LikeCount (Cache) for target object
     */
    protected function afterSave() {

        Yii::app()->cache->delete('likes_' . $this->object_model . "_" . $this->object_id);

        $activity = Activity::CreateForContent($this);
        $activity->type = "Like";
        $activity->module = "like";

        // Object Id for likes are not the Like Object itself
        $activity->object_model = $this->object_model;
        $activity->object_id = $this->object_id;

        $activity->save();
        $activity->fire();

        // Send Notifications
        NewLikeNotification::fire($this);

        return parent::afterSave();
    }

    /**
     * Before Delete, remove LikeCount (Cache) of target object.
     * Remove activity
     */
    protected function beforeDelete() {

        Yii::app()->cache->delete('likes_' . $this->object_model . "_" . $this->object_id);

        // Delete Activity
        // Currently we need to delete this manually, because the activity object is NOT bound to the Like
        // Instead is it bound to the Like Target (This should changed)
        $activity = Activity::model()->findByAttributes(array(
            'type' => 'Like',
            'module' => 'like',
            'object_model' => $this->object_model,
            'object_id' => $this->object_id,
            'created_by' => $this->created_by
        ));

        if ($activity)
            $activity->delete();

        Notification::remove('Like', $this->id);

        return parent::beforeDelete();
    }

}
