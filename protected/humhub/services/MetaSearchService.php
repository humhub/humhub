<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\interfaces\MetaSearchResultInterface;
use Yii;
use yii\helpers\Url;

/**
 * Meta Search Service
 *
 * @author luke
 * @since 1.16
 */
class MetaSearchService
{
    public int $pageSize = 4;
    public int $cacheTimeout = 180;

    protected ?int $totalCount = null;
    protected ?MetaSearchProviderInterface $provider = null;

    public function __construct(MetaSearchProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @var MetaSearchResultInterface[]|null $results
     */
    protected ?array $results = null;

    /**
     * @var string|null $route Route to the searching page with all filters
     */
    protected ?string $route = null;

    /**
     * Run search process and cache results
     *
     * @return void
     */
    public function search(): void
    {
        if ($this->provider->getKeyword() === null) {
            return;
        }

        $providerParams = $this->provider->getParams();
        $cacheKey = get_class($this->provider) .
            (empty($providerParams) ? '' : sha1(json_encode($providerParams))) .
            ':' . Yii::$app->user->id .
            ':' . $this->provider->getKeyword();

        $data = Yii::$app->cache->getOrSet($cacheKey, function () {
            return $this->provider->getResults($this->pageSize);
        }, $this->cacheTimeout);

        $this->totalCount = $data['totalCount'] ?? 0;
        $this->results = $data['results'] ?? [];
    }

    /**
     * Get URL to all results filtered with keyword
     *
     * @return string
     */
    public function getUrl(): string
    {
        $params = [$this->provider->getRoute()];
        if ($this->provider->getKeyword() !== null && $this->hasResults()) {
            $params['keyword'] = $this->provider->getKeyword();
        }

        return Url::to($params);
    }

    /**
     * Check if a searching has been done
     *
     * @return bool
     */
    public function isSearched(): bool
    {
        return $this->results !== null;
    }

    /**
     * Get number of results
     *
     * @return int
     */
    public function getTotal(): int
    {
        return isset($this->totalCount) ? (int) $this->totalCount : 0;
    }

    /**
     * Has at least one searched result
     *
     * @return bool
     */
    public function hasResults(): bool
    {
        return !empty($this->results);
    }

    /**
     * Get searched results
     *
     * @return MetaSearchResultInterface[]
     */
    public function getResults(): array
    {
        return $this->results ?? [];
    }
}
