<?php

namespace humhub\modules\live\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\models\ContentContainer;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "live".
 *
 * @property int $id
 * @property int $contentcontainer_id
 * @property int $visibility
 * @property string $serialized_data
 * @property int $created_at
 *
 * @property Contentcontainer $contentcontainer
 */
class Live extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contentcontainer_id', 'visibility', 'created_at'], 'integer'],
            [['serialized_data', 'created_at'], 'required'],
            [['serialized_data'], 'string'],
            [['contentcontainer_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentContainer::class, 'targetAttribute' => ['contentcontainer_id' => 'id']],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getContentcontainer()
    {
        return $this->hasOne(Contentcontainer::class, ['id' => 'contentcontainer_id']);
    }

}
