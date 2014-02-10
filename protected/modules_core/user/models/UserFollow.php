<?php

/**
 * This is the model class for table "user_follow".
 *
 * The followings are the available columns in table 'user_follow':
 * @property integer $user_follower_id
 * @property integer $user_followed_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property User $userFollower
 * @property User $userFollowed
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke

 */
class UserFollow extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserFollow the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_follow';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_follower_id, user_followed_id', 'required'),
            array('user_follower_id, user_followed_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('created_at, updated_at', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('user_follower_id, user_followed_id, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'userFollower' => array(self::BELONGS_TO, 'User', 'user_follower_id'),
            'userFollowed' => array(self::BELONGS_TO, 'User', 'user_followed_id'),
        );
    }

    /**
     * After Save
     */
    protected function afterSave() {

        $follower = User::model()->findByPk($this->user_follower_id);

        // Create Wall Activity for that
        $activity = new Activity;
        $activity->contentMeta->user_id = $follower->id;
        $activity->type = "ActivityUserFollowsUser";
        $activity->object_model = "User";
        $activity->object_id = $this->user_followed_id;
        $activity->save();
        $activity->contentMeta->addToWall($follower->wall_id);

        return parent::afterSave();
    }

    /**
     * Before Delete
     */
    protected function beforeDelete() {

        $follower = User::model()->findByPk($this->user_follower_id);

        $sql = "SELECT activity.* FROM activity " .
                "LEFT JOIN content ON activity.id = content.object_id AND content.object_model=:content_model " .
                "WHERE activity.type=:activity_type AND " .
                "activity.object_id = :activity_model_id AND " .
                "activity.object_model=:activity_model AND " .
                "content.user_id = :userId";

        $params = array();
        $params[':content_model'] = 'Activity';
        $params[':activity_type'] = 'ActivityUserFollowsUser';
        $params[':activity_model'] = 'User';
        $params[':activity_model_id'] = $this->user_followed_id;
        $params[':userId'] = $follower->id;

        foreach (Activity::model()->findAllBySql($sql, $params) as $activity) {
            $activity->delete();
        }

        return parent::beforeDelete();
    }

}