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
use ReflectionClass;
use Traversable;
use Zend\Cache\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * This is a resource manager for memcached
 */
class MemcachedResourceManager
{

    /**
     * Registered resources
     *
     * @var array
     */
    protected $resources = array();

    /**
     * Check if a resource exists
     *
     * @param string $id
     * @return bool
     */
    public function hasResource($id)
    {
        return isset($this->resources[$id]);
    }

    /**
     * Gets a memcached resource
     *
     * @param string $id
     * @return MemcachedResource
     * @throws Exception\RuntimeException
     */
    public function getResource($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = $this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            return $resource;
        }

        if ($resource['persistent_id'] !== '') {
            $memc = new MemcachedResource($resource['persistent_id']);
        } else {
            $memc = new MemcachedResource();
        }

        if (method_exists($memc, 'setOptions')) {
            $memc->setOptions($resource['lib_options']);
        } else {
            foreach ($resource['lib_options'] as $k => $v) {
                $memc->setOption($k, $v);
            }
        }

        // merge and add servers (with persistence id servers could be added already)
        $servers = array_udiff($resource['servers'], $memc->getServerList(), array($this, 'compareServers'));
        if ($servers) {
            $memc->addServers($servers);
        }

        // buffer and return
        $this->resources[$id] = $memc;
        return $memc;
    }

    /**
     * Set a resource
     *
     * @param string $id
     * @param array|Traversable|MemcachedResource $resource
     * @return MemcachedResourceManager Fluent interface
     */
    public function setResource($id, $resource)
    {
        $id = (string) $id;

        if (!($resource instanceof MemcachedResource)) {
            if ($resource instanceof Traversable) {
                $resource = ArrayUtils::iteratorToArray($resource);
            } elseif (!is_array($resource)) {
                throw new Exception\InvalidArgumentException(
                    'Resource must be an instance of Memcached or an array or Traversable'
                );
            }

            $resource = array_merge(array(
                'persistent_id' => '',
                'lib_options'   => array(),
                'servers'       => array(),
            ), $resource);

            // normalize and validate params
            $this->normalizePersistentId($resource['persistent_id']);
            $this->normalizeLibOptions($resource['lib_options']);
            $this->normalizeServers($resource['servers']);
        }

        $this->resources[$id] = $resource;
        return $this;
    }

    /**
     * Remove a resource
     *
     * @param string $id
     * @return MemcachedResourceManager Fluent interface
     */
    public function removeResource($id)
    {
        unset($this->resources[$id]);
        return $this;
    }

    /**
     * Set the persistent id
     *
     * @param string $id
     * @param string $persistentId
     * @return MemcachedResourceManager Fluent interface
     * @throws Exception\RuntimeException
     */
    public function setPersistentId($id, $persistentId)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, array(
                'persistent_id' => $persistentId
            ));
        }

        $resource = & $this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            throw new Exception\RuntimeException(
                "Can't change persistent id of resource {$id} after instanziation"
            );
        }

        $this->normalizePersistentId($persistentId);
        $resource['persistent_id'] = $persistentId;

        return $this;
    }

    /**
     * Get the persistent id
     *
     * @param string $id
     * @return string
     * @throws Exception\RuntimeException
     */
    public function getPersistentId($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];

        if ($resource instanceof MemcachedResource) {
            throw new Exception\RuntimeException(
                "Can't get persistent id of an instantiated memcached resource"
            );
        }

        return $resource['persistent_id'];
    }

    /**
     * Normalize the persistent id
     *
     * @param string $persistentId
     */
    protected function normalizePersistentId(& $persistentId)
    {
        $persistentId = (string) $persistentId;
    }

    /**
     * Set Libmemcached options
     *
     * @param string $id
     * @param array  $libOptions
     * @return MemcachedResourceManager Fluent interface
     */
    public function setLibOptions($id, array $libOptions)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, array(
                'lib_options' => $libOptions
            ));
        }

        $this->normalizeLibOptions($libOptions);

        $resource = & $this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            if (method_exists($resource, 'setOptions')) {
                $resource->setOptions($libOptions);
            } else {
                foreach ($libOptions as $key => $value) {
                    $resource->setOption($key, $value);
                }
            }
        } else {
            $resource['lib_options'] = $libOptions;
        }

        return $this;
    }

    /**
     * Get Libmemcached options
     *
     * @param string $id
     * @return array
     * @throws Exception\RuntimeException
     */
    public function getLibOptions($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];

        if ($resource instanceof MemcachedResource) {
            $libOptions = array();
            $reflection = new ReflectionClass('Memcached');
            $constants  = $reflection->getConstants();
            foreach ($constants as $constName => $constValue) {
                if (substr($constName, 0, 4) == 'OPT_') {
                    $libOptions[$constValue] = $resource->getOption($constValue);
                }
            }
            return $libOptions;
        }
        return $resource['lib_options'];
    }

    /**
     * Set one Libmemcached option
     *
     * @param string     $id
     * @param string|int $key
     * @param mixed      $value
     * @return MemcachedResourceManager Fluent interface
     */
    public function setLibOption($id, $key, $value)
    {
        return $this->setLibOptions($id, array($key => $value));
    }

    /**
     * Get one Libmemcached option
     *
     * @param string     $id
     * @param string|int $key
     * @return mixed
     * @throws Exception\RuntimeException
     */
    public function getLibOption($id, $key)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $this->normalizeLibOptionKey($key);
        $resource   = & $this->resources[$id];

        if ($resource instanceof MemcachedResource) {
            return $resource->getOption($key);
        }

        return isset($resource['lib_options'][$key]) ? $resource['lib_options'][$key] : null;
    }

    /**
     * Normalize libmemcached options
     *
     * @param array|Traversable $libOptions
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeLibOptions(& $libOptions)
    {
        if (!is_array($libOptions) && !($libOptions instanceof Traversable)) {
            throw new Exception\InvalidArgumentException(
                "Lib-Options must be an array or an instance of Traversable"
            );
        }

        $result = array();
        foreach ($libOptions as $key => $value) {
            $this->normalizeLibOptionKey($key);
            $result[$key] = $value;
        }

        $libOptions = $result;
    }

    /**
     * Convert option name into it's constant value
     *
     * @param string|int $key
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeLibOptionKey(& $key)
    {
        // convert option name into it's constant value
        if (is_string($key)) {
            $const = 'Memcached::OPT_' . str_replace(array(' ', '-'), '_', strtoupper($key));
            if (!defined($const)) {
                throw new Exception\InvalidArgumentException("Unknown libmemcached option '{$key}' ({$const})");
            }
            $key = constant($const);
        } else {
            $key = (int) $key;
        }
    }

    /**
     * Set servers
     *
     * $servers can be an array list or a comma separated list of servers.
     * One server in the list can be descripted as follows:
     * - URI:   [tcp://]<host>[:<port>][?weight=<weight>]
     * - Assoc: array('host' => <host>[, 'port' => <port>][, 'weight' => <weight>])
     * - List:  array(<host>[, <port>][, <weight>])
     *
     * @param string       $id
     * @param string|array $servers
     * @return MemcachedResourceManager
     */
    public function setServers($id, $servers)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, array(
                'servers' => $servers
            ));
        }

        $this->normalizeServers($servers);

        $resource = & $this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            // don't add servers twice
            $servers = array_udiff($servers, $resource->getServerList(), array($this, 'compareServers'));
            if ($servers) {
                $resource->addServers($servers);
            }
        } else {
            $resource['servers'] = $servers;
        }

        return $this;
    }

    /**
     * Get servers
     * @param string $id
     * @throws Exception\RuntimeException
     * @return array array('host' => <host>, 'port' => <port>, 'weight' => <weight>)
     */
    public function getServers($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];

        if ($resource instanceof MemcachedResource) {
            return $resource->getServerList();
        }
        return $resource['servers'];
    }

    /**
     * Add servers
     *
     * @param string       $id
     * @param string|array $servers
     * @return MemcachedResourceManager
     */
    public function addServers($id, $servers)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, array(
                'servers' => $servers
            ));
        }

        $this->normalizeServers($servers);

        $resource = & $this->resources[$id];
        if ($resource instanceof MemcachedResource) {
            // don't add servers twice
            $servers = array_udiff($servers, $resource->getServerList(), array($this, 'compareServers'));
            if ($servers) {
                $resource->addServers($servers);
            }
        } else {
            // don't add servers twice
            $resource['servers'] = array_merge(
                $resource['servers'],
                array_udiff($servers, $resource['servers'], array($this, 'compareServers'))
            );
        }

        return $this;
    }

    /**
     * Add one server
     *
     * @param string       $id
     * @param string|array $server
     * @return MemcachedResourceManager
     */
    public function addServer($id, $server)
    {
        return $this->addServers($id, array($server));
    }

    /**
     * Normalize a list of servers into the following format:
     * array(array('host' => <host>, 'port' => <port>, 'weight' => <weight>)[, ...])
     *
     * @param string|array $servers
     */
    protected function normalizeServers(& $servers)
    {
        if (!is_array($servers) && !$servers instanceof Traversable) {
            // Convert string into a list of servers
            $servers = explode(',', $servers);
        }

        $result = array();
        foreach ($servers as $server) {
            $this->normalizeServer($server);
            $result[$server['host'] . ':' . $server['port']] = $server;
        }

        $servers = array_values($result);
    }

    /**
     * Normalize one server into the following format:
     * array('host' => <host>, 'port' => <port>, 'weight' => <weight>)
     *
     * @param string|array $server
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeServer(& $server)
    {
        $host   = null;
        $port   = 11211;
        $weight = 0;

        // convert a single server into an array
        if ($server instanceof Traversable) {
            $server = ArrayUtils::iteratorToArray($server);
        }

        if (is_array($server)) {
            // array(<host>[, <port>[, <weight>]])
            if (isset($server[0])) {
                $host   = (string) $server[0];
                $port   = isset($server[1]) ? (int) $server[1] : $port;
                $weight = isset($server[2]) ? (int) $server[2] : $weight;
            }

            // array('host' => <host>[, 'port' => <port>[, 'weight' => <weight>]])
            if (!isset($server[0]) && isset($server['host'])) {
                $host   = (string) $server['host'];
                $port   = isset($server['port'])   ? (int) $server['port']   : $port;
                $weight = isset($server['weight']) ? (int) $server['weight'] : $weight;
            }

        } else {
            // parse server from URI host{:?port}{?weight}
            $server = trim($server);
            if (strpos($server, '://') === false) {
                $server = 'tcp://' . $server;
            }

            $server = parse_url($server);
            if (!$server) {
                throw new Exception\InvalidArgumentException("Invalid server given");
            }

            $host = $server['host'];
            $port = isset($server['port']) ? (int) $server['port'] : $port;

            if (isset($server['query'])) {
                $query = null;
                parse_str($server['query'], $query);
                if (isset($query['weight'])) {
                    $weight = (int) $query['weight'];
                }
            }
        }

        if (!$host) {
            throw new Exception\InvalidArgumentException('Missing required server host');
        }

        $server = array(
            'host'   => $host,
            'port'   => $port,
            'weight' => $weight,
        );
    }

    /**
     * Compare 2 normalized server arrays
     * (Compares only the host and the port)
     *
     * @param array $serverA
     * @param array $serverB
     * @return int
     */
    protected function compareServers(array $serverA, array $serverB)
    {
        $keyA = $serverA['host'] . ':' . $serverA['port'];
        $keyB = $serverB['host'] . ':' . $serverB['port'];
        if ($keyA === $keyB) {
            return 0;
        }
        return $keyA > $keyB ? 1 : -1;
    }
}
