<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Extension;

use DOMDocument;
use DOMXPath;
use Zend\Feed\Reader;

abstract class AbstractFeed
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
     * The base XPath query used to retrieve feed data
     *
     * @var DOMXPath
     */
    protected $xpath = null;

    /**
     * The XPath prefix
     *
     * @var string
     */
    protected $xpathPrefix = '';

    /**
     * Set the DOM document
     *
     * @param  DOMDocument $dom
     * @return AbstractFeed
     */
    public function setDomDocument(DOMDocument $dom)
    {
        $this->domDocument = $dom;
        return $this;
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
        return $assumed;
    }

    /**
     * Set the feed type
     *
     * @param  string $type
     * @return AbstractFeed
     */
    public function setType($type)
    {
        $this->data['type'] = $type;
        return $this;
    }

    /**
     * Get the feed type
     *
     * If null, it will attempt to autodetect the type.
     *
     * @return string
     */
    public function getType()
    {
        $type = $this->data['type'];
        if (null === $type) {
            $type = Reader\Reader::detectType($this->getDomDocument());
            $this->setType($type);
        }
        return $type;
    }


    /**
     * Return the feed as an array
     *
     * @return array
     */
    public function toArray() // untested
    {
        return $this->data;
    }

    /**
     * Set the XPath query
     *
     * @param  DOMXPath $xpath
     * @return AbstractEntry
     */
    public function setXpath(DOMXPath $xpath = null)
    {
        if (null === $xpath) {
            $this->xpath = null;
            return $this;
        }

        $this->xpath = $xpath;
        $this->registerNamespaces();
        return $this;
    }

    /**
     * Get the DOMXPath object
     *
     * @return string
     */
    public function getXpath()
    {
        if (null === $this->xpath) {
            $this->setXpath(new DOMXPath($this->getDomDocument()));
        }

        return $this->xpath;
    }

    /**
     * Get the XPath prefix
     *
     * @return string
     */
    public function getXpathPrefix()
    {
        return $this->xpathPrefix;
    }

    /**
     * Set the XPath prefix
     *
     * @param string $prefix
     * @return void
     */
    public function setXpathPrefix($prefix)
    {
        $this->xpathPrefix = $prefix;
    }

    /**
     * Register the default namespaces for the current feed format
     */
    abstract protected function registerNamespaces();
}
