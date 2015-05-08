<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use stdClass;
use Traversable;
use Zend\Cache\Exception;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;

class WinCache extends AbstractAdapter implements
    AvailableSpaceCapableInterface,
    FlushableInterface,
    TotalSpaceCapableInterface
{

    /**
     * Constructor
     *
     * @param  array|Traversable|WinCacheOptions $options
     * @throws Exception\ExceptionInterface
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('wincache')) {
            throw new Exception\ExtensionNotLoadedException("WinCache extension is not loaded");
        }

        $enabled = ini_get('wincache.ucenabled');
        if (PHP_SAPI == 'cli') {
            $enabled = $enabled && (bool) ini_get('wincache.enablecli');
        }

        if (!$enabled) {
            throw new Exception\ExtensionNotLoadedException(
                "WinCache is disabled - see 'wincache.ucenabled' and 'wincache.enablecli'"
            );
        }

        parent::__construct($options);
    }

    /* options */

    /**
     * Set options.
     *
     * @param  array|Traversable|WinCacheOptions $options
     * @return WinCache
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof WinCacheOptions) {
            $options = new WinCacheOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @return WinCacheOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new WinCacheOptions());
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
        $mem = wincache_ucache_meminfo();
        return $mem['memory_total'];
    }

    /* AvailableSpaceCapableInterface */

    /**
     * Get available space in bytes
     *
     * @return int|float
     */
    public function getAvailableSpace()
    {
        $mem = wincache_ucache_meminfo();
        return $mem['memory_free'];
    }

    /* FlushableInterface */

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        return wincache_ucache_clear();
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
        $result      = wincache_ucache_get($internalKey, $success);

        if ($success) {
            $casToken = $result;
        } else {
            $result = null;
        }

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
            return wincache_ucache_get($normalizedKeys);
        }

        $prefix       = $namespace . $options->getNamespaceSeparator();
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $fetch = wincache_ucache_get($internalKeys);

        // remove namespace prefix
        $prefixL = strlen($prefix);
        $result  = array();
        foreach ($fetch as $internalKey => & $value) {
            $result[substr($internalKey, $prefixL)] = & $value;
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
        return wincache_ucache_exists($prefix . $normalizedKey);
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

        $info = wincache_ucache_info(true, $internalKey);
        if (isset($info['ucache_entries'][1])) {
            $metadata = $info['ucache_entries'][1];
            $this->normalizeMetadata($metadata);
            return $metadata;
        }

        return false;
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

        if (!wincache_ucache_set($internalKey, $value, $ttl)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new Exception\RuntimeException(
                "wincache_ucache_set('{$internalKey}', <{$type}>, {$ttl}) failed"
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
            return wincache_ucache_set($normalizedKeyValuePairs, null, $options->getTtl());
        }

        $prefix                = $namespace . $options->getNamespaceSeparator();
        $internalKeyValuePairs = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => & $value) {
            $internalKey = $prefix . $normalizedKey;
            $internalKeyValuePairs[$internalKey] = & $value;
        }

        $result = wincache_ucache_set($internalKeyValuePairs, null, $options->getTtl());

        // remove key prefic
        $prefixL = strlen($prefix);
        foreach ($result as & $key) {
            $key = substr($key, $prefixL);
        }

        return $result;
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

        if (!wincache_ucache_add($internalKey, $value, $ttl)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new Exception\RuntimeException(
                "wincache_ucache_add('{$internalKey}', <{$type}>, {$ttl}) failed"
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
            return wincache_ucache_add($normalizedKeyValuePairs, null, $options->getTtl());
        }

        $prefix                = $namespace . $options->getNamespaceSeparator();
        $internalKeyValuePairs = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            $internalKey = $prefix . $normalizedKey;
            $internalKeyValuePairs[$internalKey] = $value;
        }

        $result = wincache_ucache_add($internalKeyValuePairs, null, $options->getTtl());

        // remove key prefic
        $prefixL = strlen($prefix);
        foreach ($result as & $key) {
            $key = substr($key, $prefixL);
        }

        return $result;
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
        if (!wincache_ucache_exists($internalKey)) {
            return false;
        }

        $ttl = $options->getTtl();
        if (!wincache_ucache_set($internalKey, $value, $ttl)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new Exception\RuntimeException(
                "wincache_ucache_set('{$internalKey}', <{$type}>, {$ttl}) failed"
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
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        return wincache_ucache_delete($internalKey);
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
            $result = wincache_ucache_delete($normalizedKeys);
            return ($result === false) ? $normalizedKeys : $result;
        }

        $prefix       = $namespace . $options->getNamespaceSeparator();
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $result = wincache_ucache_delete($internalKeys);
        if ($result === false) {
            return $normalizedKeys;
        } elseif ($result) {
            // remove key prefix
            $prefixL = strlen($prefix);
            foreach ($result as & $key) {
                $key = substr($key, $prefixL);
            }
        }

        return $result;
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
        return wincache_ucache_inc($internalKey, (int) $value);
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
        return wincache_ucache_dec($internalKey, (int) $value);
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
                        'internal_key', 'ttl', 'hits', 'size'
                    ),
                    'minTtl'             => 1,
                    'maxTtl'             => 0,
                    'staticTtl'          => true,
                    'ttlPrecision'       => 1,
                    'useRequestTime'     => false,
                    'expiredRead'        => false,
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
     * Normalize metadata to work with WinCache
     *
     * @param  array $metadata
     * @return void
     */
    protected function normalizeMetadata(array & $metadata)
    {
        $metadata['internal_key'] = $metadata['key_name'];
        $metadata['hits']         = $metadata['hitcount'];
        $metadata['ttl']          = $metadata['ttl_seconds'];
        $metadata['size']         = $metadata['value_size'];

        unset(
            $metadata['key_name'],
            $metadata['hitcount'],
            $metadata['ttl_seconds'],
            $metadata['value_size']
        );
    }
}
