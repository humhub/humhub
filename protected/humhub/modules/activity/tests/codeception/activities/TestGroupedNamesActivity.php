<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;

/**
 * Renders nothing but the grouped display names, so a test can assert the phrase itself
 * ({@see BaseActivity::formatDisplayNames()}) rather than a sentence around it.
 */
class TestGroupedNamesActivity extends BaseActivity
{
    public int $groupingThreshold = 2;
    public int $groupingTimeBucketSeconds = 900;

    protected function getMessage(array $params): string
    {
        return $this->groupCount > 1 ? $params['displayNames'] : $params['displayName'];
    }

    public function getGroupingQuery(): ?ActiveQueryActivity
    {
        return Activity::find()
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->contentContainer->id])
            ->andWhere(['activity.content_id' => $this->record->content_id]);
    }
}
