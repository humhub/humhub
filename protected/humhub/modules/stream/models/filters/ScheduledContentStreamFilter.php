<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\models\filters;

use humhub\components\StatableActiveQuery;
use humhub\modules\activity\stream\ActivityStreamQuery;
use humhub\modules\content\models\Content;
use Yii;

/**
 * @since 1.14
 */
class ScheduledContentStreamFilter extends StreamQueryFilter
{
    /**
     * @var Content[]
     */
    private array $scheduledContent = [];

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if ($this->streamQuery instanceof ActivityStreamQuery && $this->streamQuery->activity) {
            return;
        }

        if ($this->allowPinContent()) {
            $this->fetchScheduledContent();
        } else {
            $this->streamQuery->andWhereState(Content::STATE_SCHEDULED);
        }
    }

    /**
     * @return void
     */
    private function fetchScheduledContent(): void
    {
        /** @var StatableActiveQuery $scheduledQuery */
        $scheduledQuery = clone $this->query;
        $scheduledQuery->whereState(Content::STATE_SCHEDULED);
        $scheduledQuery->andWhere(['content.created_by' => Yii::$app->user->id]);
        $scheduledQuery->limit(100);
        $this->scheduledContent = $scheduledQuery->all();
    }

    /**
     * @inheritdoc
     */
    public function postProcessStreamResult(array &$results): void
    {
        $results = array_merge($this->scheduledContent, $results);
    }
}
