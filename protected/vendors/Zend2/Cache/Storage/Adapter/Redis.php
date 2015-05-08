<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Redis as RedisResource;
use RedisException as RedisResourceException;
use stdClass;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Cache\Exception;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;

class Redis extends AbstractAdapter implements
    FlushableInterface,
    TotalSpaceCapableInterface
{

    /**
     * Has this instance be initialized
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * The redis resource manager
     *
     * @var null|RedisResourceManager
     */
    protected $resourceManager;

    /**
     * The redis resource id
     *
     * @var null|string
     */
    protected $resourceId;

    /**
     * The namespace prefix
     *
     * @var string
     */
    protected $namespacePrefix = '';

    /**
     * Create new Adapter for redis storage
     *
     * @param null|array|Traversable|RedisOptions $options
     * @see \Zend\Cache\Storage\Adapter\Abstract
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('redis')) {
            throw new Exception\ExtensionNotLoadedException("Redis extension is not loaded");
        }

        parent::__construct($options);

        // reset initialized flag on update option(s)
        $initialized = & $this->initialized;
        $this->getEventManager()->attach('option', function ($event) use (& $initialized) {
            $initialized = false;
        });
    }

    /**
     * Get Redis resource
     *
     * @return RedisResource
     */
    protected function getRedisResource()
    {
        if (!$this->initialized) {
            $options = $this->getOptions();

            // get resource manager and resource id
            $this->resourceManager = $options->getResourceManager();
            $this->resourceId      = $options->getResourceId();

            // init namespace prefix
            $namespace = $options->getNamespace();
            if ($namespace !== '') {
                $this->namespacePrefix = $namespace . $options->getNamespaceSeparator();
            } else {
                $this->namespacePrefix = '';
            }

            // update initialized flag
            $this->initialized = true;
        }

        return $this->resourceManager->getResource($this->resourceId);
    }

    /* options */

    /**
     * Set options.
     *
     * @param  array|Traversable|RedisOptions $options
     * @return Redis
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof RedisOptions) {
            $options = new RedisOptions($options);
        }
        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @return RedisOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new RedisOptions());
        }
        return $this->options;
    }

    /**
     * Internal method to get an item.
     *
     * @param string  &$normalizedKey Key where to store data
     * @param bool &$success       If the operation was successfull
     * @param mixed   &$casToken      Token
     * @return mixed Data on success, false on key not found
     * @throws Exception\RuntimeException
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $redis = $this->getRedisResource();
        try {
            $value = $redis->get($this->namespacePrefix . $normalizedKey);
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }

        if ($value === false) {
            $success = false;
            return null;
        }

        $success = true;
        $casToken = $value;
        return $value;
    }

     /**
     * Internal method to get multiple items.
     *
     * @param array &$normalizedKeys Array of keys to be obtained
     *
     * @return array Associative array of keys and values
     * @throws Exception\RuntimeException
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $redis = $this->getRedisResource();

        $namespacedKeys = array();
        foreach ($normalizedKeys as & $normalizedKey) {
            $namespacedKeys[] = $this->namespacePrefix . $normalizedKey;
        }

        try {
            $results = $redis->mGet($namespacedKeys);
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
        //combine the key => value pairs and remove all missing values
        return array_filter(
            array_combine($normalizedKeys, $results),
            function ($value) {
                return $value !== false;
            }
        );
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param string &$normalizedKey Normalized key which will be checked
     *
     * @return bool
     * @throws Exception\RuntimeException
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $redis = $this->getRedisResource();
        try {
            return $redis->exists($this->namespacePrefix . $normalizedKey);
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
    }

    /**
     * Internal method to store an item.
     *
     * @param string &$normalizedKey Key in Redis under which value will be saved
     * @param mixed  &$value         Value to store under cache key
     *
     * @return bool
     * @throws Exception\RuntimeException
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $redis = $this->getRedisResource();
        $ttl = $this->getOptions()->getTtl();

        try {
            if ($ttl) {
                if ($this->resourceManager->getMajorVersion($this->resourceId) < 2) {
                    throw new Exception\UnsupportedMethodCallException("To use ttl you need version >= 2.0.0");
                }
                $success = $redis->setex($this->namespacePrefix . $normalizedKey, $ttl, $value);
            } else {
                $success = $redis->set($this->namespacePrefix . $normalizedKey, $value);
            }
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }

        return $success;
    }

     /**
     * Internal method to store multiple items.
     *
     * @param array &$normalizedKeyValuePairs An array of normalized key/value pairs
     *
     * @return array Array of not stored keys
     * @throws Exception\RuntimeException
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        $redis = $this->getRedisResource();
        $ttl   = $this->getOptions()->getTtl();

        $namespacedKeyValuePairs = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => & $value) {
            $namespacedKeyValuePairs[$this->namespacePrefix . $normalizedKey] = & $value;
        }
        try {
            if ($ttl > 0) {
                //check if ttl is supported
                if ($this->resourceManager->getMajorVersion($this->resourceId) < 2) {
                    throw new Exception\UnsupportedMethodCallException("To use ttl you need version >= 2.0.0");
                }
                //mSet does not allow ttl, so use transaction
                $transaction = $redis->multi();
                foreach ($namespacedKeyValuePairs as $key => $value) {
                    $transaction->setex($key, $ttl, $value);
                }
                $success = $transaction->exec();
            } else {
                $success = $redis->mSet($namespacedKeyValuePairs);
            }

        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
        if (!$success) {
            throw new Exception\RuntimeException($redis->getLastError());
        }

        return array();
    }

    /**
     * Add an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\RuntimeException
     */
    protected function internalAddItem(& $normalizedKey, & $value)
    {
        $redis = $this->getRedisResource();
        try {
            return $redis->setnx($this->namespacePrefix . $normalizedKey, $value);
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
    }

    /**
     * Internal method to remove an item.
     *
     * @param string &$normalizedKey Key which will be removed
     *
     * @return bool
     * @throws Exception\RuntimeException
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $redis = $this->getRedisResource();
        try {
            return (bool) $redis->delete($this->namespacePrefix . $normalizedKey);
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
    }

    /**
     * Internal method to increment an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\RuntimeException
     */
    protected function internalIncrementItem(& $normalizedKey, & $value)
    {
        $redis = $this->getRedisResource();
        try {
            return $redis->incrBy($this->namespacePrefix . $normalizedKey, $value);
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
    }

    /**
     * Internal method to decrement an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\RuntimeException
     */
    protected function internalDecrementItem(& $normalizedKey, & $value)
    {
        $redis = $this->getRedisResource();
        try {
            return $redis->decrBy($this->namespacePrefix . $normalizedKey, $value);
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
    }

    /**
     * Flush currently set DB
     *
     * @return bool
     * @throws Exception\RuntimeException
     */
    public function flush()
    {
        $redis = $this->getRedisResource();
        try {
            return $redis->flushDB();
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }
    }

    /* TotalSpaceCapableInterface */

    /**
     * Get total space in bytes
     *
     * @return int|float
     */
    public function getTotalSpace()
    {
        $redis  = $this->getRedisResource();
        try {
            $info = $redis->info();
        } catch (RedisResourceException $e) {
            throw new Exception\RuntimeException($redis->getLastError(), $e->getCode(), $e);
        }

        return $info['used_memory'];

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
            $minTtl = $this->resourceManager->getMajorVersion($this->resourceId) < 2 ? 0 : 1;
            //without serialization redis supports only strings for simple
            //get/set methods
            $this->capabilities     = new Capabilities(
                $this,
                $this->capabilityMarker,
                array(
                    'supportedDatatypes' => array(
                        'NULL'     => 'string',
                        'boolean'  => 'string',
                        'integer'  => 'string',
                        'double'   => 'string',
                        'string'   => true,
                        'array'    => false,
                        'object'   => false,
                        'resource' => false,
                    ),
                    'supportedMetadata'  => array(),
                    'minTtl'             => $minTtl,
                    'maxTtl'             => 0,
                    'staticTtl'          => true,
                    'ttlPrecision'       => 1,
                    'useRequestTime'     => false,
                    'expiredRead'        => false,
                    'maxKeyLength'       => 255,
                    'namespaceIsPrefix'  => true,
                )
            );
        }

        return $this->capabilities;
    }
}
