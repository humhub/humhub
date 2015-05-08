<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Extension\DublinCore;

use DateTime;
use Zend\Feed\Reader;
use Zend\Feed\Reader\Collection;
use Zend\Feed\Reader\Extension;

class Feed extends Extension\AbstractFeed
{
    /**
     * Get a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        $authors = $this->getAuthors();

        if (isset($authors[$index])) {
            return $authors[$index];
        }

        return null;
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (array_key_exists('authors', $this->data)) {
            return $this->data['authors'];
        }

        $authors = array();
        $list    = $this->getXpath()->query('//dc11:creator');

        if (!$list->length) {
            $list = $this->getXpath()->query('//dc10:creator');
        }
        if (!$list->length) {
            $list = $this->getXpath()->query('//dc11:publisher');

            if (!$list->length) {
                $list = $this->getXpath()->query('//dc10:publisher');
            }
        }

        if ($list->length) {
            foreach ($list as $author) {
                $authors[] = array(
                    'name' => $author->nodeValue
                );
            }
            $authors = new Collection\Author(
                Reader\Reader::arrayUnique($authors)
            );
        } else {
            $authors = null;
        }

        $this->data['authors'] = $authors;

        return $this->data['authors'];
    }

    /**
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright()
    {
        if (array_key_exists('copyright', $this->data)) {
            return $this->data['copyright'];
        }

        $copyright = null;
        $copyright = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc11:rights)');

        if (!$copyright) {
            $copyright = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc10:rights)');
        }

        if (!$copyright) {
            $copyright = null;
        }

        $this->data['copyright'] = $copyright;

        return $this->data['copyright'];
    }

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if (array_key_exists('description', $this->data)) {
            return $this->data['description'];
        }

        $description = null;
        $description = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc11:description)');

        if (!$description) {
            $description = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc10:description)');
        }

        if (!$description) {
            $description = null;
        }

        $this->data['description'] = $description;

        return $this->data['description'];
    }

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId()
    {
        if (array_key_exists('id', $this->data)) {
            return $this->data['id'];
        }

        $id = null;
        $id = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc11:identifier)');

        if (!$id) {
            $id = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc10:identifier)');
        }

        $this->data['id'] = $id;

        return $this->data['id'];
    }

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (array_key_exists('language', $this->data)) {
            return $this->data['language'];
        }

        $language = null;
        $language = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc11:language)');

        if (!$language) {
            $language = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc10:language)');
        }

        if (!$language) {
            $language = null;
        }

        $this->data['language'] = $language;

        return $this->data['language'];
    }

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (array_key_exists('title', $this->data)) {
            return $this->data['title'];
        }

        $title = null;
        $title = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc11:title)');

        if (!$title) {
            $title = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc10:title)');
        }

        if (!$title) {
            $title = null;
        }

        $this->data['title'] = $title;

        return $this->data['title'];
    }

    /**
     *
     *
     * @return DateTime|null
     */
    public function getDate()
    {
        if (array_key_exists('date', $this->data)) {
            return $this->data['date'];
        }

        $d = null;
        $date = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc11:date)');

        if (!$date) {
            $date = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/dc10:date)');
        }

        if ($date) {
            $d = new DateTime($date);
        }

        $this->data['date'] = $d;

        return $this->data['date'];
    }

    /**
     * Get categories (subjects under DC)
     *
     * @return Collection\Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->data)) {
            return $this->data['categories'];
        }

        $list = $this->getXpath()->evaluate($this->getXpathPrefix() . '//dc11:subject');

        if (!$list->length) {
            $list = $this->getXpath()->evaluate($this->getXpathPrefix() . '//dc10:subject');
        }

        if ($list->length) {
            $categoryCollection = new Collection\Category;
            foreach ($list as $category) {
                $categoryCollection[] = array(
                    'term' => $category->nodeValue,
                    'scheme' => null,
                    'label' => $category->nodeValue,
                );
            }
        } else {
            $categoryCollection = new Collection\Category;
        }

        $this->data['categories'] = $categoryCollection;
        return $this->data['categories'];
    }

    /**
     * Register the default namespaces for the current feed format
     *
     * @return void
     */
    protected function registerNamespaces()
    {
        $this->getXpath()->registerNamespace('dc10', 'http://purl.org/dc/elements/1.0/');
        $this->getXpath()->registerNamespace('dc11', 'http://purl.org/dc/elements/1.1/');
    }
}
