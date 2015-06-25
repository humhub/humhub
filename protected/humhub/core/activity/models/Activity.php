<?php

namespace humhub\core\activity\models;

use Yii;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string $type
 * @property string $module
 * @property string $object_model
 * @property string $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Activity extends \humhub\core\content\components\activerecords\Content
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['type'], 'string', 'max' => 45],
            [['module', 'object_model', 'object_id'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'module' => 'Module',
            'object_model' => 'Object Model',
            'object_id' => 'Object ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
