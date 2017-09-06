<?php

namespace humhub\modules\user\models;
use humhub\libs\BasePermission;


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

    public static function instance(BasePermission $basePermission, $groupId = null, $state = null) {
        $instance = new static([
            'permission_id' => $basePermission->getId(),
            'module_id' => $basePermission->getModuleId(),
            'class' => $basePermission->className()
        ]);

        if(!empty($groupId)) {
            $instance->group_id = ($groupId instanceof Group) ? $groupId->id : $groupId;
        }

        $instance->state = $state;
        return $instance;
    }
    
    public function init()
    {
        parent::init();
        $this->class = static::className();
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
