<?php

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;

/**
 * This is the model class for table "wall".
 *
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Wall extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wall';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_model', 'object_id'], 'required'],
            [['object_id'], 'integer'],
            [['object_model'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_model' => 'Object Model',
            'object_id' => 'Object ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }
}
