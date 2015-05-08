<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Entry;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Zend\Feed\Reader;
use Zend\Feed\Reader\Exception;

abstract class AbstractEntry
{
    /**
     * Feed entry data
     *
     * @var array
     */
    protected $data = array();

    /**
     * DOM document object
     *
     * @var DOMDocument
     */
    protected $domDocument = null;

    /**
     * Entry instance
     *
     * @var DOMElement
     */
    protected $entry = null;

    /**
     * Pointer to the current entry
     *
     * @var int
     */
    protected $entryKey = 0;

    /**
     * XPath object
     *
     * @var DOMXPath
     */
    protected $xpath = null;

    /**
     * Registered extensions
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * Constructor
     *
     * @param  DOMElement $entry
     * @param  int $entryKey
     * @param  string $type
     */
    public function __construct(DOMElement $entry, $entryKey, $type = null)
    {
        $this->entry       = $entry;
        $this->entryKey    = $entryKey;
        $this->domDocument = $entry->ownerDocument;
        if ($type !== null) {
            $this->data['type'] = $type;
        } elseif ($this->domDocument !== null) {
            $this->data['type'] = Reader\Reader::detectType($this->domDocument);
        } else {
            $this->data['type'] = Reader\Reader::TYPE_ANY;
        }
        $this->loadExtensions();
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
     * Get the entry element
     *
     * @return DOMElement
     */
    public function getElement()
    {
        return $this->entry;
    }

    /**
     * Get the Entry's encoding
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
     * Get entry as xml
     *
     * @return string
     */
    public function saveXml()
    {
        $dom = new DOMDocument('1.0', $this->getEncoding());
        $entry = $dom->importNode($this->getElement(), true);
        $dom->appendChild($entry);
        return $dom->saveXml();
    }

    /**
     * Get the entry type
     *
     * @return string
     */
    public function getType()
    {
        return $this->data['type'];
    }

    /**
     * Get the XPath query object
     *
     * @return DOMXPath
     */
    public function getXpath()
    {
        if (!$this->xpath) {
            $this->setXpath(new DOMXPath($this->getDomDocument()));
        }
        return $this->xpath;
    }

    /**
     * Set the XPath query
     *
     * @param  DOMXPath $xpath
     * @return AbstractEntry
     */
    public function setXpath(DOMXPath $xpath)
    {
        $this->xpath = $xpath;
        return $this;
    }

    /**
     * Get registered extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Return an Extension object with the matching name (postfixed with _Entry)
     *
     * @param string $name
     * @return Reader\Extension\AbstractEntry
     */
    public function getExtension($name)
    {
        if (array_key_exists($name . '\\Entry', $this->extensions)) {
            return $this->extensions[$name . '\\Entry'];
        }
        return null;
    }

    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Exception\RuntimeException if no extensions implements the method
     */
    public function __call($method, $args)
    {
        foreach ($this->extensions as $extension) {
            if (method_exists($extension, $method)) {
                return call_user_func_array(array($extension, $method), $args);
            }
        }
        throw new Exception\RuntimeException('Method: ' . $method
            . ' does not exist and could not be located on a registered Extension');
    }

    /**
     * Load extensions from Zend\Feed\Reader\Reader
     *
     * @return void
     */
    protected function loadExtensions()
    {
        $all     = Reader\Reader::getExtensions();
        $manager = Reader\Reader::getExtensionManager();
        $feed    = $all['entry'];
        foreach ($feed as $extension) {
            if (in_array($extension, $all['core'])) {
                continue;
            }
            $plugin = $manager->get($extension);
            $plugin->setEntryElement($this->getElement());
            $plugin->setEntryKey($this->entryKey);
            $plugin->setType($this->data['type']);
            $this->extensions[$extension] = $plugin;
        }
    }
}
