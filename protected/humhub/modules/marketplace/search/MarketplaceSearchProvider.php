<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\search;

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\modules\marketplace\Module;
use humhub\services\MetaSearchService;
use Yii;

/**
 * Marketplace Modules Meta Search Provider
 *
 * @author luke
 * @since 1.16
 */
class MarketplaceSearchProvider implements MetaSearchProviderInterface
{
    private ?MetaSearchService $service = null;
    public ?string $keyword = null;
    public string|array|null $route = '/marketplace/browse';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Yii::t('MarketplaceModule.base', 'Marketplace');
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder(): int
    {
        return 500;
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
            : Yii::t('MarketplaceModule.base', 'Advanced Module Search');
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
        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $notInstalledModules = $marketplaceModule->onlineModuleManager->getNotInstalledModules();

        $filteredModules = Yii::$app->moduleManager->filterModules($notInstalledModules, ['keyword' => $this->getKeyword()]);

        $results = [];
        foreach ($filteredModules as $module) {
            $results[] = Yii::createObject(SearchRecord::class, [$module]);
            if (count($results) === $maxResults) {
                break;
            }
        }

        return [
            'totalCount' => count($filteredModules),
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
