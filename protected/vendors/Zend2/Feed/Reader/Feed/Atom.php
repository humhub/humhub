<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Feed;

use DOMDocument;
use Zend\Feed\Reader;

/**
*/
class Atom extends AbstractFeed
{

    /**
     * Constructor
     *
     * @param  DOMDocument $dom
     * @param  string $type
     */
    public function __construct(DOMDocument $dom, $type = null)
    {
        parent::__construct($dom, $type);
        $manager = Reader\Reader::getExtensionManager();

        $atomFeed = $manager->get('Atom\Feed');
        $atomFeed->setDomDocument($dom);
        $atomFeed->setType($this->data['type']);
        $atomFeed->setXpath($this->xpath);
        $this->extensions['Atom\\Feed'] = $atomFeed;

        $atomFeed = $manager->get('DublinCore\Feed');
        $atomFeed->setDomDocument($dom);
        $atomFeed->setType($this->data['type']);
        $atomFeed->setXpath($this->xpath);
        $this->extensions['DublinCore\\Feed'] = $atomFeed;

        foreach ($this->extensions as $extension) {
            $extension->setXpathPrefix('/atom:feed');
        }
    }

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

        $authors = $this->getExtension('Atom')->getAuthors();

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

        $copyright = $this->getExtension('Atom')->getCopyright();

        if (!$copyright) {
            $copyright = null;
        }

        $this->data['copyright'] = $copyright;

        return $this->data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated()
    {
        if (array_key_exists('datecreated', $this->data)) {
            return $this->data['datecreated'];
        }

        $dateCreated = $this->getExtension('Atom')->getDateCreated();

        if (!$dateCreated) {
            $dateCreated = null;
        }

        $this->data['datecreated'] = $dateCreated;

        return $this->data['datecreated'];
    }

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->data)) {
            return $this->data['datemodified'];
        }

        $dateModified = $this->getExtension('Atom')->getDateModified();

        if (!$dateModified) {
            $dateModified = null;
        }

        $this->data['datemodified'] = $dateModified;

        return $this->data['datemodified'];
    }

    /**
     * Get the feed lastBuild date. This is not implemented in Atom.
     *
     * @return string|null
     */
    public function getLastBuildDate()
    {
        return null;
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

        $description = $this->getExtension('Atom')->getDescription();

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

        $generator = $this->getExtension('Atom')->getGenerator();

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

        $id = $this->getExtension('Atom')->getId();

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

        $language = $this->getExtension('Atom')->getLanguage();

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
     * Get a link to the source website
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (array_key_exists('baseUrl', $this->data)) {
            return $this->data['baseUrl'];
        }

        $baseUrl = $this->getExtension('Atom')->getBaseUrl();

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

        $link = $this->getExtension('Atom')->getLink();

        $this->data['link'] = $link;

        return $this->data['link'];
    }

    /**
     * Get feed image data
     *
     * @return array|null
     */
    public function getImage()
    {
        if (array_key_exists('image', $this->data)) {
            return $this->data['image'];
        }

        $link = $this->getExtension('Atom')->getImage();

        $this->data['image'] = $link;

        return $this->data['image'];
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

        $link = $this->getExtension('Atom')->getFeedLink();

        if ($link === null || empty($link)) {
            $link = $this->getOriginalSourceUri();
        }

        $this->data['feedlink'] = $link;

        return $this->data['feedlink'];
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

        $title = $this->getExtension('Atom')->getTitle();

        $this->data['title'] = $title;

        return $this->data['title'];
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

        $hubs = $this->getExtension('Atom')->getHubs();

        $this->data['hubs'] = $hubs;

        return $this->data['hubs'];
    }

    /**
     * Get all categories
     *
     * @return Reader\Collection\Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->data)) {
            return $this->data['categories'];
        }

        $categoryCollection = $this->getExtension('Atom')->getCategories();

        if (count($categoryCollection) == 0) {
            $categoryCollection = $this->getExtension('DublinCore')->getCategories();
        }

        $this->data['categories'] = $categoryCollection;

        return $this->data['categories'];
    }

    /**
     * Read all entries to the internal entries array
     *
     * @return void
     */
    protected function indexEntries()
    {
        if ($this->getType() == Reader\Reader::TYPE_ATOM_10 ||
            $this->getType() == Reader\Reader::TYPE_ATOM_03) {
            $entries = array();
            $entries = $this->xpath->evaluate('//atom:entry');

            foreach ($entries as $index => $entry) {
                $this->entries[$index] = $entry;
            }
        }
    }

    /**
     * Register the default namespaces for the current feed format
     *
     */
    protected function registerNamespaces()
    {
        switch ($this->data['type']) {
            case Reader\Reader::TYPE_ATOM_03:
                $this->xpath->registerNamespace('atom', Reader\Reader::NAMESPACE_ATOM_03);
                break;
            case Reader\Reader::TYPE_ATOM_10:
            default:
                $this->xpath->registerNamespace('atom', Reader\Reader::NAMESPACE_ATOM_10);
        }
    }
}
