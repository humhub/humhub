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
use Zend\Cache\Exception;
use Zend\Cache\Storage\Capabilities;

abstract class AbstractZendServer extends AbstractAdapter
{
    /**
     * The namespace separator used on Zend Data Cache functions
     *
     * @var string
     */
    const NAMESPACE_SEPARATOR = '::';

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
        $namespace   = $this->getOptions()->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . self::NAMESPACE_SEPARATOR;

        $result = $this->zdcFetch($prefix . $normalizedKey);
        if ($result === false) {
            $success = false;
            $result  = null;
        } else {
            $success  = true;
            $casToken = $result;
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
        $namespace = $this->getOptions()->getNamespace();
        if ($namespace === '') {
            return $this->zdcFetchMulti($normalizedKeys);
        }

        $prefix       = $namespace . self::NAMESPACE_SEPARATOR;
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $fetch   = $this->zdcFetchMulti($internalKeys);
        $result  = array();
        $prefixL = strlen($prefix);
        foreach ($fetch as $k => & $v) {
            $result[substr($k, $prefixL)] = $v;
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
        $namespace = $this->getOptions()->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . self::NAMESPACE_SEPARATOR;
        return  ($this->zdcFetch($prefix . $normalizedKey) !== false);
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
        $namespace = $this->getOptions()->getNamespace();
        if ($namespace === '') {
            return array_keys($this->zdcFetchMulti($normalizedKeys));
        }

        $prefix       = $namespace . self::NAMESPACE_SEPARATOR;
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $fetch   = $this->zdcFetchMulti($internalKeys);
        $result  = array();
        $prefixL = strlen($prefix);
        foreach ($fetch as $internalKey => & $value) {
            $result[] = substr($internalKey, $prefixL);
        }

        return $result;
    }

    /**
     * Get metadata for multiple items
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
        $namespace = $this->getOptions()->getNamespace();
        if ($namespace === '') {
            $result = $this->zdcFetchMulti($normalizedKeys);
            return array_fill_keys(array_keys($result), array());
        }

        $prefix       = $namespace . self::NAMESPACE_SEPARATOR;
        $internalKeys = array();
        foreach ($normalizedKeys as $normalizedKey) {
            $internalKeys[] = $prefix . $normalizedKey;
        }

        $fetch   = $this->zdcFetchMulti($internalKeys);
        $result  = array();
        $prefixL = strlen($prefix);
        foreach ($fetch as $internalKey => $value) {
            $result[substr($internalKey, $prefixL)] = array();
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
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . self::NAMESPACE_SEPARATOR;
        $this->zdcStore($prefix . $normalizedKey, $value, $options->getTtl());
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
        $namespace = $this->getOptions()->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . self::NAMESPACE_SEPARATOR;
        return $this->zdcDelete($prefix . $normalizedKey);
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
            $this->capabilityMarker = new stdClass();
            $this->capabilities     = new Capabilities(
                $this,
                $this->capabilityMarker,
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
                    'supportedMetadata'  => array(),
                    'maxTtl'             => 0,
                    'staticTtl'          => true,
                    'ttlPrecision'       => 1,
                    'useRequestTime'     => false,
                    'expiredRead'        => false,
                    'maxKeyLength'       => 0,
                    'namespaceIsPrefix'  => true,
                    'namespaceSeparator' => self::NAMESPACE_SEPARATOR,
                )
            );
        }

        return $this->capabilities;
    }

    /* internal wrapper of zend_[disk|shm]_cache_* functions */

    /**
     * Store data into Zend Data Cache (zdc)
     *
     * @param  string $internalKey
     * @param  mixed  $value
     * @param  int    $ttl
     * @return void
     * @throws Exception\RuntimeException
     */
    abstract protected function zdcStore($internalKey, $value, $ttl);

    /**
     * Fetch a single item from Zend Data Cache (zdc)
     *
     * @param  string $internalKey
     * @return mixed The stored value or FALSE if item wasn't found
     * @throws Exception\RuntimeException
     */
    abstract protected function zdcFetch($internalKey);

    /**
     * Fetch multiple items from Zend Data Cache (zdc)
     *
     * @param  array $internalKeys
     * @return array All found items
     * @throws Exception\RuntimeException
     */
    abstract protected function zdcFetchMulti(array $internalKeys);

    /**
     * Delete data from Zend Data Cache (zdc)
     *
     * @param  string $internalKey
     * @return bool
     * @throws Exception\RuntimeException
     */
    abstract protected function zdcDelete($internalKey);
}
