<?php

/**
 * This is the model class for table "user_content".
 *
 * Its propose is to link User with SIContent Objects.
 * So the system (e.g. wall) can identify which user is involved with which
 * piece of content.
 *
 * The followings are the available columns in table 'user_content':
 * @property integer $id
 * @property integer $user_id
 * @property string $object_model
 * @property integer $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke
 */

class UserContent extends HActiveRecord {

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'HUnderlyingObjectBehavior' => array(
                'class' => 'application.behaviors.HUnderlyingObjectBehavior',
                'mustBeInstanceOf' => array('HContentBehavior'),
            ),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserContent the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_content';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, object_model, object_id', 'required'),
            array('user_id, object_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('created_at, created_by, updated_at, updated_by', 'safe'),
            array('object_model', 'length', 'max' => 100),
        );
    }

}