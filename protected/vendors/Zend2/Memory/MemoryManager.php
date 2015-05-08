<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Memory;

use Zend\Cache\Storage\ClearByNamespaceInterface as ClearByNamespaceCacheStorage;
use Zend\Cache\Storage\FlushableInterface as FlushableCacheStorage;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

/**
 * Memory manager
 *
 * This class encapsulates memory menagement operations, when PHP works
 * in limited memory mode.
 */
class MemoryManager
{
    /**
     * Storage cache object
     *
     * @var CacheStorage
     */
    private $cache = null;

    /**
     * Memory grow limit.
     * Default value is 2/3 of memory_limit php.ini variable
     * Negative value means no limit
     *
     * @var int
     */
    private $memoryLimit = -1;

    /**
     * Minimum value size to be swapped.
     * Default value is 16K
     * Negative value means that memory objects are never swapped
     *
     * @var int
     */
    private $minSize = 16384;

    /**
     * Overall size of memory, used by values
     *
     * @var int
     */
    private $memorySize = 0;

    /**
     * Id for next Zend\Memory object
     *
     * @var int
     */
    private $nextId = 0;

    /**
     * List of candidates to unload
     *
     * It also represents objects access history. Last accessed objects are moved to the end of array
     *
     * array(
     *     <id> => <memory container object>,
     *     ...
     *      )
     *
     * @var array
     */
    private $unloadCandidates = array();

    /**
     * List of object sizes.
     *
     * This list is used to calculate modification of object sizes
     *
     * array( <id> => <size>, ...)
     *
     * @var array
     */
    private $sizes = array();

    /**
     * Last modified object
     *
     * It's used to reduce number of calls necessary to trace objects' modifications
     * Modification is not processed by memory manager until we do not switch to another
     * object.
     * So we have to trace only _first_ object modification and do nothing for others
     *
     * @var \Zend\Memory\Container\Movable
     */
    private $lastModified = null;

    /**
     * Unique memory manager id
     *
     * @var int
     */
    private $managerId;

    /**
     * This function is intended to generate unique id, used by memory manager
     */
    private function _generateMemManagerId()
    {
        /**
         * @todo !!!
         * uniqid() php function doesn't really guarantee the id to be unique
         * it should be changed by something else
         * (Ex. backend interface should be extended to provide this functionality)
         */
        $this->managerId = str_replace('.', '_', uniqid('ZendMemManager', true)) . '_';
    }

    /**
     * Memory manager constructor
     *
     * If cache is not specified, then memory objects are never swapped
     *
     * @param  CacheStorage $cache
     */
    public function __construct(CacheStorage $cache = null)
    {
        if ($cache === null) {
            return;
        }

        $this->cache = $cache;
        $this->_generateMemManagerId();

        $memoryLimitStr = trim(ini_get('memory_limit'));
        if ($memoryLimitStr != '' && $memoryLimitStr != -1) {
            $this->memoryLimit = (int) $memoryLimitStr;
            switch (strtolower($memoryLimitStr[strlen($memoryLimitStr) - 1])) {
                case 'g':
                    $this->memoryLimit *= 1024;
                    // no break
                case 'm':
                    $this->memoryLimit *= 1024;
                    // no break
                case 'k':
                    $this->memoryLimit *= 1024;
                    break;
                default:
                    break;
            }

            $this->memoryLimit = (int) ($this->memoryLimit*2/3);
        } // No limit otherwise
    }

    /**
     * Object destructor
     *
     * Clean up cache storage
     */
    public function __destruct()
    {
        if ($this->cache !== null) {
            if ($this->cache instanceof ClearByNamespaceCacheStorage) {
                $this->cache->clearByNamespace($this->cache->getOptions()->getNamespace());
            } elseif ($this->cache instanceof FlushableCacheStorage) {
                $this->cache->flush();
            }
        }
    }

    /**
     * Set memory grow limit
     *
     * @param int $newLimit
     */
    public function setMemoryLimit($newLimit)
    {
        $this->memoryLimit = $newLimit;

        $this->_swapCheck();
    }

    /**
     * Get memory grow limit
     *
     * @return int
     */
    public function getMemoryLimit()
    {
        return $this->memoryLimit;
    }

    /**
     * Set minimum size of values, which may be swapped
     *
     * @param int $newSize
     */
    public function setMinSize($newSize)
    {
        $this->minSize = $newSize;
    }

    /**
     * Get minimum size of values, which may be swapped
     *
     * @return int
     */
    public function getMinSize()
    {
        return $this->minSize;
    }

    /**
     * Create new Zend\Memory value container
     *
     * @param string $value
     * @return Container\ContainerInterface
     * @throws Exception\ExceptionInterface
     */
    public function create($value = '')
    {
        return $this->_create($value,  false);
    }

