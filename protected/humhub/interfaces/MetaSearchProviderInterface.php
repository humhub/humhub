<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\services\MetaSearchService;

/**
 * Meta Search Provider Interface
 * @since 1.16
 */
interface MetaSearchProviderInterface
{
    /**
     * Get name of the Search Provider
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * Get text of link to go to all results
     *
     * @return string
     */
    public function getAllResultsText(): string;

    /**
     * Get a route to provider page
     *
     * @return string|array
     */
    public function getRoute(): string|array;

    /**
     * Run search process to get results
     *
     * @param int $maxResults
     * @return array 'totalCount' - Number of total searched records,
     *               'results'    - Array of searched records MetaSearchResultInterface[]
     */
    public function getResults(int $maxResults): array;

    /**
     * @return bool True if provider should be hidden when no results are found
     */
    public function getIsHiddenWhenEmpty(): bool;

    /**
     * Get a service for searching
     *
     * @return MetaSearchService
     */
    public function getService(): MetaSearchService;

    /**
     * Get a current keyword
     *
     * @return string|null
     */
    public function getKeyword(): ?string;
}
