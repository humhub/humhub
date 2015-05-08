<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Entry;

use Zend\Feed\Reader\Collection\Category;

interface EntryInterface
{
    /**
     * Get the specified author
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
     * Get the entry content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated();

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified();

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get the entry enclosure
     *
     * @return \stdClass
     */
    public function getEnclosure();

    /**
     * Get the entry ID
     *
     * @return string
     */
    public function getId();

    /**
     * Get a specific link
     *
     * @param  int $index
     * @return string
     */
    public function getLink($index = 0);

    /**
     * Get all links
     *
     * @return array
     */
    public function getLinks();

    /**
     * Get a permalink to the entry
     *
     * @return string
     */
    public function getPermalink();

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get the number of comments/replies for current entry
     *
     * @return int
     */
    public function getCommentCount();

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink();

    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function getCommentFeedLink();

    /**
     * Get all categories
     *
     * @return Category
     */
    public function getCategories();
}
