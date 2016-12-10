<?php

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;

/**
 * This is the model class for table "wall_entry".
 *
 * @property integer $id
 * @property integer $wall_id
 * @property integer $content_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class WallEntry extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wall_entry';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wall_id', 'content_id'], 'required'],
            [['wall_id', 'content_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wall_id' => 'Wall ID',
            'content_id' => 'Content ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }

}