    /**
     * Create new Zend\Memory value container, which has value always
     * locked in memory
     *
     * @param string $value
     * @return Container\ContainerInterface
     * @throws Exception\ExceptionInterface
     */
    public function createLocked($value = '')
    {
        return $this->_create($value, true);
    }

    /**
     * Create new Zend\Memory object
     *
     * @param string $value
     * @param  bool $locked
     * @return \Zend\Memory\Container\ContainerInterface
     * @throws \Zend\Memory\Exception\ExceptionInterface
     */
    private function _create($value, $locked)
    {
        $id = $this->nextId++;

        if ($locked  ||  ($this->cache === null) /* Use only memory locked objects if backend is not specified */) {
            return new Container\Locked($value);
        }

        // Commit other objects modifications
        $this->_commit();

        $valueObject = new Container\Movable($this, $id, $value);

        // Store last object size as 0
        $this->sizes[$id] = 0;
        // prepare object for next modifications
        $this->lastModified = $valueObject;

        return new Container\AccessController($valueObject);
    }

    /**
     * Unlink value container from memory manager
     *
     * Used by Memory container destroy() method
     *
     * @internal
     * @param Container\Movable $container
     * @param int $id
     * @return null
     */
    public function unlink(Container\Movable $container, $id)
    {
        if ($this->lastModified === $container) {
            // Drop all object modifications
            $this->lastModified = null;
            unset($this->sizes[$id]);
            return;
        }

        if (isset($this->unloadCandidates[$id])) {
            unset($this->unloadCandidates[$id]);
        }

        $this->memorySize -= $this->sizes[$id];
        unset($this->sizes[$id]);
    }

    /**
     * Process value update
     *
     * @internal
     * @param \Zend\Memory\Container\Movable $container
     * @param int $id
     */
    public function processUpdate(Container\Movable $container, $id)
    {
        /**
         * This method is automatically invoked by memory container only once per
         * "modification session", but user may call memory container touch() method
         * several times depending on used algorithm. So we have to use this check
         * to optimize this case.
         */
        if ($container === $this->lastModified) {
            return;
        }

        // Remove just updated object from list of candidates to unload
        if (isset($this->unloadCandidates[$id])) {
            unset($this->unloadCandidates[$id]);
        }

        // Reduce used memory mark
        $this->memorySize -= $this->sizes[$id];

        // Commit changes of previously modified object if necessary
        $this->_commit();

        $this->lastModified = $container;
    }

    /**
     * Commit modified object and put it back to the loaded objects list
     */
    private function _commit()
    {
        if (($container = $this->lastModified) === null) {
            return;
        }

        $this->lastModified = null;

        $id = $container->getId();

        // Calculate new object size and increase used memory size by this value
        $this->memorySize += ($this->sizes[$id] = strlen($container->getRef()));

        if ($this->sizes[$id] > $this->minSize) {
            // Move object to "unload candidates list"
            $this->unloadCandidates[$id] = $container;
        }

        $container->startTrace();

        $this->_swapCheck();
    }

    /**
     * Check and swap objects if necessary
     *
     * @throws Exception\RuntimeException
     */
    private function _swapCheck()
    {
        if ($this->memoryLimit < 0  ||  $this->memorySize < $this->memoryLimit) {
            // Memory limit is not reached
            // Do nothing
            return;
        }

        // walk through loaded objects in access history order
        foreach ($this->unloadCandidates as $id => $container) {
            $this->_swap($container, $id);
            unset($this->unloadCandidates[$id]);

            if ($this->memorySize < $this->memoryLimit) {
                // We've swapped enough objects
                return;
            }
        }

        throw new Exception\RuntimeException('Memory manager can\'t get enough space.');
    }

    /**
     * Swap object data to disk
     * Actually swaps data or only unloads it from memory,
     * if object is not changed since last swap
     *
     * @param \Zend\Memory\Container\Movable $container
     * @param int $id
     */
    private function _swap(Container\Movable $container, $id)
    {
        if ($container->isLocked()) {
            return;
        }

        if (!$container->isSwapped()) {
            $this->cache->setItem($this->managerId . $id, $container->getRef());
        }

        $this->memorySize -= $this->sizes[$id];

        $container->markAsSwapped();
        $container->unloadValue();
    }

    /**
     * Load value from swap file.
     *
     * @internal
     * @param \Zend\Memory\Container\Movable $container
     * @param int $id
     */
    public function load(Container\Movable $container, $id)
    {
        $value = $this->cache->getItem($this->managerId . $id);

        // Try to swap other objects if necessary
        // (do not include specified object into check)
        $this->memorySize += strlen($value);
        $this->_swapCheck();

        // Add loaded object to the end of loaded objects list
        $container->setValue($value);

        if ($this->sizes[$id] > $this->minSize) {
            // Add object to the end of "unload candidates list"
            $this->unloadCandidates[$id] = $container;
        }
    }
}
