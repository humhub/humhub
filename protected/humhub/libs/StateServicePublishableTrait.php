<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\interfaces\StatableInterface;
use humhub\modules\activity\helpers\ActivityHelper;
use humhub\modules\content\activities\ContentCreated;

/**
 * Adds methods for publishable records
 *
 * @since 1.16
 */
trait StateServicePublishableTrait
{
    public function isPublished(): bool
    {
        return $this->is(StatableInterface::STATE_PUBLISHED);
    }

    public function wasPublished(): bool
    {
        $activityQuery = ActivityHelper::getActivitiesQuery($this->record->content->getPolymorphicRelation());

        if ($activityQuery === null) {
            return false;
        }

        $contentCreatedActivity = new ContentCreated();

        return $activityQuery
            ->andWhere(['class' => get_class($contentCreatedActivity)])
            ->andWhere(['module' => $contentCreatedActivity->moduleId])
            ->exists();
    }

    public function publish(): bool
    {
        return $this->update(StatableInterface::STATE_PUBLISHED);
    }
}
