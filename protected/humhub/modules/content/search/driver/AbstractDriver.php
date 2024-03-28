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
    public ?SearchRequest $request = null;

    abstract public function purge(): void;

    abstract public function update(Content $content): void;

    abstract public function delete(Content $content): void;

    /**
     * Run search process, result may be cached
     *
     * // Add private content, which is in Space content containers where the user is member of
     * // Add private content, of User content containers where the user is friend or self
     *
     * // Add all public content
     * @return ResultSet
     */
    abstract public function runSearch(): ResultSet;

    /**
     * Run search process and cache results
     *
     * @param SearchRequest $request
     * @return ResultSet
     */
    public function search(SearchRequest $request): ResultSet
    {
        $this->request = $request;

        if ($this->request->cachePageNumber < 1) {
            // Search results without caching
            return $this->runSearch();
        }

        // Store original pagination
        $origPage = $this->request->page - 1;
        $origPageSize = $this->request->pageSize;

        // Set pagination to load results from DB or Cache with bigger portion than original page size
        $cachePageSize = $origPageSize * $this->request->cachePageNumber;
        $cachePage = (int) ceil(($this->request->page * $origPageSize) / $cachePageSize);
        $this->request->page = $cachePage;
        $this->request->pageSize = $cachePageSize;

        /* @var ResultSet $resultSet */
        // Load results from cache or Search & Cache
        $resultSet = Yii::$app->cache->getOrSet($this->getSearchCacheKey(), [$this, 'runSearch']);

        // Extract part of results only for the current(original requested) page
        $slicePageStart = ($origPage - ($cachePageSize * ($cachePage - 1)) / $origPageSize) * $origPageSize;
        $resultSet->results = array_slice($resultSet->results, $slicePageStart, $origPageSize);

        // Revert original pagination for correct working with AJAX response
        $resultSet->pagination->setPage($origPage);
        $resultSet->pagination->setPageSize($origPageSize);

        return $resultSet;
    }

    protected function getSearchCacheKey(): string
    {
        if ($this->request instanceof Model) {
            $requestFilters = array_filter($this->request->getAttributes(), function ($value) {
                return is_scalar($value) || is_array($value);
            });
        } else {
            $requestFilters = [];
        }

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
