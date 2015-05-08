<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Entry;

use DOMElement;
use DOMXPath;
use Zend\Feed\Reader;

class Atom extends AbstractEntry implements EntryInterface
{
    /**
     * XPath query
     *
     * @var string
     */
    protected $xpathQuery = '';

    /**
     * Constructor
     *
     * @param  DOMElement $entry
     * @param  int $entryKey
     * @param  string $type
     */
    public function __construct(DOMElement $entry, $entryKey, $type = null)
    {
        parent::__construct($entry, $entryKey, $type);

        // Everyone by now should know XPath indices start from 1 not 0
        $this->xpathQuery = '//atom:entry[' . ($this->entryKey + 1) . ']';

        $manager    = Reader\Reader::getExtensionManager();
        $extensions = array('Atom\Entry', 'Thread\Entry', 'DublinCore\Entry');

        foreach ($extensions as $name) {
            $extension = $manager->get($name);
            $extension->setEntryElement($entry);
            $extension->setEntryKey($entryKey);
            $extension->setType($type);
            $this->extensions[$name] = $extension;
        }
    }

    /**
     * Get the specified author
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

        $people = $this->getExtension('Atom')->getAuthors();

        $this->data['authors'] = $people;

        return $this->data['authors'];
    }

    /**
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        if (array_key_exists('content', $this->data)) {
            return $this->data['content'];
        }

        $content = $this->getExtension('Atom')->getContent();

        $this->data['content'] = $content;

        return $this->data['content'];
    }

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated()
    {
        if (array_key_exists('datecreated', $this->data)) {
            return $this->data['datecreated'];
        }

        $dateCreated = $this->getExtension('Atom')->getDateCreated();

        $this->data['datecreated'] = $dateCreated;

        return $this->data['datecreated'];
    }

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->data)) {
            return $this->data['datemodified'];
        }

        $dateModified = $this->getExtension('Atom')->getDateModified();

        $this->data['datemodified'] = $dateModified;

        return $this->data['datemodified'];
    }

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription()
    {
        if (array_key_exists('description', $this->data)) {
            return $this->data['description'];
        }

        $description = $this->getExtension('Atom')->getDescription();

        $this->data['description'] = $description;

        return $this->data['description'];
    }

    /**
     * Get the entry enclosure
     *
     * @return string
     */
    public function getEnclosure()
    {
        if (array_key_exists('enclosure', $this->data)) {
            return $this->data['enclosure'];
        }

        $enclosure = $this->getExtension('Atom')->getEnclosure();

        $this->data['enclosure'] = $enclosure;

        return $this->data['enclosure'];
    }

    /**
     * Get the entry ID
     *
     * @return string
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
     * Get a specific link
     *
     * @param  int $index
     * @return string
     */
    public function getLink($index = 0)
    {
        if (!array_key_exists('links', $this->data)) {
            $this->getLinks();
        }

        if (isset($this->data['links'][$index])) {
            return $this->data['links'][$index];
        }

        return null;
    }

    /**
     * Get all links
     *
     * @return array
     */
    public function getLinks()
    {
        if (array_key_exists('links', $this->data)) {
            return $this->data['links'];
        }

        $links = $this->getExtension('Atom')->getLinks();

        $this->data['links'] = $links;

        return $this->data['links'];
    }

    /**
     * Get a permalink to the entry
     *
     * @return string
     */
    public function getPermalink()
    {
        return $this->getLink(0);
    }

    /**
     * Get the entry title
     *
     * @return string
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
     * Get the number of comments/replies for current entry
     *
     * @return int
     */
    public function getCommentCount()
    {
        if (array_key_exists('commentcount', $this->data)) {
            return $this->data['commentcount'];
        }

        $commentcount = $this->getExtension('Thread')->getCommentCount();

        if (!$commentcount) {
            $commentcount = $this->getExtension('Atom')->getCommentCount();
        }

        $this->data['commentcount'] = $commentcount;

        return $this->data['commentcount'];
    }

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink()
    {
        if (array_key_exists('commentlink', $this->data)) {
            return $this->data['commentlink'];
        }

        $commentlink = $this->getExtension('Atom')->getCommentLink();

        $this->data['commentlink'] = $commentlink;

        return $this->data['commentlink'];
    }

    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function getCommentFeedLink()
    {
        if (array_key_exists('commentfeedlink', $this->data)) {
            return $this->data['commentfeedlink'];
        }

        $commentfeedlink = $this->getExtension('Atom')->getCommentFeedLink();

        $this->data['commentfeedlink'] = $commentfeedlink;

        return $this->data['commentfeedlink'];
    }

    /**
     * Get category data as a Reader\Reader_Collection_Category object
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
     * Get source feed metadata from the entry
     *
     * @return Reader\Feed\Atom\Source|null
     */
    public function getSource()
    {
        if (array_key_exists('source', $this->data)) {
            return $this->data['source'];
        }

        $source = $this->getExtension('Atom')->getSource();

        $this->data['source'] = $source;

        return $this->data['source'];
    }

    /**
     * Set the XPath query (incl. on all Extensions)
     *
     * @param DOMXPath $xpath
     * @return void
     */
    public function setXpath(DOMXPath $xpath)
    {
        parent::setXpath($xpath);
        foreach ($this->extensions as $extension) {
            $extension->setXpath($this->xpath);
        }
    }
}
