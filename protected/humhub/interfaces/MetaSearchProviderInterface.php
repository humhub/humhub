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
     * Get text of link to go to all results
     *
     * @return string
     */
    public function getAllResultsText(): string;

    /**
     * Get a route to provider page
     *
     * @return string
     */
    public function getRoute(): string;

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

    /**
     * @return array|null Additional params which may be used to initialize the provider
     */
    public function getParams(): ?array;
}
