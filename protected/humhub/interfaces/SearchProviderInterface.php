<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\interfaces;

/**
 * Search Provider Interface
 * @since 1.16
 */
interface SearchProviderInterface
{
    /**
     * Get name of the Search Provider
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get URL to all results
     *
     * @return string
     */
    public function getAllResultsUrl(): string;

    /**
     * Get number of results
     *
     * @return int
     */
    public function getTotal(): int;

    /**
     * Search results
     *
     * @return void
     */
    public function search(): void;

    /**
     * Check if a searching has been done
     *
     * @return bool
     */
    public function isSearched(): bool;
}
