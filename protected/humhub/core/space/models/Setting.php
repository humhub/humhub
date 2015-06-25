<?php

namespace humhub\core\space\models;

use Yii;

/**
 * This is the model class for table "space_setting".
 *
 * @property integer $id
 * @property integer $space_id
 * @property string $module_id
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Setting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['module_id', 'name'], 'string', 'max' => 100],
            [['value'], 'string', 'max' => 255],
            [['space_id', 'module_id', 'name'], 'unique', 'targetAttribute' => ['space_id', 'module_id', 'name'], 'message' => 'The combination of Space ID, Module ID and Name has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'space_id' => 'Space ID',
            'module_id' => 'Module ID',
            'name' => 'Name',
            'value' => 'Value',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }
}
