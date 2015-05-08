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
use DOMDocument;
use DOMElement;
use stdClass;
use Zend\Feed\Reader;
use Zend\Feed\Reader\Collection;
use Zend\Feed\Reader\Extension;
use Zend\Feed\Uri;

class Entry extends Extension\AbstractEntry
{
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
     * @return Collection\Author
     */
    public function getAuthors()
    {
        if (array_key_exists('authors', $this->data)) {
            return $this->data['authors'];
        }

        $authors = array();
        $list = $this->getXpath()->query($this->getXpathPrefix() . '//atom:author');

        if (!$list->length) {
            /**
             * TODO: Limit query to feed level els only!
             */
            $list = $this->getXpath()->query('//atom:author');
        }

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
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        if (array_key_exists('content', $this->data)) {
            return $this->data['content'];
        }

        $content = null;

        $el = $this->getXpath()->query($this->getXpathPrefix() . '/atom:content');
        if ($el->length > 0) {
            $el = $el->item(0);
            $type = $el->getAttribute('type');
            switch ($type) {
                case '':
                case 'text':
                case 'text/plain':
                case 'html':
                case 'text/html':
                    $content = $el->nodeValue;
                break;
                case 'xhtml':
                    $this->getXpath()->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
                    $xhtml = $this->getXpath()->query(
                        $this->getXpathPrefix() . '/atom:content/xhtml:div'
                    )->item(0);
                    $d = new DOMDocument('1.0', $this->getEncoding());
                    $xhtmls = $d->importNode($xhtml, true);
                    $d->appendChild($xhtmls);
                    $content = $this->collectXhtml(
                        $d->saveXML(),
                        $d->lookupPrefix('http://www.w3.org/1999/xhtml')
                    );
                break;
            }
        }

        if (!$content) {
            $content = $this->getDescription();
        }

        $this->data['content'] = trim($content);

        return $this->data['content'];
    }

