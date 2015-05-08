<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Memcached as MemcachedResource;
use Zend\Cache\Exception;

/**
 * These are options specific to the APC adapter
 */
class MemcachedOptions extends AdapterOptions
{
    /**
     * The namespace separator
     * @var string
     */
    protected $namespaceSeparator = ':';

    /**
     * The memcached resource manager
     *
     * @var null|MemcachedResourceManager
     */
    protected $resourceManager;

    /**
     * The resource id of the resource manager
     *
     * @var string
     */
    protected $resourceId = 'default';

    /**
     * Set namespace.
     *
     * The option Memcached::OPT_PREFIX_KEY will be used as the namespace.
     * It can't be longer than 128 characters.
     *
     * @see AdapterOptions::setNamespace()
     * @see MemcachedOptions::setPrefixKey()
     */
    public function setNamespace($namespace)
    {
        $namespace = (string) $namespace;

        if (128 < strlen($namespace)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a prefix key of no longer than 128 characters',
                __METHOD__
            ));
        }

        return parent::setNamespace($namespace);
    }

    /**
     * Set namespace separator
     *
     * @param  string $namespaceSeparator
     * @return MemcachedOptions
     */
    public function setNamespaceSeparator($namespaceSeparator)
    {
        $namespaceSeparator = (string) $namespaceSeparator;
        if ($this->namespaceSeparator !== $namespaceSeparator) {
            $this->triggerOptionEvent('namespace_separator', $namespaceSeparator);
            $this->namespaceSeparator = $namespaceSeparator;
        }
        return $this;
    }

    /**
     * Get namespace separator
     *
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }

    /**
     * A memcached resource to share
     *
     * @param null|MemcachedResource $memcachedResource
     * @return MemcachedOptions
     * @deprecated Please use the resource manager instead
     */
    public function setMemcachedResource(MemcachedResource $memcachedResource = null)
    {
        trigger_error(
            'This method is deprecated and will be removed in the feature'
            . ', please use the resource manager instead',
            E_USER_DEPRECATED
        );

        if ($memcachedResource !== null) {
            $this->triggerOptionEvent('memcached_resource', $memcachedResource);
            $resourceManager = $this->getResourceManager();
            $resourceId      = $this->getResourceId();
            $resourceManager->setResource($resourceId, $memcachedResource);
        }
        return $this;
    }

    /**
     * Get memcached resource to share
     *
     * @return MemcachedResource
     * @deprecated Please use the resource manager instead
     */
    public function getMemcachedResource()
    {
        trigger_error(
            'This method is deprecated and will be removed in the feature'
            . ', please use the resource manager instead',
            E_USER_DEPRECATED
        );

        return $this->resourceManager->getResource($this->getResourceId());
    }

    /**
     * Set the memcached resource manager to use
     *
     * @param null|MemcachedResourceManager $resourceManager
     * @return MemcachedOptions
     */
    public function setResourceManager(MemcachedResourceManager $resourceManager = null)
    {
        if ($this->resourceManager !== $resourceManager) {
            $this->triggerOptionEvent('resource_manager', $resourceManager);
            $this->resourceManager = $resourceManager;
        }
        return $this;
    }

    /**
     * Get the memcached resource manager
     *
     * @return MemcachedResourceManager
     */
    public function getResourceManager()
    {
        if (!$this->resourceManager) {
            $this->resourceManager = new MemcachedResourceManager();
        }
        return $this->resourceManager;
    }

    /**
     * Get the memcached resource id
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Set the memcached resource id
     *
     * @param string $resourceId
     * @return MemcachedOptions
     */
    public function setResourceId($resourceId)
    {
        $resourceId = (string) $resourceId;
        if ($this->resourceId !== $resourceId) {
            $this->triggerOptionEvent('resource_id', $resourceId);
            $this->resourceId = $resourceId;
        }
        return $this;
    }

    /**
     * Get the persistent id
     *
     * @return string
     */
    public function getPersistentId()
    {
        return $this->getResourceManager()->getPersistentId($this->getResourceId());
    }

    /**
     * Set the persistent id
     *
     * @param string $persistentId
     * @return MemcachedOptions
     */
    public function setPersistentId($persistentId)
    {
        $this->triggerOptionEvent('persistent_id', $persistentId);
        $this->getResourceManager()->setPersistentId($this->getPersistentId(), $persistentId);
        return $this;
    }

    /**
     * Add a server to the list
     *
     * @param string $host
     * @param int $port
     * @param int $weight
     * @return MemcachedOptions
     * @deprecated Please use the resource manager instead
     */
    public function addServer($host, $port = 11211, $weight = 0)
    {
        trigger_error(
            'This method is deprecated and will be removed in the feature'
            . ', please use the resource manager instead',
            E_USER_DEPRECATED
        );

        $this->getResourceManager()->addServer($this->getResourceId(), array(
            'host'   => $host,
            'port'   => $port,
            'weight' => $weight
        ));

        return $this;
    }

    /**
    * Set a list of memcached servers to add on initialize
    *
    * @param string|array $servers list of servers
    * @return MemcachedOptions
    * @throws Exception\InvalidArgumentException
    */
    public function setServers($servers)
    {
        $this->getResourceManager()->setServers($this->getResourceId(), $servers);
        return $this;
    }

    /**
     * Get Servers
     *
     * @return array
     */
    public function getServers()
    {
        return $this->getResourceManager()->getServers($this->getResourceId());
    }

    /**
    * Set libmemcached options
    *
    * @param array $libOptions
    * @return MemcachedOptions
    * @link http://php.net/manual/memcached.constants.php
    */
    public function setLibOptions(array $libOptions)
    {
        $this->getResourceManager()->setLibOptions($this->getResourceId(), $libOptions);
        return $this;
    }

    /**
     * Set libmemcached option
     *
     * @param string|int $key
     * @param mixed $value
     * @return MemcachedOptions
     * @link http://php.net/manual/memcached.constants.php
     * @deprecated Please use lib_options or the resource manager instead
     */
    public function setLibOption($key, $value)
    {
        trigger_error(
            'This method is deprecated and will be removed in the feature'
            . ', please use "lib_options" or the resource manager instead',
            E_USER_DEPRECATED
        );

        $this->getResourceManager()->setLibOption($this->getResourceId(), $key, $value);
        return $this;
    }

    /**
     * Get libmemcached options
     *
     * @return array
     * @link http://php.net/manual/memcached.constants.php
     */
    public function getLibOptions()
    {
        return $this->getResourceManager()->getLibOptions($this->getResourceId());
    }

    /**
    * Get libmemcached option
    *
    * @param string|int $key
    * @return mixed
    * @link http://php.net/manual/memcached.constants.php
    * @deprecated Please use lib_options or the resource manager instead
    */
    public function getLibOption($key)
    {
        trigger_error(
            'This method is deprecated and will be removed in the feature'
            . ', please use "lib_options" or the resource manager instead',
            E_USER_DEPRECATED
        );

        return $this->getResourceManager()->getLibOption($this->getResourceId(), $key);
    }
}
