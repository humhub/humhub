<?php

namespace humhub\modules\space\activities;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use humhub\modules\space\components\BaseSpaceActivity;
use Yii;

class MemberAddedActivity extends BaseSpaceActivity implements ConfigurableActivityInterface
{
    public int $groupingTimeBucketSeconds = 900;

    public ?int $groupingThreshold = 3;

    public static function getTitle(): string
    {
        return Yii::t('SpaceModule.activities', 'Space member joined');
    }

    public static function getDescription(): string
    {
        return Yii::t('SpaceModule.activities', 'Whenever a new member joined one of your spaces.');
    }

    protected function getMessage(array $params): string
    {
        $isGrouped = $this->groupCount > 1;
        $isInSpace = $this->inSpaceContext();

        return match (true) {
            $isGrouped && $isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayNames} joined this Space.',
                $params,
            ),
            $isGrouped && !$isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayNames} joined the Space {spaceName}.',
                $params,
            ),
            !$isGrouped && $isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayName} joined this Space.',
                $params,
            ),
            !$isGrouped && !$isInSpace => Yii::t(
                'SpaceModule.base',
                '{displayName} joined the Space {spaceName}.',
                $params,
            ),
        };
    }

    public function findGroupedQuery(): ActiveQueryActivity
    {
        return Activity::find()
            ->timeBucket($this->groupingTimeBucketSeconds, $this->createdAt)
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id]);
    }
}
