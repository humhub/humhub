<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Entry;

use DateTime;
use DOMElement;
use DOMXPath;
use Zend\Feed\Reader;
use Zend\Feed\Reader\Exception;

class Rss extends AbstractEntry implements EntryInterface
{

    /**
     * XPath query for RDF
     *
     * @var string
     */
    protected $xpathQueryRdf = '';

    /**
     * XPath query for RSS
     *
     * @var string
     */
    protected $xpathQueryRss = '';

    /**
     * Constructor
     *
     * @param  DOMElement $entry
     * @param  string $entryKey
     * @param  string $type
     */
    public function __construct(DOMElement $entry, $entryKey, $type = null)
    {
        parent::__construct($entry, $entryKey, $type);
        $this->xpathQueryRss = '//item[' . ($this->entryKey+1) . ']';
        $this->xpathQueryRdf = '//rss:item[' . ($this->entryKey+1) . ']';

        $manager    = Reader\Reader::getExtensionManager();
        $extensions = array(
            'DublinCore\Entry',
            'Content\Entry',
            'Atom\Entry',
            'WellFormedWeb\Entry',
            'Slash\Entry',
            'Thread\Entry',
        );
        foreach ($extensions as $name) {
            $extension = $manager->get($name);
            $extension->setEntryElement($entry);
            $extension->setEntryKey($entryKey);
            $extension->setType($type);
            $this->extensions[$name] = $extension;
        }
    }

