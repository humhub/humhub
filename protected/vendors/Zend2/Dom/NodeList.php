<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Dom;

use ArrayAccess;
use Countable;
use DOMDocument;
use DOMNodeList;
use DOMNode;
use Iterator;

/**
 * Nodelist for DOM XPath query
 */
class NodeList implements Iterator, Countable, ArrayAccess
{
    /**
     * CSS Selector query
     * @var string
     */
    protected $cssQuery;

    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var DOMNodeList
     */
    protected $nodeList;

    /**
     * Current iterator position
     * @var int
     */
    protected $position = 0;

    /**
     * XPath query
     * @var string
     */
    protected $xpathQuery;

    /**
     * Constructor
     *
     * @param string       $cssQuery
     * @param string|array $xpathQuery
     * @param DOMDocument  $document
     * @param DOMNodeList  $nodeList
     */
    public function __construct($cssQuery, $xpathQuery, DOMDocument $document, DOMNodeList $nodeList)
    {
        $this->cssQuery   = $cssQuery;
        $this->xpathQuery = $xpathQuery;
        $this->document   = $document;
        $this->nodeList   = $nodeList;
    }

    /**
     * Retrieve CSS Query
     *
     * @return string
     */
    public function getCssQuery()
    {
        return $this->cssQuery;
    }

    /**
     * Retrieve XPath query
     *
     * @return string
     */
    public function getXpathQuery()
    {
        return $this->xpathQuery;
    }

    /**
     * Retrieve DOMDocument
     *
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Iterator: rewind to first element
     *
     * @return DOMNode
     */
    public function rewind()
    {
        $this->position = 0;

        return $this->nodeList->item(0);
    }

    /**
     * Iterator: is current position valid?
     *
     * @return bool
     */
    public function valid()
    {
        if (in_array($this->position, range(0, $this->nodeList->length - 1)) && $this->nodeList->length > 0) {
            return true;
        }

        return false;
    }

    /**
     * Iterator: return current element
     *
     * @return DOMNode
     */
    public function current()
    {
        return $this->nodeList->item($this->position);
    }

    /**
     * Iterator: return key of current element
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator: move to next element
     *
     * @return DOMNode
     */
    public function next()
    {
        ++$this->position;

        return $this->nodeList->item($this->position);
    }

    /**
     * Countable: get count
     *
     * @return int
     */
    public function count()
    {
        return $this->nodeList->length;
    }

    /**
     * ArrayAccess: offset exists
     *
     * @param int $key
     * @return bool
     */
    public function offsetExists($key)
    {
        if (in_array($key, range(0, $this->nodeList->length - 1)) && $this->nodeList->length > 0) {
            return true;
        }
        return false;
    }

    /**
     * ArrayAccess: get offset
     *
     * @param int $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->nodeList->item($key);
    }

    /**
     * ArrayAccess: set offset
     *
     * @param  mixed $key
     * @param  mixed $value
     * @throws Exception\BadMethodCallException when attemptingn to write to a read-only item
     */
    public function offsetSet($key, $value)
    {
        throw new Exception\BadMethodCallException('Attempting to write to a read-only list');
    }

    /**
     * ArrayAccess: unset offset
     *
     * @param  mixed $key
     * @throws Exception\BadMethodCallException when attemptingn to unset a read-only item
     */
    public function offsetUnset($key)
    {
        throw new Exception\BadMethodCallException('Attempting to unset on a read-only list');
    }
}
