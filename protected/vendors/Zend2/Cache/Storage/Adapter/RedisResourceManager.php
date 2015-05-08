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
use ReflectionClass;
use Traversable;
use Zend\Cache\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * This is a resource manager for redis
 */
class RedisResourceManager
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
     * Gets a redis resource
     *
     * @param string $id
     * @return RedisResource
     * @throws Exception\RuntimeException
     */
    public function getResource($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];
        if ($resource['resource'] instanceof RedisResource) {
            //in case new server was set then connect
            if (!$resource['initialized']) {
                $this->connect($resource);
            }
            $info = $resource['resource']->info();
            $resource['version'] = $info['redis_version'];
            return $resource['resource'];
        }

        $redis = new RedisResource();

        $resource['resource'] = $redis;
        $this->connect($resource);

        foreach ($resource['lib_options'] as $k => $v) {
            $redis->setOption($k, $v);
        }

        $info = $redis->info();
        $resource['version'] = $info['redis_version'];
        $this->resources[$id]['resource'] = $redis;
        return $redis;
    }

    /**
     * Connects to redis server
     *
     *
     * @param array & $resource
     *
     * @return null
     * @throws Exception\RuntimeException
     */
    protected function connect(array & $resource)
    {
        $server = $resource['server'];
        $redis  = $resource['resource'];
        if ($resource['persistent_id'] !== '') {
            //connect or reuse persistent connection
            $success = $redis->pconnect($server['host'], $server['port'], $server['timeout'], $server['persistend_id']);
        } elseif ($server['port']) {
            $success = $redis->connect($server['host'], $server['port'], $server['timeout']);
        } elseif ($server['timeout']) {
            //connect through unix domain socket
            $success = $redis->connect($server['host'], $server['timeout']);
        } else {
            $success = $redis->connect($server['host']);
        }

        if (!$success) {
            throw new Exception\RuntimeException('Could not estabilish connection with Redis instance');
        }

        $resource['initialized'] = true;
        if ($resource['password']) {
            $redis->auth($resource['password']);
        }
        $redis->select($resource['database']);
    }

    /**
     * Set a resource
     *
     * @param string $id
     * @param array|Traversable|RedisResource $resource
     * @return RedisResourceManager Fluent interface
     */
    public function setResource($id, $resource)
    {
        $id = (string) $id;
        //TODO: how to get back redis connection info from resource?
        $defaults = array(
            'persistent_id' => '',
            'lib_options'   => array(),
            'server'        => array(),
            'password'      => '',
            'database'      => 0,
            'resource'      => null,
            'initialized'   => false,
            'version'       => 0,
        );
        if (!$resource instanceof RedisResource) {
            if ($resource instanceof Traversable) {
                $resource = ArrayUtils::iteratorToArray($resource);
            } elseif (!is_array($resource)) {
                throw new Exception\InvalidArgumentException(
                    'Resource must be an instance of an array or Traversable'
                );
            }

            $resource = array_merge($defaults, $resource);
            // normalize and validate params
            $this->normalizePersistentId($resource['persistent_id']);
            $this->normalizeLibOptions($resource['lib_options']);
            $this->normalizeServer($resource['server']);
        } else {
            //there are two ways of determining if redis is already initialized
            //with connect function:
            //1) pinging server
            //2) checking undocummented property socket which is available only
            //after successfull connect
            $resource = array_merge($defaults, array(
                    'resource' => $resource,
                    'initialized' => isset($resource->socket),
                )
            );
        }
        $this->resources[$id] = $resource;
        return $this;
    }

    /**
     * Remove a resource
     *
     * @param string $id
     * @return RedisResourceManager Fluent interface
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
     * @return RedisResourceManager Fluent interface
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
        if ($resource instanceof RedisResource) {
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

        if ($resource instanceof RedisResource) {
            throw new Exception\RuntimeException(
                "Can't get persistent id of an instantiated redis resource"
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
     * Set Redis options
     *
     * @param string $id
     * @param array  $libOptions
     * @return RedisResourceManager Fluent interface
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

        $resource['lib_options'] = $libOptions;

        if ($resource['resource'] instanceof RedisResource) {
            $redis = & $resource['resource'];
            if (method_exists($redis, 'setOptions')) {
                $redis->setOptions($libOptions);
            } else {
                foreach ($libOptions as $key => $value) {
                    $redis->setOption($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Get Redis options
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

        if ($resource instanceof RedisResource) {
            $libOptions = array();
            $reflection = new ReflectionClass('Redis');
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
     * Set one Redis option
     *
     * @param string     $id
     * @param string|int $key
     * @param mixed      $value
     * @return RedisResourceManager Fluent interface
     */
    public function setLibOption($id, $key, $value)
    {
        return $this->setLibOptions($id, array($key => $value));
    }

    /**
     * Get one Redis option
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

        if ($resource instanceof RedisResource) {
            return $resource->getOption($key);
        }

        return isset($resource['lib_options'][$key]) ? $resource['lib_options'][$key] : null;
    }

    /**
     * Normalize Redis options
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
            $const = 'Redis::OPT_' . str_replace(array(' ', '-'), '_', strtoupper($key));
            if (!defined($const)) {
                throw new Exception\InvalidArgumentException("Unknown redis option '{$key}' ({$const})");
            }
            $key = constant($const);
        } else {
            $key = (int) $key;
        }
    }

    /**
     * Set server
     *
     * Server can be described as follows:
     * - URI:   /path/to/sock.sock
     * - Assoc: array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     * - List:  array(<host>[, <port>, [, <timeout>]])
     *
     * @param string       $id
     * @param string|array $server
     * @return RedisResourceManager
     */
    public function setServer($id, $server)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, array(
                'server' => $server
            ));
        }

        $this->normalizeServer($server);

        $resource = & $this->resources[$id];
        if ($resource['resource'] instanceof RedisResource) {
            $this->setResource($id, array('server' => $server));
        } else {
            $resource['server'] = $server;
        }
        return $this;
    }

    /**
     * Get server
     * @param string $id
     * @throws Exception\RuntimeException
     * @return array array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     */
    public function getServer($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];
        return $resource['server'];
    }

    /**
     * Set redis password
     *
     * @param string $id
     * @param string $password
     * @return RedisResource
     */
    public function setPassword($id, $password)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, array(
                'password' => $password,
            ));
        }

        $resource = & $this->resources[$id];
        $resource['password']    = $password;
        $resource['initialized'] = false;
        return $this;
    }

    /**
     * Get redis resource password
     *
     * @param string $id
     * @return string
     */
    public function getPassword($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];
        return $resource['password'];
    }

    /**
     * Set redis database number
     *
     * @param string $id
     * @param int $database
     * @return RedisResource
     */
    public function setDatabase($id, $database)
    {
        if (!$this->hasResource($id)) {
            return $this->setResource($id, array(
                'database' => (int) $database,
            ));
        }

        $resource = & $this->resources[$id];
        $resource['database']    = $database;
        $resource['initialized'] = false;
        return $this;
    }

    /**
     * Get redis resource database
     *
     * @param string $id
     * @return string
     */
    public function getDatabase($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];
        return $resource['database'];
    }

    /**
     * Get redis server version
     *
     * @deprecated 2.2.2 Use getMajorVersion instead
     *
     * @param string $id
     * @return int
     * @throws Exception\RuntimeException
     */
    public function getMayorVersion($id)
    {
        return $this->getMajorVersion($id);
    }

    /**
     * Get redis server version
     *
     * @param string $id
     * @return int
     * @throws Exception\RuntimeException
     */
    public function getMajorVersion($id)
    {
        if (!$this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = & $this->resources[$id];
        return (int) $resource['version'];
    }

    /**
     * Normalize one server into the following format:
     * array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     *
     * @param string|array $server
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeServer(& $server)
    {
        $host    = null;
        $port    = null;
        $timeout = 0;
        // convert a single server into an array
        if ($server instanceof Traversable) {
            $server = ArrayUtils::iteratorToArray($server);
        }

        if (is_array($server)) {
            // array(<host>[, <port>[, <timeout>]])
            if (isset($server[0])) {
                $host    = (string) $server[0];
                $port    = isset($server[1]) ? (int) $server[1] : $port;
                $timeout = isset($server[2]) ? (int) $server[2] : $timeout;
            }

            // array('host' => <host>[, 'port' => <port>, ['timeout' => <timeout>]])
            if (!isset($server[0]) && isset($server['host'])) {
                $host    = (string) $server['host'];
                $port    = isset($server['port'])    ? (int) $server['port']    : $port;
                $timeout = isset($server['timeout']) ? (int) $server['timeout'] : $timeout;
            }

        } else {
            // parse server from URI host{:?port}
            $server = trim($server);
            if (!strpos($server, '/') === 0) {
                //non unix domain socket connection
                $server = parse_url($server);
            } else {
                $server = array('host' => $server);
            }
            if (!$server) {
                throw new Exception\InvalidArgumentException("Invalid server given");
            }

            $host    = $server['host'];
            $port    = isset($server['port'])    ? (int) $server['port']    : $port;
            $timeout = isset($server['timeout']) ? (int) $server['timeout'] : $timeout;
        }

        if (!$host) {
            throw new Exception\InvalidArgumentException('Missing required server host');
        }

        $server = array(
            'host'    => $host,
            'port'    => $port,
            'timeout' => $timeout,
        );
    }
}
