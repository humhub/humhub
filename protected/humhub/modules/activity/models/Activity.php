<?php

namespace humhub\modules\activity\models;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "activity".
 *
 * @property int $id
 * @property string $class
 * @property int $contentcontainer_id
 * @property int $content_id
 * @property int $content_addon_record_id
 * @property int $created_by
 * @property string $created_at
 *
 * @property-read Content $content
 * @property-read ContentContainer $contentContainer
 */
class Activity extends \humhub\components\ActiveRecord
{

    public static function tableName()
    {
        return 'activity';
    }

    public function getContent(): ActiveQuery
    {
        return $this->hasOne(Content::class, ['id' => 'content_id']);
    }

    public function getContentContainer(): ActiveQuery
    {
        return $this->hasOne(ContentContainer::class, ['id' => 'contentcontainer_id']);
    }

    public static function find(): ActiveQueryActivity
    {
        return new ActiveQueryActivity(static::class);
    }

}
