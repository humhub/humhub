<?php

namespace humhub\modules\stream\models\filters;

use humhub\interfaces\StatableInterface;
use humhub\libs\StatableActiveQuery;
use humhub\modules\activity\stream\ActivityStreamQuery;
use humhub\modules\content\models\Content;
use Yii;

/**
 * @since 1.14
 */
class DraftContentStreamFilter extends StreamQueryFilter
{
    /**
     * @var Content[]
     */
    private array $draftContent = [];

    /**
     * @inheritDoc
     */
    public function apply()
    {
        if ($this->streamQuery instanceof ActivityStreamQuery && $this->streamQuery->activity) {
            return;
        }

        if ($this->allowPinContent()) {
            $this->fetchDraftContent();
        } else {
            $this->streamQuery->andWhereState(StatableInterface::STATE_DRAFT);
        }
    }

    /**
     * @return void
     */
    private function fetchDraftContent(): void
    {
        /** @var StatableActiveQuery $draftQuery */
        $draftQuery = clone $this->query;
        $draftQuery->whereState(StatableInterface::STATE_DRAFT);
        $draftQuery->andWhere(['content.created_by' => Yii::$app->user->id]);
        $draftQuery->limit(100);
        $this->draftContent = $draftQuery->all();
    }

    /**
     * @inheritDoc
     */
    public function postProcessStreamResult(array &$results): void
    {
        $results = array_merge($this->draftContent, $results);
    }

}
