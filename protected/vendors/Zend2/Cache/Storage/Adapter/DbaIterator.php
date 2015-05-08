<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Zend\Cache\Exception;
use Zend\Cache\Storage\IteratorInterface;

class DbaIterator implements IteratorInterface
{
    /**
     * The apc storage instance
     *
     * @var Apc
     */
    protected $storage;

    /**
     * The iterator mode
     *
     * @var int
     */
    protected $mode = IteratorInterface::CURRENT_AS_KEY;

    /**
     * The dba resource handle
     *
     * @var resource
     */
    protected $handle;

    /**
     * The length of the namespace prefix
     *
     * @var int
     */
    protected $prefixLength;

    /**
     * The current internal key
     *
     * @var string|bool
     */
    protected $currentInternalKey;

    /**
     * Constructor
     *
     * @param Dba      $storage
     * @param resource $handle
     * @param string   $prefix
     */
    public function __construct(Dba $storage, $handle, $prefix)
    {
        $this->storage      = $storage;
        $this->handle       = $handle;
        $this->prefixLength = strlen($prefix);

        $this->rewind();
    }

    /**
     * Get storage instance
     *
     * @return Dba
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get iterator mode
     *
     * @return int Value of IteratorInterface::CURRENT_AS_*
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set iterator mode
     *
     * @param int $mode
     * @return ApcIterator Fluent interface
     */
    public function setMode($mode)
    {
        $this->mode = (int) $mode;
        return $this;
    }

    /* Iterator */

    /**
     * Get current key, value or metadata.
     *
     * @return mixed
     * @throws Exception\RuntimeException
     */
    public function current()
    {
        if ($this->mode == IteratorInterface::CURRENT_AS_SELF) {
            return $this;
        }

        $key = $this->key();

        if ($this->mode == IteratorInterface::CURRENT_AS_VALUE) {
            return $this->storage->getItem($key);
        } elseif ($this->mode == IteratorInterface::CURRENT_AS_METADATA) {
            return $this->storage->getMetadata($key);
        }

        return $key;
    }

    /**
     * Get current key
     *
     * @return string
     * @throws Exception\RuntimeException
     */
    public function key()
    {
        if ($this->currentInternalKey === false) {
            throw new Exception\RuntimeException("Iterator is on an invalid state");
        }

        // remove namespace prefix
        return substr($this->currentInternalKey, $this->prefixLength);
    }

    /**
     * Move forward to next element
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    public function next()
    {
        if ($this->currentInternalKey === false) {
            throw new Exception\RuntimeException("Iterator is on an invalid state");
        }

        $this->currentInternalKey = dba_nextkey($this->handle);

        // Workaround for PHP-Bug #62492
        if ($this->currentInternalKey === null) {
            $this->currentInternalKey = false;
        }
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return ($this->currentInternalKey !== false);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    public function rewind()
    {
        if ($this->currentInternalKey === false) {
            throw new Exception\RuntimeException("Iterator is on an invalid state");
        }

        $this->currentInternalKey = dba_firstkey($this->handle);

        // Workaround for PHP-Bug #62492
        if ($this->currentInternalKey === null) {
            $this->currentInternalKey = false;
        }
    }
}
