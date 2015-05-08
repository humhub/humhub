<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use APCIterator as BaseApcIterator;
use stdClass;
use Traversable;
use Zend\Cache\Exception;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\ClearByPrefixInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\IterableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;

class Apc extends AbstractAdapter implements
    AvailableSpaceCapableInterface,
    ClearByNamespaceInterface,
    ClearByPrefixInterface,
    FlushableInterface,
    IterableInterface,
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
     * @param  null|array|Traversable|ApcOptions $options
     * @throws Exception\ExceptionInterface
     */
    public function __construct($options = null)
    {
        if (version_compare('3.1.6', phpversion('apc')) > 0) {
            throw new Exception\ExtensionNotLoadedException("Missing ext/apc >= 3.1.6");
        }

        $enabled = ini_get('apc.enabled');
        if (PHP_SAPI == 'cli') {
            $enabled = $enabled && (bool) ini_get('apc.enable_cli');
        }

        if (!$enabled) {
            throw new Exception\ExtensionNotLoadedException(
                "ext/apc is disabled - see 'apc.enabled' and 'apc.enable_cli'"
            );
        }

        parent::__construct($options);
    }

    /* options */

    /**
     * Set options.
     *
     * @param  array|Traversable|ApcOptions $options
     * @return Apc
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof ApcOptions) {
            $options = new ApcOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @return ApcOptions
     * @see    setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new ApcOptions());
        }
        return $this->options;
    }

    /* TotalSpaceCapableInterface */

    /**
     * Get total space in bytes
     *
     * @return int|float
     */
    public function getTotalSpace()
    {
        if ($this->totalSpace === null) {
            $smaInfo = apc_sma_info(true);
            $this->totalSpace = $smaInfo['num_seg'] * $smaInfo['seg_size'];
        }

        return $this->totalSpace;
    }

    /* AvailableSpaceCapableInterface */

    /**
     * Get available space in bytes
     *
     * @return int|float
     */
    public function getAvailableSpace()
    {
        $smaInfo = apc_sma_info(true);
        return $smaInfo['avail_mem'];
    }

    /* IterableInterface */

    /**
     * Get the storage iterator
     *
     * @return ApcIterator
     */
    public function getIterator()
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = '';
        $pattern   = null;
        if ($namespace !== '') {
            $prefix  = $namespace . $options->getNamespaceSeparator();
            $pattern = '/^' . preg_quote($prefix, '/') . '/';
        }

        $baseIt = new BaseApcIterator('user', $pattern, 0, 1, APC_LIST_ACTIVE);
        return new ApcIterator($this, $baseIt, $prefix);
    }

    /* FlushableInterface */

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        return apc_clear_cache('user');
    }

    /* ClearByNamespaceInterface */

    /**
     * Remove items by given namespace
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

        $options = $this->getOptions();
        $prefix  = $namespace . $options->getNamespaceSeparator();
        $pattern = '/^' . preg_quote($prefix, '/') . '/';
        return apc_delete(new BaseApcIterator('user', $pattern, 0, 1, APC_LIST_ACTIVE));
    }

    /* ClearByPrefixInterface */

    /**
     * Remove items matching given prefix
     *
     * @param string $prefix
     * @return bool
     */
    public function clearByPrefix($prefix)
    {
        $prefix = (string) $prefix;
        if ($prefix === '') {
            throw new Exception\InvalidArgumentException('No prefix given');
        }

        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $nsPrefix  = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $pattern = '/^' . preg_quote($nsPrefix . $prefix, '/') . '/';
        return apc_delete(new BaseApcIterator('user', $pattern, 0, 1, APC_LIST_ACTIVE));
    }

    /* reading */

    /**
     * Internal method to get an item.
     *
     * @param  string  $normalizedKey
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $result      = apc_fetch($internalKey, $success);

        if (!$success) {
            return null;
        }

        $casToken = $result;
        return $result;
    }

    /**
     * Internal method to get multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        if ($namespace === '') {
            return apc_fetch($normalizedKeys);
        }

        $prefix       = $namespace . $options->getNamespaceSeparator();
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $fetch = apc_fetch($internalKeys);

        // remove namespace prefix
        $prefixL = strlen($prefix);
        $result  = array();
        foreach ($fetch as $internalKey => & $value) {
            $result[substr($internalKey, $prefixL)] = $value;
        }

        return $result;
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        return apc_exists($prefix . $normalizedKey);
    }

    /**
     * Internal method to test multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of found keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItems(array & $normalizedKeys)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        if ($namespace === '') {
            // array_filter with no callback will remove entries equal to FALSE
            return array_keys(array_filter(apc_exists($normalizedKeys)));
        }

        $prefix       = $namespace . $options->getNamespaceSeparator();
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $exists  = apc_exists($internalKeys);
        $result  = array();
        $prefixL = strlen($prefix);
        foreach ($exists as $internalKey => $bool) {
            if ($bool === true) {
                $result[] = substr($internalKey, $prefixL);
            }
        }

        return $result;
    }

    /**
     * Get metadata of an item.
     *
     * @param  string $normalizedKey
     * @return array|bool Metadata on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;

        // @see http://pecl.php.net/bugs/bug.php?id=22564
        if (!apc_exists($internalKey)) {
            $metadata = false;
        } else {
            $format   = APC_ITER_ALL ^ APC_ITER_VALUE ^ APC_ITER_TYPE ^ APC_ITER_REFCOUNT;
            $regexp   = '/^' . preg_quote($internalKey, '/') . '$/';
            $it       = new BaseApcIterator('user', $regexp, $format, 100, APC_LIST_ACTIVE);
            $metadata = $it->current();
        }

        if (!$metadata) {
            return false;
        }

        $this->normalizeMetadata($metadata);
        return $metadata;
    }

    /**
     * Get metadata of multiple items
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and metadata
     *
     * @triggers getMetadatas.pre(PreEvent)
     * @triggers getMetadatas.post(PostEvent)
     * @triggers getMetadatas.exception(ExceptionEvent)
     */
    protected function internalGetMetadatas(array & $normalizedKeys)
    {
        $keysRegExp = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $keysRegExp[] = preg_quote($normalizedKey, '/');
        }

        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        if ($namespace === '') {
            $pattern = '/^(' . implode('|', $keysRegExp) . ')' . '$/';
        } else {
            $prefix  = $namespace . $options->getNamespaceSeparator();
            $pattern = '/^' . preg_quote($prefix, '/') . '(' . implode('|', $keysRegExp) . ')' . '$/';
        }
        $format  = APC_ITER_ALL ^ APC_ITER_VALUE ^ APC_ITER_TYPE ^ APC_ITER_REFCOUNT;
        $it      = new BaseApcIterator('user', $pattern, $format, 100, APC_LIST_ACTIVE);
        $result  = array();
        $prefixL = strlen($prefix);
        foreach ($it as $internalKey => $metadata) {
            // @see http://pecl.php.net/bugs/bug.php?id=22564
            if (!apc_exists($internalKey)) {
                continue;
            }

            $this->normalizeMetadata($metadata);
            $result[substr($internalKey, $prefixL)] = & $metadata;
        }

        return $result;
    }

    /* writing */

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $ttl         = $options->getTtl();

        if (!apc_store($internalKey, $value, $ttl)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new Exception\RuntimeException(
                "apc_store('{$internalKey}', <{$type}>, {$ttl}) failed"
            );
        }

        return true;
    }

    /**
     * Internal method to store multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        if ($namespace === '') {
            return array_keys(apc_store($normalizedKeyValuePairs, null, $options->getTtl()));
        }

        $prefix                = $namespace . $options->getNamespaceSeparator();
        $internalKeyValuePairs = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => &$value) {
            $internalKey = $prefix . $normalizedKey;
            $internalKeyValuePairs[$internalKey] = &$value;
        }

        $failedKeys = apc_store($internalKeyValuePairs, null, $options->getTtl());
        $failedKeys = array_keys($failedKeys);

        // remove prefix
        $prefixL = strlen($prefix);
        foreach ($failedKeys as & $key) {
            $key = substr($key, $prefixL);
        }

        return $failedKeys;
    }

    /**
     * Add an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalAddItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $ttl         = $options->getTtl();

        if (!apc_add($internalKey, $value, $ttl)) {
            if (apc_exists($internalKey)) {
                return false;
            }

            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new Exception\RuntimeException(
                "apc_add('{$internalKey}', <{$type}>, {$ttl}) failed"
            );
        }

        return true;
    }

    /**
     * Internal method to add multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalAddItems(array & $normalizedKeyValuePairs)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        if ($namespace === '') {
            return array_keys(apc_add($normalizedKeyValuePairs, null, $options->getTtl()));
        }

        $prefix                = $namespace . $options->getNamespaceSeparator();
        $internalKeyValuePairs = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            $internalKey = $prefix . $normalizedKey;
            $internalKeyValuePairs[$internalKey] = $value;
        }

        $failedKeys = apc_add($internalKeyValuePairs, null, $options->getTtl());
        $failedKeys = array_keys($failedKeys);

        // remove prefix
        $prefixL = strlen($prefix);
        foreach ($failedKeys as & $key) {
            $key = substr($key, $prefixL);
        }

        return $failedKeys;
    }

    /**
     * Internal method to replace an existing item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalReplaceItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;

        if (!apc_exists($internalKey)) {
            return false;
        }

        $ttl = $options->getTtl();
        if (!apc_store($internalKey, $value, $ttl)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new Exception\RuntimeException(
                "apc_store('{$internalKey}', <{$type}>, {$ttl}) failed"
            );
        }

        return true;
    }

    /**
     * Internal method to remove an item.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        return apc_delete($prefix . $normalizedKey);
    }

    /**
     * Internal method to remove multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Array of not removed keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItems(array & $normalizedKeys)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        if ($namespace === '') {
            return apc_delete($normalizedKeys);
        }

        $prefix       = $namespace . $options->getNamespaceSeparator();
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $failedKeys = apc_delete($internalKeys);

        // remove prefix
        $prefixL = strlen($prefix);
        foreach ($failedKeys as & $key) {
            $key = substr($key, $prefixL);
        }

        return $failedKeys;
    }

    /**
     * Internal method to increment an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalIncrementItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $ttl         = $options->getTtl();
        $value       = (int) $value;
        $newValue    = apc_inc($internalKey, $value);

        // initial value
        if ($newValue === false) {
            $ttl      = $options->getTtl();
            $newValue = $value;
            if (!apc_add($internalKey, $newValue, $ttl)) {
                throw new Exception\RuntimeException(
                    "apc_add('{$internalKey}', {$newValue}, {$ttl}) failed"
                );
            }
        }

        return $newValue;
    }

    /**
     * Internal method to decrement an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalDecrementItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $value       = (int) $value;
        $newValue    = apc_dec($internalKey, $value);

        // initial value
        if ($newValue === false) {
            $ttl      = $options->getTtl();
            $newValue = -$value;
            if (!apc_add($internalKey, $newValue, $ttl)) {
                throw new Exception\RuntimeException(
                    "apc_add('{$internalKey}', {$newValue}, {$ttl}) failed"
                );
            }
        }

        return $newValue;
    }

    /* status */

    /**
     * Internal method to get capabilities of this adapter
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $marker       = new stdClass();
            $capabilities = new Capabilities(
                $this,
                $marker,
                array(
                    'supportedDatatypes' => array(
                        'NULL'     => true,
                        'boolean'  => true,
                        'integer'  => true,
                        'double'   => true,
                        'string'   => true,
                        'array'    => true,
                        'object'   => 'object',
                        'resource' => false,
                    ),
                    'supportedMetadata' => array(
                        'internal_key',
                        'atime', 'ctime', 'mtime', 'rtime',
                        'size', 'hits', 'ttl',
                    ),
                    'minTtl'             => 1,
                    'maxTtl'             => 0,
                    'staticTtl'          => true,
                    'ttlPrecision'       => 1,
                    'useRequestTime'     => (bool) ini_get('apc.use_request_time'),
                    'expiredRead'        => false,
                    'maxKeyLength'       => 5182,
                    'namespaceIsPrefix'  => true,
                    'namespaceSeparator' => $this->getOptions()->getNamespaceSeparator(),
                )
            );

            // update namespace separator on change option
            $this->getEventManager()->attach('option', function ($event) use ($capabilities, $marker) {
                $params = $event->getParams();

                if (isset($params['namespace_separator'])) {
                    $capabilities->setNamespaceSeparator($marker, $params['namespace_separator']);
                }
            });

            $this->capabilities     = $capabilities;
            $this->capabilityMarker = $marker;
        }

        return $this->capabilities;
    }

    /* internal */

    /**
     * Normalize metadata to work with APC
     *
     * @param  array $metadata
     * @return void
     */
    protected function normalizeMetadata(array & $metadata)
    {
        $metadata['internal_key'] = $metadata['key'];
        $metadata['ctime']        = $metadata['creation_time'];
        $metadata['atime']        = $metadata['access_time'];
        $metadata['rtime']        = $metadata['deletion_time'];
        $metadata['size']         = $metadata['mem_size'];
        $metadata['hits']         = $metadata['num_hits'];

        unset(
            $metadata['key'],
            $metadata['creation_time'],
            $metadata['access_time'],
            $metadata['deletion_time'],
            $metadata['mem_size'],
            $metadata['num_hits']
        );
    }
}
