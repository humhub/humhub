<?php

namespace humhub\modules\content\models;



/**
 * This is the model class for table "contentcontainer_permission".
 *
 * @property string $permission_id
 * @property integer $contentcontainer_id
 * @property string $group_id
 * @property string $module_id
 * @property string $class
 * @property integer $state
 */
class ContentContainerPermission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['permission_id', 'contentcontainer_id', 'group_id', 'module_id'], 'required'],
            [['contentcontainer_id', 'state'], 'integer'],
            [['permission_id', 'group_id', 'module_id', 'class'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'permission_id' => 'Permission ID',
            'contentcontainer_id' => 'Contentcontainer ID',
            'group_id' => 'Role ID',
            'module_id' => 'Module ID',
            'class' => 'Class',
            'state' => 'State',
        ];
    }
}
