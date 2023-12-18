<?php

namespace humhub\components;

use yii\db\ActiveRecord;

/**
 * This is the base model class for permission tables.
 *
 * @property string $permission_id
 * @property integer|string $group_id
 * @property string $module_id
 * @property string $class
 * @property integer $state
 */
abstract class PermissionActiveRecord extends ActiveRecord
{

    public function init()
    {
        parent::init();
        $this->class = static::class;
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
