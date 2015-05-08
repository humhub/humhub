<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Feed;

use Countable;
use Iterator;

/**
*/
interface FeedInterface extends Iterator, Countable
{
    /**
     * Get a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0);

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors();

    /**
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright();

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated();

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified();

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator();

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId();

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage();

    /**
     * Get a link to the HTML source
     *
     * @return string|null
     */
    public function getLink();

    /**
     * Get a link to the XML feed
     *
     * @return string|null
     */
    public function getFeedLink();

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get all categories
     *
     * @return \Zend\Feed\Reader\Collection\Category
     */
    public function getCategories();

}
