<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use GlobIterator;
use Zend\Cache\Storage\IteratorInterface;

class FilesystemIterator implements IteratorInterface
{

    /**
     * The Filesystem storage instance
     *
     * @var Filesystem
     */
    protected $storage;

    /**
     * The iterator mode
     *
     * @var int
     */
    protected $mode = IteratorInterface::CURRENT_AS_KEY;

    /**
     * The GlobIterator instance
     *
     * @var GlobIterator
     */
    protected $globIterator;

    /**
     * The namespace sprefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * String length of namespace prefix
     *
     * @var int
     */
    protected $prefixLength;

    /**
     * Constructor
     *
     * @param Filesystem  $storage
     * @param string      $path
     * @param string      $prefix
     */
    public function __construct(Filesystem $storage, $path, $prefix)
    {
        $this->storage      = $storage;
        $this->globIterator = new GlobIterator($path, GlobIterator::KEY_AS_FILENAME);
        $this->prefix       = $prefix;
        $this->prefixLength = strlen($prefix);
    }

    /**
     * Get storage instance
     *
     * @return Filesystem
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
     * @return FilesystemIterator Fluent interface
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
     */
    public function key()
    {
        $filename = $this->globIterator->key();

        // return without namespace prefix and file suffix
        return substr($filename, $this->prefixLength, -4);
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        $this->globIterator->next();
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        try {
            return $this->globIterator->valid();
        } catch (\LogicException $e) {
            // @link https://bugs.php.net/bug.php?id=55701
            // GlobIterator throws LogicException with message
            // 'The parent constructor was not called: the object is in an invalid state'
            return false;
        }
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return bool false if the operation failed.
     */
    public function rewind()
    {
        try {
            return $this->globIterator->rewind();
        } catch (\LogicException $e) {
            // @link https://bugs.php.net/bug.php?id=55701
            // GlobIterator throws LogicException with message
            // 'The parent constructor was not called: the object is in an invalid state'
            return false;
        }
    }
}
