<?php

namespace humhub\modules\activity\models;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\services\GroupingService;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use Throwable;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "activity".
 *
 * @property int $id
 * @property string $class
 * @property int|null $contentcontainer_id
 * @property int $content_id
 * @property int $content_addon_record_id
 * @property string $grouping_key
 * @property int $created_by
 * @property string $created_at
 *
 * @property-read Content|null $content
 * @property-read ContentContainer|null $contentContainer
 */
class Activity extends \humhub\components\ActiveRecord
{
    public ?int $group_count = null;
    public ?int $group_max_id = null;

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

    public function beforeDelete()
    {
        // The Activity target (Content/ContentContainer/content object) may already be
        // gone at this point, e.g. when this Activity is cleaned up as a side effect of
        // deleting its orphaned target (see IntegrityController, RecordMap cleanup).
        // Loading the full activity object for grouping purposes must not block deletion
        // of an otherwise broken Activity record in that case.
        try {
            (new GroupingService(ActivityManager::load($this)))->beforeDelete();
        } catch (Throwable $e) {
            Yii::warning('Could not load Activity #' . $this->id . ' for grouping before delete: ' . $e->getMessage(), 'activity');
        }

        return parent::beforeDelete();
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
