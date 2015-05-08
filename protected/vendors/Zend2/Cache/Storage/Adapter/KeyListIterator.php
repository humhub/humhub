<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Countable;
use Zend\Cache\Storage\IteratorInterface;
use Zend\Cache\Storage\StorageInterface;

class KeyListIterator implements IteratorInterface, Countable
{

    /**
     * The storage instance
     *
     * @var StorageInterface
     */
    protected $storage;

    /**
     * The iterator mode
     *
     * @var int
     */
    protected $mode = IteratorInterface::CURRENT_AS_KEY;

    /**
     * Keys to iterate over
     *
     * @var string[]
     */
    protected $keys;

    /**
     * Number of keys
     *
     * @var int
     */
    protected $count;

    /**
     * Current iterator position
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Constructor
     *
     * @param StorageInterface $storage
     * @param array            $keys
     */
    public function __construct(StorageInterface $storage, array $keys)
    {
        $this->storage = $storage;
        $this->keys    = $keys;
        $this->count   = count($keys);
    }

    /**
     * Get storage instance
     *
     * @return StorageInterface
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
     * @return KeyListIterator Fluent interface
     */
    public function setMode($mode)
    {
        $this->mode = (int) $mode;
        return $this;
    }

    /**
     * Get current key, value or metadata.
     *
     * @return mixed
     */
    public function current()
    {
        if ($this->mode == IteratorInterface::CURRENT_AS_SELF) {
            return $this;
        }

        $key = $this->key();

        if ($this->mode == IteratorInterface::CURRENT_AS_METADATA) {
            return $this->storage->getMetadata($key);
        } elseif ($this->mode == IteratorInterface::CURRENT_AS_VALUE) {
            return $this->storage->getItem($key);
        }

        return $key;
    }

    /**
     * Get current key
     *
     * @return string
     */
    public function key()
    {
        return $this->keys[$this->position];
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->position < $this->count;
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Count number of items
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }
}
