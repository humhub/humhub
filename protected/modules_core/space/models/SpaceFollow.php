<?php

/**
 * This is the model class for table "space_follow".
 *
 * The followings are the available columns in table 'space_follow':
 * @property integer $user_id
 * @property integer $space_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 *
 * @author Luke
 * @package humhub.modules_core.space.models
 * @since 0.5
 */
class SpaceFollow extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return SpaceFollow the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'space_follow';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, space_id', 'required'),
            array('user_id, space_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('created_at, updated_at', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('user_id, space_id, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'workspace' => array(self::BELONGS_TO, 'Space', 'space_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'user_id' => 'User',
            'space_id' => 'Space',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('space_id', $this->space_id);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Follow a workspace
     *
     * @param $workspaceId
     * @param $userId
     *
     * @return Boolean Success
     */
    public static function follow($workspaceId, $userId) {

        $workspace = Space::model()->findByAttributes(array('id' => $workspaceId));
        $user = User::model()->findByAttributes(array('id' => $userId));

        // Invalid Space?
        if ($workspace == null)
            throw new CHttpException(404, 'Space not found!');

        // Invalid User?
        if ($user == null)
            throw new CHttpException(404, 'User not found!');

        // Really not folllowed yet?
        if (!$workspace->isFollowedBy($user->id) && !$workspace->isMember($user->id)) {

            // Create Follower Object
            $follow = new SpaceFollow;
            $follow->space_id = $workspace->id;
            $follow->user_id = $userId;
            $follow->save();


            return true;
        }

        return false;
    }

    /**
     * Unfollow a workspace
     *
     * @param $workspaceId
     * @param $userId
     *
     * @return Boolean Success
     */
    public static function unfollow($workspaceId, $userId) {

        $workspace = Space::model()->findByPk($workspaceId);
        $user = User::model()->findByPk($userId);

        // Invalid Space?
        if ($workspace == null)
            throw new CHttpException(404, 'Space not found!');

        // User not found?
        if ($workspace == null)
            throw new CHttpException(404, 'Space not found!');

        if ($workspace->isFollowedBy($userId)) {

            SpaceFollow::model()->deleteAllByAttributes(array(
                'user_id' => Yii::app()->user->id,
                'space_id' => $workspace->id,
            ));

            return true;
        }

        return false;
    }

}