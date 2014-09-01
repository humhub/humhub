<?php

/**
 * This is the model class for table "user_module".
 * It holds all activated user modules.
 *
 * The followings are the available columns in table 'user_module':
 * @property integer $id
 * @property string $module_id
 * @property integer $user_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 *
 * @author Luke
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class UserApplicationModule extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return SpaceApplicationModule the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_module';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('module_id, user_id', 'required'),
            array('user_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('module_id', 'length', 'max' => 255),
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

}