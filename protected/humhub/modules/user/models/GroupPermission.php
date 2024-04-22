<?php

namespace humhub\modules\user\models;

use humhub\libs\BasePermission;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "group_permission".
 *
 * @property string $permission_id
 * @property int $group_id
 * @property string $module_id
 * @property string $class
 * @property int $state
 */
class GroupPermission extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_permission';
    }

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
            [['permission_id', 'module_id', 'class'], 'string', 'max' => 255],
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
