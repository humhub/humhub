<?php

/**
 * This is the model class for table "user_follow".
 *
 * The followings are the available columns in table 'user_follow':
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property integer $user_id
 * @property integer $send_notifications
 */
class UserFollow extends HActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Follow the static model class
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
        return 'user_follow';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('object_model, object_id, user_id', 'required'),
            array('object_id, user_id', 'numerical', 'integerOnly' => true),
            array('object_model', 'length', 'max' => 100),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, object_model, object_id, user_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord) {

            // ToDo: Handle this via event of User Module
            if ($this->object_model == 'User') {
                // Create Wall Activity for that
                $activity = new Activity;
                $activity->content->user_id = $this->user->id;
                $activity->content->visibility = Content::VISIBILITY_PUBLIC;
                $activity->type = "ActivityUserFollowsUser";
                $activity->object_model = "User";
                $activity->object_id = $this->object_id;
                $activity->save();
                $activity->content->addToWall($this->user->wall_id);
            }
        }

        return parent::beforeSave();
    }
    
    public function afterSave(){
        
        if($this->isNewRecord && $this->object_model=='User'){
            FollowNotification::fire($this);
        }
    }

    protected function beforeDelete()
    {

        // ToDo: Handle this via event of User Module
        if ($this->object_model == 'User') {
            $user = User::model()->findByPk($this->user_id);
            $activity = Activity::model()->contentContainer($user)->findByAttributes(array('type' => "ActivityUserFollowsUser", 'object_id' => $this->object_id));
            if ($activity !== null) {
                $activity->delete();
            }
        }

        FollowNotification::remove('UserFollow', $this->id);
        return parent::beforeDelete();
    }

}
