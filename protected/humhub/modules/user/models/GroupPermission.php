<?php

namespace humhub\modules\user\models;



/**
 * This is the model class for table "group_permission".
 *
 * @property string $permission_id
 * @property integer $group_id
 * @property string $module_id
 * @property string $class
 * @property integer $state
 */
class GroupPermission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['permission_id', 'group_id', 'module_id'], 'required'],
            [['group_id', 'state'], 'integer'],
            [['permission_id', 'module_id', 'class'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'permission_id' => 'Permission ID',
            'group_id' => 'Group ID',
            'module_id' => 'Module ID',
            'class' => 'Class',
            'state' => 'State',
        ];
    }
}
