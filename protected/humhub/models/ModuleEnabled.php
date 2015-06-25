<?php

namespace humhub\models;

use Yii;

/**
 * This is the model class for table "module_enabled".
 *
 * @property string $module_id
 */
class ModuleEnabled extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'module_enabled';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id'], 'required'],
            [['module_id'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'module_id' => 'Module ID',
        ];
    }
}
