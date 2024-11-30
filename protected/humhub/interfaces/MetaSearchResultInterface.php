<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\interfaces;

/**
 * Meta Search Result Interface
 * @since 1.16
 */
interface MetaSearchResultInterface
{
    /**
     * Get image of the Search Record
     *
     * @return string
     */
    public function getImage(): string;

    /**
     * Get title of the Search Record
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get description of the Search Record
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get URL of the Search Record
     *
     * @return string
     */
    public function getUrl(): string;
}
