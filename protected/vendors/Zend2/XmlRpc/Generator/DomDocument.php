<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Generator;

/**
 * DOMDocument based implementation of a XML/RPC generator
 */
class DomDocument extends AbstractGenerator
{
    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @var \DOMNode
     */
    protected $currentElement;

    /**
     * Start XML element
     *
     * @param string $name
     * @return void
     */
    protected function _openElement($name)
    {
        $newElement = $this->dom->createElement($name);

        $this->currentElement = $this->currentElement->appendChild($newElement);
    }

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text
     */
    protected function _writeTextData($text)
    {
        $this->currentElement->appendChild($this->dom->createTextNode($text));
    }

    /**
     * Close an previously opened XML element
     *
     * Resets $currentElement to the next parent node in the hierarchy
     *
     * @param string $name
     * @return void
     */
    protected function _closeElement($name)
    {
        if (isset($this->currentElement->parentNode)) {
            $this->currentElement = $this->currentElement->parentNode;
        }
    }

    /**
     * Save XML as a string
     *
     * @return string
     */
    public function saveXml()
    {
        return $this->dom->saveXml();
    }

    /**
     * Initializes internal objects
     *
     * @return void
     */
    protected function _init()
    {
        $this->dom = new \DOMDocument('1.0', $this->encoding);
        $this->currentElement = $this->dom;
    }
}
