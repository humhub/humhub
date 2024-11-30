<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\modules\content\Module;
use humhub\services\MetaSearchService;
use Yii;

/**
 * Content Meta Search Provider
 *
 * @author luke
 * @since 1.16
 */
class ContentSearchProvider implements MetaSearchProviderInterface
{
    private ?MetaSearchService $service = null;
    public ?string $keyword = null;
    public string|array|null $route = '/content/search';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Yii::t('ContentModule.base', 'Content');
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder(): int
    {
        return 100;
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
            : Yii::t('ContentModule.base', 'Advanced Content Search');
    }

    /**
     * @inheritdoc
     */
    public function getIsHiddenWhenEmpty(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getResults(int $maxResults): array
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('content');

        $resultSet = $module->getSearchDriver()->search(new SearchRequest([
            'keyword' => $this->getKeyword(),
            'pageSize' => $maxResults,
            'orderBy' => SearchRequest::ORDER_BY_SCORE,
        ]));

        $results = [];
        foreach ($resultSet->results as $content) {
            $results[] = Yii::createObject(SearchRecord::class, [$content, $this->getKeyword()]);
        }

        return [
            'totalCount' => $resultSet->pagination->totalCount,
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
