<?php

/**
 * This is the model class for table "wall".
 *
 * The followings are the available columns in table 'wall':
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property User[] $users
 * @property User $createdBy
 * @property User $updatedBy
 * @property WallEntry[] $wallEntries
 * @property Space[] $workspaces
 *
 * @package humhub.modules_core.wall.models
 */
class Wall extends HActiveRecord
{

    /**
     * @deprecated since version 0.11
     */
    const TYPE_USER = 'User';
    const TYPE_SPACE = 'Space';
    const TYPE_DASHBOARD = 'Dashboard';
    const TYPE_COMMUNITY = 'Community';

    /**
     * Add mix-ins to this model
     *
     * @return type
     */

    public function behaviors()
    {
        return array(
            'HUnderlyingObjectBehavior' => array(
                'class' => 'application.behaviors.HUnderlyingObjectBehavior',
                'mustBeInstanceOf' => array('HActiveRecordContentContainer'),
            ),
        );
    }

    /**
     * Saves the current Wall Mode, find maybe a better place for this var.
     *
     * Wall::$currentType = "dashboard";  // user, workspace
     * 
     * @deprecated since version 0.11
     */
    static $currentType = "";

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Wall the static model class
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
        return 'wall';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('created_at, updated_at', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'users' => array(self::HAS_MANY, 'User', 'wall_id'),
            'createdBy' => array(self::BELONGS_TO, 'User', 'created_by'),
            'updatedBy' => array(self::BELONGS_TO, 'User', 'updated_by'),
            'entries' => array(self::HAS_MANY, 'WallEntry', 'wall_id', 'order' => 'updated_at DESC'),
            'workspaces' => array(self::HAS_MANY, 'Space', 'wall_id'),
        );
    }

}
