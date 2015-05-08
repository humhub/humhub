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
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;
use Zend\Stdlib\ErrorHandler;

class ZendServerDisk extends AbstractZendServer implements
    AvailableSpaceCapableInterface,
    ClearByNamespaceInterface,
    FlushableInterface,
    TotalSpaceCapableInterface
{

    /**
     * Buffered total space in bytes
     *
     * @var null|int|float
     */
    protected $totalSpace;

    /**
     * Constructor
     *
     * @param  null|array|\Traversable|AdapterOptions $options
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct($options = array())
    {
        if (!function_exists('zend_disk_cache_store')) {
            throw new Exception\ExtensionNotLoadedException("Missing 'zend_disk_cache_*' functions");
        } elseif (PHP_SAPI == 'cli') {
            throw new Exception\ExtensionNotLoadedException("Zend server data cache isn't available on cli");
        }

        parent::__construct($options);
    }

    /* FlushableInterface */

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        return zend_disk_cache_clear();
    }

    /* ClearByNamespaceInterface */

    /**
     * Remove items of given namespace
     *
     * @param string $namespace
     * @return bool
     */
    public function clearByNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($namespace === '') {
            throw new Exception\InvalidArgumentException('No namespace given');
        }

        return zend_disk_cache_clear($namespace);
    }

    /* TotalSpaceCapableInterface */

    /**
     * Get total space in bytes
     *
     * @throws Exception\RuntimeException
     * @return int|float
     */
    public function getTotalSpace()
    {
        if ($this->totalSpace === null) {
            $path = ini_get('zend_datacache.disk.save_path');

            ErrorHandler::start();
            $total = disk_total_space($path);
            $error = ErrorHandler::stop();
            if ($total === false) {
                throw new Exception\RuntimeException("Can't detect total space of '{$path}'", 0, $error);
            }

            $this->totalSpace = $total;
        }
        return $this->totalSpace;
    }

    /* AvailableSpaceCapableInterface */

    /**
     * Get available space in bytes
     *
     * @throws Exception\RuntimeException
     * @return int|float
     */
    public function getAvailableSpace()
    {
        $path = ini_get('zend_datacache.disk.save_path');

        ErrorHandler::start();
        $avail = disk_free_space($path);
        $error = ErrorHandler::stop();
        if ($avail === false) {
            throw new Exception\RuntimeException("Can't detect free space of '{$path}'", 0, $error);
        }

        return $avail;
    }

    /* internal  */

    /**
     * Store data into Zend Data Disk Cache
     *
     * @param  string $internalKey
     * @param  mixed  $value
     * @param  int    $ttl
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function zdcStore($internalKey, $value, $ttl)
    {
        if (!zend_disk_cache_store($internalKey, $value, $ttl)) {
            $valueType = gettype($value);
            throw new Exception\RuntimeException(
                "zend_disk_cache_store($internalKey, <{$valueType}>, {$ttl}) failed"
            );
        }
    }

    /**
     * Fetch a single item from Zend Data Disk Cache
     *
     * @param  string $internalKey
     * @return mixed The stored value or FALSE if item wasn't found
     * @throws Exception\RuntimeException
     */
    protected function zdcFetch($internalKey)
    {
        return zend_disk_cache_fetch((string) $internalKey);
    }

    /**
     * Fetch multiple items from Zend Data Disk Cache
     *
     * @param  array $internalKeys
     * @return array All found items
     * @throws Exception\RuntimeException
     */
    protected function zdcFetchMulti(array $internalKeys)
    {
        $items = zend_disk_cache_fetch($internalKeys);
        if ($items === false) {
            throw new Exception\RuntimeException("zend_disk_cache_fetch(<array>) failed");
        }
        return $items;
    }

    /**
     * Delete data from Zend Data Disk Cache
     *
     * @param  string $internalKey
     * @return bool
     * @throws Exception\RuntimeException
     */
    protected function zdcDelete($internalKey)
    {
        return zend_disk_cache_delete($internalKey);
    }
}
