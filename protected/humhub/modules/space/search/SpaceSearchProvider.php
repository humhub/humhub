<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\search;

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\modules\space\components\SpaceDirectoryQuery;
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
        $spaceDirectoryQuery = new SpaceDirectoryQuery([
            'defaultFilters' => ['keyword' => $this->getKeyword()],
            'pageSize' => $maxResults,
        ]);

        $results = [];
        foreach ($spaceDirectoryQuery->all() as $space) {
            $results[] = Yii::createObject(SearchRecord::class, [$space]);
        }

        return [
            'totalCount' => $spaceDirectoryQuery->pagination->totalCount,
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
