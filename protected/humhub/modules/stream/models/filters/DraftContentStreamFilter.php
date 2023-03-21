<?php

namespace humhub\modules\stream\models\filters;

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

        if ($this->streamQuery->isInitialQuery()) {
            $this->fetchDraftContent();
        } else {
            $this->streamQuery->stateFilterCondition[] = ['content.state' => Content::STATE_DRAFT];
        }
    }

    /**
     * @return void
     */
    private function fetchDraftContent(): void
    {
        $draftQuery = clone $this->query;
        $draftQuery->andWhere([
                'AND', ['content.state' => Content::STATE_DRAFT],
                ['content.created_by' => Yii::$app->user->id]]
        );
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
