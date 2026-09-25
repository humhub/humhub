<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\search;

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\modules\space\components\SpaceListQuery;
use humhub\services\MetaSearchService;
use Yii;

/**
 * Space Meta Search Provider
 *
 * @author luke
 * @since 1.16
 */
class SpaceSearchProvider implements MetaSearchProviderInterface
{
    private ?MetaSearchService $service = null;
    public ?string $keyword = null;
    public string|array|null $route = '/space/spaces';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Yii::t('SpaceModule.base', 'Spaces');
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder(): int
    {
        return 300;
    }

    /**
     * @inheritdoc
     */
    public function getRoute(): string|array
    {
        return $this->route;
    }

    /**
     * @inheritdoc
     */
    public function getAllResultsText(): string
    {
        return $this->getService()->hasResults()
            ? Yii::t('base', 'Show all results')
            : Yii::t('SpaceModule.base', 'Advanced Spaces Search');
    }

    /**
     * @inheritdoc
     */
    public function getIsHiddenWhenEmpty(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResults(int $maxResults): array
    {
        // The directory's search (with the restrictions modules apply to it), whose "Show all
        // results" is the directory itself.
        $query = (new SpaceListQuery())->build([
            'q' => (string)$this->getKeyword(),
            'purpose' => SpaceListQuery::PURPOSE_DIRECTORY,
        ]);

        $results = [];
        foreach ((clone $query)->limit($maxResults)->all() as $space) {
            $results[] = Yii::createObject(SearchRecord::class, [$space]);
        }

        return [
            'totalCount' => (int)$query->count(),
            'results' => $results,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getService(): MetaSearchService
    {
        if ($this->service === null) {
            $this->service = new MetaSearchService($this);
        }

        return $this->service;
    }

    /**
     * @inheritdoc
     */
    public function getKeyword(): ?string
    {
        return $this->keyword;
    }
}
