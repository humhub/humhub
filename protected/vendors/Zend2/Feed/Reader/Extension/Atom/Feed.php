<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Extension\Atom;

use DateTime;
use DOMElement;
use Zend\Feed\Reader;
use Zend\Feed\Reader\Collection;
use Zend\Feed\Reader\Extension;
use Zend\Feed\Uri;

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
     * @return Collection\Author
     */
    public function getAuthors()
    {
        if (array_key_exists('authors', $this->data)) {
            return $this->data['authors'];
        }

        $list = $this->xpath->query('//atom:author');

        $authors = array();

        if ($list->length) {
            foreach ($list as $author) {
                $author = $this->getAuthorFromElement($author);
                if (!empty($author)) {
                    $authors[] = $author;
                }
            }
        }

        if (count($authors) == 0) {
            $authors = new Collection\Author();
        } else {
            $authors = new Collection\Author(
                Reader\Reader::arrayUnique($authors)
            );
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

        if ($this->getType() === Reader\Reader::TYPE_ATOM_03) {
            $copyright = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:copyright)');
        } else {
            $copyright = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:rights)');
        }

        if (!$copyright) {
            $copyright = null;
        }

        $this->data['copyright'] = $copyright;

        return $this->data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return DateTime|null
     */
    public function getDateCreated()
    {
        if (array_key_exists('datecreated', $this->data)) {
            return $this->data['datecreated'];
        }

        $date = null;

        if ($this->getType() === Reader\Reader::TYPE_ATOM_03) {
            $dateCreated = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:created)');
        } else {
            $dateCreated = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:published)');
        }

        if ($dateCreated) {
            $date = new DateTime($dateCreated);
        }

        $this->data['datecreated'] = $date;

        return $this->data['datecreated'];
    }

    /**
     * Get the feed modification date
     *
     * @return DateTime|null
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->data)) {
            return $this->data['datemodified'];
        }

        $date = null;

        if ($this->getType() === Reader\Reader::TYPE_ATOM_03) {
            $dateModified = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:modified)');
        } else {
            $dateModified = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:updated)');
        }

        if ($dateModified) {
            $date = new DateTime($dateModified);
        }

        $this->data['datemodified'] = $date;

        return $this->data['datemodified'];
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

        if ($this->getType() === Reader\Reader::TYPE_ATOM_03) {
            $description = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:tagline)');
        } else {
            $description = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:subtitle)');
        }

        if (!$description) {
            $description = null;
        }

        $this->data['description'] = $description;

        return $this->data['description'];
    }

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator()
    {
        if (array_key_exists('generator', $this->data)) {
            return $this->data['generator'];
        }
        // TODO: Add uri support
        $generator = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:generator)');

        if (!$generator) {
            $generator = null;
        }

        $this->data['generator'] = $generator;

        return $this->data['generator'];
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

        $id = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:id)');

        if (!$id) {
            if ($this->getLink()) {
                $id = $this->getLink();
            } elseif ($this->getTitle()) {
                $id = $this->getTitle();
            } else {
                $id = null;
            }
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

        $language = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:lang)');

        if (!$language) {
            $language = $this->xpath->evaluate('string(//@xml:lang[1])');
        }

        if (!$language) {
            $language = null;
        }

        $this->data['language'] = $language;

        return $this->data['language'];
    }

    /**
     * Get the feed image
     *
     * @return array|null
     */
    public function getImage()
    {
        if (array_key_exists('image', $this->data)) {
            return $this->data['image'];
        }

        $imageUrl = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:logo)');

        if (!$imageUrl) {
            $image = null;
        } else {
            $image = array('uri' => $imageUrl);
        }

        $this->data['image'] = $image;

        return $this->data['image'];
    }

    /**
     * Get the base URI of the feed (if set).
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (array_key_exists('baseUrl', $this->data)) {
            return $this->data['baseUrl'];
        }

        $baseUrl = $this->xpath->evaluate('string(//@xml:base[1])');

        if (!$baseUrl) {
            $baseUrl = null;
        }
        $this->data['baseUrl'] = $baseUrl;

        return $this->data['baseUrl'];
    }

    /**
     * Get a link to the source website
     *
     * @return string|null
     */
    public function getLink()
    {
        if (array_key_exists('link', $this->data)) {
            return $this->data['link'];
        }

        $link = null;

        $list = $this->xpath->query(
            $this->getXpathPrefix() . '/atom:link[@rel="alternate"]/@href' . '|' .
            $this->getXpathPrefix() . '/atom:link[not(@rel)]/@href'
        );

        if ($list->length) {
            $link = $list->item(0)->nodeValue;
            $link = $this->absolutiseUri($link);
        }

        $this->data['link'] = $link;

        return $this->data['link'];
    }

    /**
     * Get a link to the feed's XML Url
     *
     * @return string|null
     */
    public function getFeedLink()
    {
        if (array_key_exists('feedlink', $this->data)) {
            return $this->data['feedlink'];
        }

        $link = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:link[@rel="self"]/@href)');

        $link = $this->absolutiseUri($link);

        $this->data['feedlink'] = $link;

        return $this->data['feedlink'];
    }

    /**
     * Get an array of any supported Pusubhubbub endpoints
     *
     * @return array|null
     */
    public function getHubs()
    {
        if (array_key_exists('hubs', $this->data)) {
            return $this->data['hubs'];
        }
        $hubs = array();

        $list = $this->xpath->query($this->getXpathPrefix()
            . '//atom:link[@rel="hub"]/@href');

        if ($list->length) {
            foreach ($list as $uri) {
                $hubs[] = $this->absolutiseUri($uri->nodeValue);
            }
        } else {
            $hubs = null;
        }

        $this->data['hubs'] = $hubs;

        return $this->data['hubs'];
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

        $title = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:title)');

        if (!$title) {
            $title = null;
        }

        $this->data['title'] = $title;

        return $this->data['title'];
    }

    /**
     * Get all categories
     *
     * @return Collection\Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->data)) {
            return $this->data['categories'];
        }

        if ($this->getType() == Reader\Reader::TYPE_ATOM_10) {
            $list = $this->xpath->query($this->getXpathPrefix() . '/atom:category');
        } else {
            /**
             * Since Atom 0.3 did not support categories, it would have used the
             * Dublin Core extension. However there is a small possibility Atom 0.3
             * may have been retrofittied to use Atom 1.0 instead.
             */
            $this->xpath->registerNamespace('atom10', Reader\Reader::NAMESPACE_ATOM_10);
            $list = $this->xpath->query($this->getXpathPrefix() . '/atom10:category');
        }

        if ($list->length) {
            $categoryCollection = new Collection\Category;
            foreach ($list as $category) {
                $categoryCollection[] = array(
                    'term' => $category->getAttribute('term'),
                    'scheme' => $category->getAttribute('scheme'),
                    'label' => $category->getAttribute('label')
                );
            }
        } else {
            return new Collection\Category;
        }

        $this->data['categories'] = $categoryCollection;

        return $this->data['categories'];
    }

    /**
     * Get an author entry in RSS format
     *
     * @param  DOMElement $element
     * @return string
     */
    protected function getAuthorFromElement(DOMElement $element)
    {
        $author = array();

        $emailNode = $element->getElementsByTagName('email');
        $nameNode  = $element->getElementsByTagName('name');
        $uriNode   = $element->getElementsByTagName('uri');

        if ($emailNode->length && strlen($emailNode->item(0)->nodeValue) > 0) {
            $author['email'] = $emailNode->item(0)->nodeValue;
        }

        if ($nameNode->length && strlen($nameNode->item(0)->nodeValue) > 0) {
            $author['name'] = $nameNode->item(0)->nodeValue;
        }

        if ($uriNode->length && strlen($uriNode->item(0)->nodeValue) > 0) {
            $author['uri'] = $uriNode->item(0)->nodeValue;
        }

        if (empty($author)) {
            return null;
        }
        return $author;
    }

    /**
     *  Attempt to absolutise the URI, i.e. if a relative URI apply the
     *  xml:base value as a prefix to turn into an absolute URI.
     */
    protected function absolutiseUri($link)
    {
        if (!Uri::factory($link)->isAbsolute()) {
            if ($this->getBaseUrl() !== null) {
                $link = $this->getBaseUrl() . $link;
                if (!Uri::factory($link)->isValid()) {
                    $link = null;
                }
            }
        }
        return $link;
    }

    /**
     * Register the default namespaces for the current feed format
     */
    protected function registerNamespaces()
    {
        if ($this->getType() == Reader\Reader::TYPE_ATOM_10
            || $this->getType() == Reader\Reader::TYPE_ATOM_03
        ) {
            return; // pre-registered at Feed level
        }
        $atomDetected = $this->getAtomType();
        switch ($atomDetected) {
            case Reader\Reader::TYPE_ATOM_03:
                $this->xpath->registerNamespace('atom', Reader\Reader::NAMESPACE_ATOM_03);
                break;
            default:
                $this->xpath->registerNamespace('atom', Reader\Reader::NAMESPACE_ATOM_10);
                break;
        }
    }

    /**
     * Detect the presence of any Atom namespaces in use
     */
    protected function getAtomType()
    {
        $dom = $this->getDomDocument();
        $prefixAtom03 = $dom->lookupPrefix(Reader\Reader::NAMESPACE_ATOM_03);
        $prefixAtom10 = $dom->lookupPrefix(Reader\Reader::NAMESPACE_ATOM_10);
        if ($dom->isDefaultNamespace(Reader\Reader::NAMESPACE_ATOM_10)
            || !empty($prefixAtom10)
        ) {
            return Reader\Reader::TYPE_ATOM_10;
        }
        if ($dom->isDefaultNamespace(Reader\Reader::NAMESPACE_ATOM_03)
            || !empty($prefixAtom03)
        ) {
            return Reader\Reader::TYPE_ATOM_03;
        }
    }
}
