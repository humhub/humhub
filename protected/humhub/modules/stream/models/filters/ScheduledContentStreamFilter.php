<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\models\filters;

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
        if (!$this->allowStateContent()) {
            return;
        }

        if ($this->allowPinContent()) {
            $this->fetchScheduledContent();
        } elseif (!Yii::$app->user->isGuest) {
            $this->streamQuery->stateFilterCondition[] = $this->getScheduledCondition();
        }
    }

    private function getScheduledCondition(): array
    {
        return ['AND',
            ['content.state' => Content::STATE_SCHEDULED],
            ['content.created_by' => Yii::$app->user->id],
        ];
    }

    /**
     * @return void
     */
    private function fetchScheduledContent(): void
    {
        $scheduledQuery = clone $this->query;
        $scheduledQuery->andWhere($this->getScheduledCondition());
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
