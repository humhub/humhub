<?php

namespace humhub\modules\content\activities;

use Yii;
use humhub\modules\activity\components\BaseContentActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;


final class ContentCreatedActivity extends BaseContentActivity implements ConfigurableActivityInterface
{
    public int $groupingTimeBucketSeconds = 900;

    /*
    public ?int $groupingThreshold = 4;
    */

    public static function getTitle(): string
    {
        return Yii::t('ContentModule.activities', 'Contents');
    }

    public static function getDescription(): string
    {
        return Yii::t('ContentModule.activities', 'Whenever a new content (e.g. post) has been created.');
    }

    protected function getMessage(array $params): string
    {
        if ($this->groupCount > 1) {
            return Yii::t(
                'ContentModule.activities',
                '{displayName} created a new {contentTitle} and {groupCount} others.',
                $params,
            );
        } else {
            return Yii::t(
                'ContentModule.activities',
                '{displayName} created a new {contentTitle}.',
                $params,
            );
        }
    }

    /*
    public function findGroupedQuery(): ActiveQueryActivity
    {
        return Activity::find()
            ->timeBucket($this->groupingTimeBucketSeconds, $this->createdAt)
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id])
            ->andWhere(['activity.created_by' => $this->user->id]);
    }
    */
}
