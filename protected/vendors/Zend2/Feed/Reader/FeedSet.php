<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader;

use ArrayObject;
use DOMNodeList;
use Zend\Feed\Uri;

/**
*/
class FeedSet extends ArrayObject
{

    public $rss = null;

    public $rdf = null;

    public $atom = null;

    /**
     * Import a DOMNodeList from any document containing a set of links
     * for alternate versions of a document, which will normally refer to
     * RSS/RDF/Atom feeds for the current document.
     *
     * All such links are stored internally, however the first instance of
     * each RSS, RDF or Atom type has its URI stored as a public property
     * as a shortcut where the use case is simply to get a quick feed ref.
     *
     * Note that feeds are not loaded at this point, but will be lazy
     * loaded automatically when each links 'feed' array key is accessed.
     *
     * @param DOMNodeList $links
     * @param string $uri
     * @return void
     */
    public function addLinks(DOMNodeList $links, $uri)
    {
        foreach ($links as $link) {
            if (strtolower($link->getAttribute('rel')) !== 'alternate'
                || !$link->getAttribute('type') || !$link->getAttribute('href')) {
                continue;
            }
            if (!isset($this->rss) && $link->getAttribute('type') == 'application/rss+xml') {
                $this->rss = $this->absolutiseUri(trim($link->getAttribute('href')), $uri);
            } elseif (!isset($this->atom) && $link->getAttribute('type') == 'application/atom+xml') {
                $this->atom = $this->absolutiseUri(trim($link->getAttribute('href')), $uri);
            } elseif (!isset($this->rdf) && $link->getAttribute('type') == 'application/rdf+xml') {
                $this->rdf = $this->absolutiseUri(trim($link->getAttribute('href')), $uri);
            }
            $this[] = new static(array(
                'rel' => 'alternate',
                'type' => $link->getAttribute('type'),
                'href' => $this->absolutiseUri(trim($link->getAttribute('href')), $uri),
            ));
        }
    }

    /**
     *  Attempt to turn a relative URI into an absolute URI
     */
    protected function absolutiseUri($link, $uri = null)
    {
        $linkUri = Uri::factory($link);
        if (!$linkUri->isAbsolute() or !$linkUri->isValid()) {
            if ($uri !== null) {
                $uri = Uri::factory($uri);

                if ($link[0] !== '/') {
                    $link = $uri->getPath() . '/' . $link;
                }

                $link = $uri->getScheme() . '://' . $uri->getHost() . '/' . $this->canonicalizePath($link);
                if (!Uri::factory($link)->isValid()) {
                    $link = null;
                }
            }
        }
        return $link;
    }

    /**
     *  Canonicalize relative path
     */
    protected function canonicalizePath($path)
    {
        $parts = array_filter(explode('/', $path));
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode('/', $absolutes);
    }

    /**
     * Supports lazy loading of feeds using Reader::import() but
     * delegates any other operations to the parent class.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($offset == 'feed' && !$this->offsetExists('feed')) {
            if (!$this->offsetExists('href')) {
                return null;
            }
            $feed = Reader::import($this->offsetGet('href'));
            $this->offsetSet('feed', $feed);
            return $feed;
        }
        return parent::offsetGet($offset);
    }
}
