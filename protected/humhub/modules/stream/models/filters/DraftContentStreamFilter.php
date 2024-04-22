<?php

namespace humhub\modules\stream\models\filters;

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
        if (!$this->allowStateContent()) {
            return;
        }

        if ($this->allowPinContent()) {
            $this->fetchDraftContent();
        } elseif (!Yii::$app->user->isGuest) {
            $this->streamQuery->stateFilterCondition[] = $this->getDraftCondition();
        }
    }

    private function getDraftCondition(): array
    {
        return ['AND',
            ['content.state' => Content::STATE_DRAFT],
            ['content.created_by' => Yii::$app->user->id],
        ];
    }

    /**
     * @return void
     */
    private function fetchDraftContent(): void
    {
        $draftQuery = clone $this->query;
        $draftQuery->andWhere($this->getDraftCondition());
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
