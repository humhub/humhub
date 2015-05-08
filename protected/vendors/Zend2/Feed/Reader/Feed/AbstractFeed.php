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
use DOMElement;
use DOMXPath;
use Zend\Feed\Reader;
use Zend\Feed\Reader\Exception;

/**
*/
abstract class AbstractFeed implements FeedInterface
{
    /**
     * Parsed feed data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Parsed feed data in the shape of a DOMDocument
     *
     * @var DOMDocument
     */
    protected $domDocument = null;

    /**
     * An array of parsed feed entries
     *
     * @var array
     */
    protected $entries = array();

    /**
     * A pointer for the iterator to keep track of the entries array
     *
     * @var int
     */
    protected $entriesKey = 0;

    /**
     * The base XPath query used to retrieve feed data
     *
     * @var DOMXPath
     */
    protected $xpath = null;

    /**
     * Array of loaded extensions
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * Original Source URI (set if imported from a URI)
     *
     * @var string
     */
    protected $originalSourceUri = null;

    /**
     * Constructor
     *
     * @param DOMDocument $domDocument The DOM object for the feed's XML
     * @param string $type Feed type
     */
    public function __construct(DOMDocument $domDocument, $type = null)
    {
        $this->domDocument = $domDocument;
        $this->xpath = new DOMXPath($this->domDocument);

        if ($type !== null) {
            $this->data['type'] = $type;
        } else {
            $this->data['type'] = Reader\Reader::detectType($this->domDocument);
        }
        $this->registerNamespaces();
        $this->indexEntries();
        $this->loadExtensions();
    }

    /**
     * Set an original source URI for the feed being parsed. This value
     * is returned from getFeedLink() method if the feed does not carry
     * a self-referencing URI.
     *
     * @param string $uri
     */
    public function setOriginalSourceUri($uri)
    {
        $this->originalSourceUri = $uri;
    }

    /**
     * Get an original source URI for the feed being parsed. Returns null if
     * unset or the feed was not imported from a URI.
     *
     * @return string|null
     */
    public function getOriginalSourceUri()
    {
        return $this->originalSourceUri;
    }

    /**
     * Get the number of feed entries.
     * Required by the Iterator interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->entries);
    }

    /**
     * Return the current entry
     *
     * @return \Zend\Feed\Reader\Entry\EntryInterface
     */
    public function current()
    {
        if (substr($this->getType(), 0, 3) == 'rss') {
            $reader = new Reader\Entry\Rss($this->entries[$this->key()], $this->key(), $this->getType());
        } else {
            $reader = new Reader\Entry\Atom($this->entries[$this->key()], $this->key(), $this->getType());
        }

        $reader->setXpath($this->xpath);

        return $reader;
    }

    /**
     * Get the DOM
     *
     * @return DOMDocument
     */
    public function getDomDocument()
    {
        return $this->domDocument;
    }

    /**
     * Get the Feed's encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        $assumed = $this->getDomDocument()->encoding;
        if (empty($assumed)) {
            $assumed = 'UTF-8';
        }
        return $assumed;
    }

    /**
     * Get feed as xml
     *
     * @return string
     */
    public function saveXml()
    {
          return $this->getDomDocument()->saveXml();
    }

    /**
     * Get the DOMElement representing the items/feed element
     *
     * @return DOMElement
     */
    public function getElement()
    {
          return $this->getDomDocument()->documentElement;
    }

    /**
     * Get the DOMXPath object for this feed
     *
     * @return DOMXPath
     */
    public function getXpath()
    {
          return $this->xpath;
    }

    /**
     * Get the feed type
     *
     * @return string
     */
    public function getType()
    {
        return $this->data['type'];
    }

    /**
     * Return the current feed key
     *
     * @return int
     */
    public function key()
    {
        return $this->entriesKey;
    }

    /**
     * Move the feed pointer forward
     *
     */
    public function next()
    {
        ++$this->entriesKey;
    }

    /**
     * Reset the pointer in the feed object
     *
     */
    public function rewind()
    {
        $this->entriesKey = 0;
    }

    /**
     * Check to see if the iterator is still valid
     *
     * @return bool
     */
    public function valid()
    {
        return 0 <= $this->entriesKey && $this->entriesKey < $this->count();
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function __call($method, $args)
    {
        foreach ($this->extensions as $extension) {
            if (method_exists($extension, $method)) {
                return call_user_func_array(array($extension, $method), $args);
            }
        }
        throw new Exception\BadMethodCallException('Method: ' . $method
        . 'does not exist and could not be located on a registered Extension');
    }

    /**
     * Return an Extension object with the matching name (postfixed with _Feed)
     *
     * @param string $name
     * @return \Zend\Feed\Reader\Extension\AbstractFeed
     */
    public function getExtension($name)
    {
        if (array_key_exists($name . '\\Feed', $this->extensions)) {
            return $this->extensions[$name . '\\Feed'];
        }
        return null;
    }

    protected function loadExtensions()
    {
        $all     = Reader\Reader::getExtensions();
        $manager = Reader\Reader::getExtensionManager();
        $feed    = $all['feed'];
        foreach ($feed as $extension) {
            if (in_array($extension, $all['core'])) {
                continue;
            }
            if (!$manager->has($extension)) {
                throw new Exception\RuntimeException(sprintf('Unable to load extension "%s"; cannot find class', $extension));
            }
            $plugin = $manager->get($extension);
            $plugin->setDomDocument($this->getDomDocument());
            $plugin->setType($this->data['type']);
            $plugin->setXpath($this->xpath);
            $this->extensions[$extension] = $plugin;
        }
    }

    /**
     * Read all entries to the internal entries array
     *
     */
    abstract protected function indexEntries();

    /**
     * Register the default namespaces for the current feed format
     *
     */
    abstract protected function registerNamespaces();
}