    /**
     * Parse out XHTML to remove the namespacing
     *
     * @param $xhtml
     * @param $prefix
     * @return mixed
     */
    protected function collectXhtml($xhtml, $prefix)
    {
        if (!empty($prefix)) $prefix = $prefix . ':';
        $matches = array(
            "/<\?xml[^<]*>[^<]*<" . $prefix . "div[^<]*/",
            "/<\/" . $prefix . "div>\s*$/"
        );
        $xhtml = preg_replace($matches, '', $xhtml);
        if (!empty($prefix)) {
            $xhtml = preg_replace("/(<[\/]?)" . $prefix . "([a-zA-Z]+)/", '$1$2', $xhtml);
        }
        return $xhtml;
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

        $date = null;

        if ($this->getAtomType() === Reader\Reader::TYPE_ATOM_03) {
            $dateCreated = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:created)');
        } else {
            $dateCreated = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:published)');
        }

        if ($dateCreated) {
            $date = new DateTime($dateCreated);
        }

        $this->data['datecreated'] = $date;

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

        $date = null;

        if ($this->getAtomType() === Reader\Reader::TYPE_ATOM_03) {
            $dateModified = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:modified)');
        } else {
            $dateModified = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:updated)');
        }

        if ($dateModified) {
            $date = new DateTime($dateModified);
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

        $description = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:summary)');

        if (!$description) {
            $description = null;
        }

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

        $enclosure = null;

        $nodeList = $this->getXpath()->query($this->getXpathPrefix() . '/atom:link[@rel="enclosure"]');

        if ($nodeList->length > 0) {
            $enclosure         = new stdClass();
            $enclosure->url    = $nodeList->item(0)->getAttribute('href');
            $enclosure->length = $nodeList->item(0)->getAttribute('length');
            $enclosure->type   = $nodeList->item(0)->getAttribute('type');
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

        $id = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:id)');

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
     * Get the base URI of the feed (if set).
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (array_key_exists('baseUrl', $this->data)) {
            return $this->data['baseUrl'];
        }

        $baseUrl = $this->getXpath()->evaluate('string('
            . $this->getXpathPrefix() . '/@xml:base[1]'
        . ')');

        if (!$baseUrl) {
            $baseUrl = $this->getXpath()->evaluate('string(//@xml:base[1])');
        }

        if (!$baseUrl) {
            $baseUrl = null;
        }

        $this->data['baseUrl'] = $baseUrl;

        return $this->data['baseUrl'];
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

        $list = $this->getXpath()->query(
            $this->getXpathPrefix() . '//atom:link[@rel="alternate"]/@href' . '|' .
            $this->getXpathPrefix() . '//atom:link[not(@rel)]/@href'
        );

        if ($list->length) {
            foreach ($list as $link) {
                $links[] = $this->absolutiseUri($link->value);
            }
        }

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

        $title = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:title)');

        if (!$title) {
            $title = null;
        }

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

        $count = null;

        $this->getXpath()->registerNamespace('thread10', 'http://purl.org/syndication/thread/1.0');
        $list = $this->getXpath()->query(
            $this->getXpathPrefix() . '//atom:link[@rel="replies"]/@thread10:count'
        );

        if ($list->length) {
            $count = $list->item(0)->value;
        }

        $this->data['commentcount'] = $count;

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

        $link = null;

        $list = $this->getXpath()->query(
            $this->getXpathPrefix() . '//atom:link[@rel="replies" and @type="text/html"]/@href'
        );

        if ($list->length) {
            $link = $list->item(0)->value;
            $link = $this->absolutiseUri($link);
        }

        $this->data['commentlink'] = $link;

        return $this->data['commentlink'];
    }

    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @param string $type
     * @return string
     */
    public function getCommentFeedLink($type = 'atom')
    {
        if (array_key_exists('commentfeedlink', $this->data)) {
            return $this->data['commentfeedlink'];
        }

        $link = null;

        $list = $this->getXpath()->query(
            $this->getXpathPrefix() . '//atom:link[@rel="replies" and @type="application/' . $type.'+xml"]/@href'
        );

        if ($list->length) {
            $link = $list->item(0)->value;
            $link = $this->absolutiseUri($link);
        }

        $this->data['commentfeedlink'] = $link;

        return $this->data['commentfeedlink'];
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

        if ($this->getAtomType() == Reader\Reader::TYPE_ATOM_10) {
            $list = $this->getXpath()->query($this->getXpathPrefix() . '//atom:category');
        } else {
            /**
             * Since Atom 0.3 did not support categories, it would have used the
             * Dublin Core extension. However there is a small possibility Atom 0.3
             * may have been retrofitted to use Atom 1.0 instead.
             */
            $this->getXpath()->registerNamespace('atom10', Reader\Reader::NAMESPACE_ATOM_10);
            $list = $this->getXpath()->query($this->getXpathPrefix() . '//atom10:category');
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
     * Get source feed metadata from the entry
     *
     * @return Reader\Feed\Atom\Source|null
     */
    public function getSource()
    {
        if (array_key_exists('source', $this->data)) {
            return $this->data['source'];
        }

        $source = null;
        // TODO: Investigate why _getAtomType() fails here. Is it even needed?
        if ($this->getType() == Reader\Reader::TYPE_ATOM_10) {
            $list = $this->getXpath()->query($this->getXpathPrefix() . '/atom:source[1]');
            if ($list->length) {
                $element = $list->item(0);
                $source = new Reader\Feed\Atom\Source($element, $this->getXpathPrefix());
            }
        }

        $this->data['source'] = $source;
        return $this->data['source'];
    }

    /**
     *  Attempt to absolutise the URI, i.e. if a relative URI apply the
     *  xml:base value as a prefix to turn into an absolute URI.
     *
     * @param $link
     * @return string
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
     * Get an author entry
     *
     * @param DOMElement $element
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
     * Register the default namespaces for the current feed format
     */
    protected function registerNamespaces()
    {
        switch ($this->getAtomType()) {
            case Reader\Reader::TYPE_ATOM_03:
                $this->getXpath()->registerNamespace('atom', Reader\Reader::NAMESPACE_ATOM_03);
                break;
            default:
                $this->getXpath()->registerNamespace('atom', Reader\Reader::NAMESPACE_ATOM_10);
                break;
        }
    }

    /**
     * Detect the presence of any Atom namespaces in use
     *
     * @return string
     */
    protected function getAtomType()
    {
        $dom = $this->getDomDocument();
        $prefixAtom03 = $dom->lookupPrefix(Reader\Reader::NAMESPACE_ATOM_03);
        $prefixAtom10 = $dom->lookupPrefix(Reader\Reader::NAMESPACE_ATOM_10);
        if ($dom->isDefaultNamespace(Reader\Reader::NAMESPACE_ATOM_03)
        || !empty($prefixAtom03)) {
            return Reader\Reader::TYPE_ATOM_03;
        }
        if ($dom->isDefaultNamespace(Reader\Reader::NAMESPACE_ATOM_10)
        || !empty($prefixAtom10)) {
            return Reader\Reader::TYPE_ATOM_10;
        }
    }
}
