<?php

namespace humhub\modules\content\search\driver;

use humhub\modules\content\models\Content;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use Yii;
use yii\base\Component;
use yii\base\Model;

abstract class AbstractDriver extends Component
{
    abstract public function purge(): void;

    abstract public function update(Content $content): void;

    abstract public function delete(int $contentId): void;

    /**
     * Run search process
     *
     * // Add private content, which is in Space content containers where the user is member of
     * // Add private content, of User content containers where the user is friend or self
     * // Add all public content
     *
     * @param SearchRequest $request
     * @return ResultSet
     */
    abstract public function search(SearchRequest $request): ResultSet;

    /**
     * Run search process and cache results
     *
     * @param SearchRequest $request
     * @param int Number of pages that should be cached, 0 - don't cache
     * @return ResultSet
     */
    public function searchCached(SearchRequest $request, int $cachePageNumber = 0): ResultSet
    {
        if ($cachePageNumber < 1) {
            // Search results without caching
            return $this->search($request);
        }

        // Store original pagination
        $origPage = $request->page - 1;
        $origPageSize = $request->pageSize;

        // Set pagination to load results from DB or Cache with bigger portion than original page size
        $cachePageSize = $origPageSize * $cachePageNumber;
        $cachePage = (int) ceil(($request->page * $origPageSize) / $cachePageSize);
        $request->page = $cachePage;
        $request->pageSize = $cachePageSize;

        /* @var ResultSet $resultSet */
        // Load results from cache or Search & Cache
        $resultSet = Yii::$app->cache->getOrSet($this->getSearchCacheKey($request), function () use ($request) {
            return $this->search($request);
        });

        // Extract part of results only for the current(original requested) page
        $slicePageStart = ($origPage - ($cachePageSize * ($cachePage - 1)) / $origPageSize) * $origPageSize;
        $resultSet->results = array_slice($resultSet->results, $slicePageStart, $origPageSize);

        // Revert original pagination for correct working with AJAX response
        $resultSet->pagination->setPage($origPage);
        $resultSet->pagination->setPageSize($origPageSize);

        return $resultSet;
    }

    protected function getSearchCacheKey(SearchRequest $request): string
    {
        $requestFilters = array_filter($request->getAttributes(), function ($value) {
            return is_scalar($value) || is_array($value);
        });

        return static::class . Yii::$app->user->id . sha1(json_encode($requestFilters));
    }

    public static function rebuild($showDots = false)
    {
        foreach (Content::find()->each() as $content) {
            if ($showDots) {
                print ".";
            }
        }
    }

}
