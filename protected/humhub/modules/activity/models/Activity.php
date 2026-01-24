<?php

namespace humhub\modules\activity\models;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\services\GroupingService;
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
 * @property string $grouping_key
 * @property int $created_by
 * @property string $created_at
 *
 * @property-read int $groupCount
 * @property-read Content $content
 * @property-read ContentContainer $contentContainer
 */
class Activity extends \humhub\components\ActiveRecord
{
    public ?int $_group_count = null;

    public static function tableName()
    {
        return 'activity';
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->updateAttributes(['grouping_key' => $this->id]);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        (new GroupingService(ActivityManager::load($this)))->afterDelete();
        parent::afterDelete();
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

    public function getGroupCount(): int
    {
        if ($this->_group_count === null) {
            $this->_group_count = Activity::find()->where(['grouping_key' => $this->grouping_key])->count();
        }

        return $this->_group_count;
    }
}
