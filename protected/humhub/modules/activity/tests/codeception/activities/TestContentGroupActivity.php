<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;

class TestContentGroupActivity extends BaseActivity
{
    public int $groupingThreshold = 4;
    public int $groupingTimeBucketSeconds = 900;

    protected function getMessage(array $params): string
    {
        if ($this->groupCount > 1) {
            return 'Grouped Activity (Total: 5)';
        } else {
            return 'Single Activity';
        }
    }

    public function findGroupedQuery(): ?ActiveQueryActivity
    {
        return Activity::find()
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id])
            ->andWhere(['activity.content_id' => $this->record->content_id]);
    }
}