    /**
     * Get an author entry
     *
     * @param int $index
     * @return string
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
        $authorsDc = $this->getExtension('DublinCore')->getAuthors();
        if (!empty($authorsDc)) {
            foreach ($authorsDc as $author) {
                $authors[] = array(
                    'name' => $author['name']
                );
            }
        }

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
        && $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query($this->xpathQueryRss . '//author');
        } else {
            $list = $this->xpath->query($this->xpathQueryRdf . '//rss:author');
        }
        if ($list->length) {
            foreach ($list as $author) {
                $string = trim($author->nodeValue);
                $email = null;
                $name = null;
                $data = array();
                // Pretty rough parsing - but it's a catchall
                if (preg_match("/^.*@[^ ]*/", $string, $matches)) {
                    $data['email'] = trim($matches[0]);
                    if (preg_match("/\((.*)\)$/", $string, $matches)) {
                        $data['name'] = $matches[1];
                    }
                    $authors[] = $data;
                }
            }
        }

        if (count($authors) == 0) {
            $authors = $this->getExtension('Atom')->getAuthors();
        } else {
            $authors = new Reader\Collection\Author(
                Reader\Reader::arrayUnique($authors)
            );
        }

        if (count($authors) == 0) {
            $authors = null;
        }

        $this->data['authors'] = $authors;

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

        $content = $this->getExtension('Content')->getContent();

        if (!$content) {
            $content = $this->getDescription();
        }

        if (empty($content)) {
            $content = $this->getExtension('Atom')->getContent();
        }

        $this->data['content'] = $content;

        return $this->data['content'];
    }

    /**
     * Get the entry's date of creation
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->getDateModified();
    }

    /**
     * Get the entry's date of modification
     *
     * @throws Exception\RuntimeException
     * @return string
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->data)) {
            return $this->data['datemodified'];
        }

        $dateModified = null;
        $date = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
            && $this->getType() !== Reader\Reader::TYPE_RSS_090
        ) {
            $dateModified = $this->xpath->evaluate('string(' . $this->xpathQueryRss . '/pubDate)');
            if ($dateModified) {
                $dateModifiedParsed = strtotime($dateModified);
                if ($dateModifiedParsed) {
                    $date = new DateTime('@' . $dateModifiedParsed);
                } else {
                    $dateStandards = array(DateTime::RSS, DateTime::RFC822,
                                           DateTime::RFC2822, null);
                    foreach ($dateStandards as $standard) {
                        try {
                            $date = date_create_from_format($standard, $dateModified);
                            break;
                        } catch (\Exception $e) {
                            if ($standard == null) {
                                throw new Exception\RuntimeException(
                                    'Could not load date due to unrecognised'
                                    .' format (should follow RFC 822 or 2822):'
                                    . $e->getMessage(),
                                    0, $e
                                );
                            }
                        }
                    }
                }
            }
        }

        if (!$date) {
            $date = $this->getExtension('DublinCore')->getDate();
        }

        if (!$date) {
            $date = $this->getExtension('Atom')->getDateModified();
        }

        if (!$date) {
            $date = null;
        }

        $this->data['datemodified'] = $date;

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

        $description = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
            && $this->getType() !== Reader\Reader::TYPE_RSS_090
        ) {
            $description = $this->xpath->evaluate('string(' . $this->xpathQueryRss . '/description)');
        } else {
            $description = $this->xpath->evaluate('string(' . $this->xpathQueryRdf . '/rss:description)');
        }

        if (!$description) {
            $description = $this->getExtension('DublinCore')->getDescription();
        }

        if (empty($description)) {
            $description = $this->getExtension('Atom')->getDescription();
        }

        if (!$description) {
            $description = null;
        }

        $this->data['description'] = $description;

        return $this->data['description'];
    }

    /**
     * Get the entry enclosure
     * @return string
     */
    public function getEnclosure()
    {
        if (array_key_exists('enclosure', $this->data)) {
            return $this->data['enclosure'];
        }

        $enclosure = null;

        if ($this->getType() == Reader\Reader::TYPE_RSS_20) {
            $nodeList = $this->xpath->query($this->xpathQueryRss . '/enclosure');

            if ($nodeList->length > 0) {
                $enclosure = new \stdClass();
                $enclosure->url    = $nodeList->item(0)->getAttribute('url');
                $enclosure->length = $nodeList->item(0)->getAttribute('length');
                $enclosure->type   = $nodeList->item(0)->getAttribute('type');
            }
        }

        if (!$enclosure) {
            $enclosure = $this->getExtension('Atom')->getEnclosure();
        }

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

        $id = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
            && $this->getType() !== Reader\Reader::TYPE_RSS_090
        ) {
            $id = $this->xpath->evaluate('string(' . $this->xpathQueryRss . '/guid)');
        }

        if (!$id) {
            $id = $this->getExtension('DublinCore')->getId();
        }

        if (empty($id)) {
            $id = $this->getExtension('Atom')->getId();
        }

        if (!$id) {
            if ($this->getPermalink()) {
                $id = $this->getPermalink();
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

        $links = array();

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query($this->xpathQueryRss . '//link');
        } else {
            $list = $this->xpath->query($this->xpathQueryRdf . '//rss:link');
        }

        if (!$list->length) {
            $links = $this->getExtension('Atom')->getLinks();
        } else {
            foreach ($list as $link) {
                $links[] = $link->nodeValue;
            }
        }

        $this->data['links'] = $links;

        return $this->data['links'];
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

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10 &&
            $this->getType() !== Reader\Reader::TYPE_RSS_090) {
            $list = $this->xpath->query($this->xpathQueryRss . '//category');
        } else {
            $list = $this->xpath->query($this->xpathQueryRdf . '//rss:category');
        }

        if ($list->length) {
            $categoryCollection = new Reader\Collection\Category;
            foreach ($list as $category) {
                $categoryCollection[] = array(
                    'term' => $category->nodeValue,
                    'scheme' => $category->getAttribute('domain'),
                    'label' => $category->nodeValue,
                );
            }
        } else {
            $categoryCollection = $this->getExtension('DublinCore')->getCategories();
        }

        if (count($categoryCollection) == 0) {
            $categoryCollection = $this->getExtension('Atom')->getCategories();
        }

        $this->data['categories'] = $categoryCollection;

        return $this->data['categories'];
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

        $title = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
            && $this->getType() !== Reader\Reader::TYPE_RSS_090
        ) {
            $title = $this->xpath->evaluate('string(' . $this->xpathQueryRss . '/title)');
        } else {
            $title = $this->xpath->evaluate('string(' . $this->xpathQueryRdf . '/rss:title)');
        }

        if (!$title) {
            $title = $this->getExtension('DublinCore')->getTitle();
        }

        if (!$title) {
            $title = $this->getExtension('Atom')->getTitle();
        }

        if (!$title) {
            $title = null;
        }

        $this->data['title'] = $title;

        return $this->data['title'];
    }

    /**
     * Get the number of comments/replies for current entry
     *
     * @return string|null
     */
    public function getCommentCount()
    {
        if (array_key_exists('commentcount', $this->data)) {
            return $this->data['commentcount'];
        }

        $commentcount = $this->getExtension('Slash')->getCommentCount();

        if (!$commentcount) {
            $commentcount = $this->getExtension('Thread')->getCommentCount();
        }

        if (!$commentcount) {
            $commentcount = $this->getExtension('Atom')->getCommentCount();
        }

        if (!$commentcount) {
            $commentcount = null;
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

        $commentlink = null;

        if ($this->getType() !== Reader\Reader::TYPE_RSS_10
            && $this->getType() !== Reader\Reader::TYPE_RSS_090
        ) {
            $commentlink = $this->xpath->evaluate('string(' . $this->xpathQueryRss . '/comments)');
        }

        if (!$commentlink) {
            $commentlink = $this->getExtension('Atom')->getCommentLink();
        }

        if (!$commentlink) {
            $commentlink = null;
        }

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

        $commentfeedlink = $this->getExtension('WellFormedWeb')->getCommentFeedLink();

        if (!$commentfeedlink) {
            $commentfeedlink = $this->getExtension('Atom')->getCommentFeedLink('rss');
        }

        if (!$commentfeedlink) {
            $commentfeedlink = $this->getExtension('Atom')->getCommentFeedLink('rdf');
        }

        if (!$commentfeedlink) {
            $commentfeedlink = null;
        }

        $this->data['commentfeedlink'] = $commentfeedlink;

        return $this->data['commentfeedlink'];
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
