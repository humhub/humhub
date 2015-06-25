<?php

namespace humhub\core\space\models;

use Yii;

/**
 * This is the model class for table "space_module".
 *
 * @property integer $id
 * @property string $module_id
 * @property integer $space_id
 * @property integer $state
 */
class Module extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_module';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id', 'space_id'], 'required'],
            [['space_id', 'state'], 'integer'],
            [['module_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module_id' => 'Module ID',
            'space_id' => 'Space ID',
            'state' => 'State',
        ];
    }
}
