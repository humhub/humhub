<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

use ArrayIterator;
use Iterator;
use Traversable;
use Zend\Session\ManagerInterface as Manager;
use Zend\Session\Storage\StorageInterface as Storage;
use Zend\Stdlib\ArrayObject;

/**
 * Session storage container
 *
 * Allows for interacting with session storage in isolated containers, which
 * may have their own expiries, or even expiries per key in the container.
 * Additionally, expiries may be absolute TTLs or measured in "hops", which
 * are based on how many times the key or container were accessed.
 */
abstract class AbstractContainer extends ArrayObject
{
    /**
     * Container name
     *
     * @var string
     */
    protected $name;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Default manager class to use if no manager has been provided
     *
     * @var string
     */
    protected static $managerDefaultClass = 'Zend\\Session\\SessionManager';

    /**
     * Default manager to use when instantiating a container without providing a ManagerInterface
     *
     * @var Manager
     */
    protected static $defaultManager;

    /**
     * Constructor
     *
     * Provide a name ('Default' if none provided) and a ManagerInterface instance.
     *
     * @param  null|string                        $name
     * @param  Manager                            $manager
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name = 'Default', Manager $manager = null)
    {
        if (!preg_match('/^[a-z][a-z0-9_\\\]+$/i', $name)) {
            throw new Exception\InvalidArgumentException(
                'Name passed to container is invalid; must consist of alphanumerics, backslashes and underscores only'
            );
        }
        $this->name = $name;
        $this->setManager($manager);

        // Create namespace
        parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);

        // Start session
        $this->getManager()->start();
    }

    /**
     * Set the default ManagerInterface instance to use when none provided to constructor
     *
     * @param  Manager $manager
     * @return void
     */
    public static function setDefaultManager(Manager $manager = null)
    {
        static::$defaultManager = $manager;
    }

    /**
     * Get the default ManagerInterface instance
     *
     * If none provided, instantiates one of type {@link $managerDefaultClass}
     *
     * @return Manager
     * @throws Exception\InvalidArgumentException if invalid manager default class provided
     */
    public static function getDefaultManager()
    {
        if (null === static::$defaultManager) {
            $manager = new static::$managerDefaultClass();
            if (!$manager instanceof Manager) {
                throw new Exception\InvalidArgumentException(
                    'Invalid default manager type provided; must implement ManagerInterface'
                );
            }
            static::$defaultManager = $manager;
        }

        return static::$defaultManager;
    }

    /**
     * Get container name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set session manager
     *
     * @param  null|Manager                       $manager
     * @return Container
     * @throws Exception\InvalidArgumentException
     */
    protected function setManager(Manager $manager = null)
    {
        if (null === $manager) {
            $manager = static::getDefaultManager();
            if (!$manager instanceof Manager) {
                throw new Exception\InvalidArgumentException(
                    'Manager provided is invalid; must implement ManagerInterface'
                );
            }
        }
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager instance
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Get session storage object
     *
     * Proxies to ManagerInterface::getStorage()
     *
     * @return Storage
     */
    protected function getStorage()
    {
        return $this->getManager()->getStorage();
    }

    /**
     * Create a new container object on which to act
     *
     * @return ArrayObject
     */
    protected function createContainer()
    {
        return new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Verify container namespace
     *
     * Checks to see if a container exists within the Storage object already.
     * If not, one is created; if so, checks to see if it's an ArrayObject.
     * If not, it raises an exception; otherwise, it returns the Storage
     * object.
     *
     * @param  bool                       $createContainer Whether or not to create the container for the namespace
     * @return Storage|null               Returns null only if $createContainer is false
     * @throws Exception\RuntimeException
     */
    protected function verifyNamespace($createContainer = true)
    {
        $storage = $this->getStorage();
        $name    = $this->getName();
        if (!isset($storage[$name])) {
            if (!$createContainer) {
                return;
            }
            $storage[$name] = $this->createContainer();
        }
        if (!is_array($storage[$name]) && !$storage[$name] instanceof Traversable) {
            throw new Exception\RuntimeException('Container cannot write to storage due to type mismatch');
        }

        return $storage;
    }

    /**
     * Determine whether a given key needs to be expired
     *
     * Returns true if the key has expired, false otherwise.
     *
     * @param  null|string $key
     * @return bool
     */
    protected function expireKeys($key = null)
    {
        $storage = $this->verifyNamespace();
        $name    = $this->getName();

        // Return early if key not found
        if ((null !== $key) && !isset($storage[$name][$key])) {
            return true;
        }

        if ($this->expireByExpiryTime($storage, $name, $key)) {
            return true;
        }

        if ($this->expireByHops($storage, $name, $key)) {
            return true;
        }

        return false;
    }

    /**
     * Expire a key by expiry time
     *
     * Checks to see if the entire container has expired based on TTL setting,
     * or the individual key.
     *
     * @param  Storage $storage
     * @param  string  $name    Container name
     * @param  string  $key     Key in container to check
     * @return bool
     */
    protected function expireByExpiryTime(Storage $storage, $name, $key)
    {
        $metadata = $storage->getMetadata($name);

        // Global container expiry
        if (is_array($metadata)
            && isset($metadata['EXPIRE'])
            && ($_SERVER['REQUEST_TIME'] > $metadata['EXPIRE'])
        ) {
            unset($metadata['EXPIRE']);
            $storage->setMetadata($name, $metadata, true);
            $storage[$name] = $this->createContainer();

            return true;
        }

        // Expire individual key
        if ((null !== $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_KEYS'])
            && isset($metadata['EXPIRE_KEYS'][$key])
            && ($_SERVER['REQUEST_TIME'] > $metadata['EXPIRE_KEYS'][$key])
        ) {
            unset($metadata['EXPIRE_KEYS'][$key]);
            $storage->setMetadata($name, $metadata, true);
            unset($storage[$name][$key]);

            return true;
        }

        // Find any keys that have expired
        if ((null === $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_KEYS'])
        ) {
            foreach (array_keys($metadata['EXPIRE_KEYS']) as $key) {
                if ($_SERVER['REQUEST_TIME'] > $metadata['EXPIRE_KEYS'][$key]) {
                    unset($metadata['EXPIRE_KEYS'][$key]);
                    if (isset($storage[$name][$key])) {
                        unset($storage[$name][$key]);
                    }
                }
            }
            $storage->setMetadata($name, $metadata, true);

            return true;
        }

        return false;
    }

    /**
     * Expire key by session hops
     *
     * Determines whether the container or an individual key within it has
     * expired based on session hops
     *
     * @param  Storage $storage
     * @param  string  $name
     * @param  string  $key
     * @return bool
     */
    protected function expireByHops(Storage $storage, $name, $key)
    {
        $ts       = $storage->getRequestAccessTime();
        $metadata = $storage->getMetadata($name);

        // Global container expiry
        if (is_array($metadata)
            && isset($metadata['EXPIRE_HOPS'])
            && ($ts > $metadata['EXPIRE_HOPS']['ts'])
        ) {
            $metadata['EXPIRE_HOPS']['hops']--;
            if (-1 === $metadata['EXPIRE_HOPS']['hops']) {
                unset($metadata['EXPIRE_HOPS']);
                $storage->setMetadata($name, $metadata, true);
                $storage[$name] = $this->createContainer();

                return true;
            }
            $metadata['EXPIRE_HOPS']['ts'] = $ts;
            $storage->setMetadata($name, $metadata, true);

            return false;
        }

        // Single key expiry
        if ((null !== $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_HOPS_KEYS'])
            && isset($metadata['EXPIRE_HOPS_KEYS'][$key])
            && ($ts > $metadata['EXPIRE_HOPS_KEYS'][$key]['ts'])
        ) {
            $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']--;
            if (-1 === $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']) {
                unset($metadata['EXPIRE_HOPS_KEYS'][$key]);
                $storage->setMetadata($name, $metadata, true);
                unset($storage[$name][$key]);

                return true;
            }
            $metadata['EXPIRE_HOPS_KEYS'][$key]['ts'] = $ts;
            $storage->setMetadata($name, $metadata, true);

            return false;
        }

        // Find all expired keys
        if ((null === $key)
            && is_array($metadata)
            && isset($metadata['EXPIRE_HOPS_KEYS'])
        ) {
            foreach (array_keys($metadata['EXPIRE_HOPS_KEYS']) as $key) {
                if ($ts > $metadata['EXPIRE_HOPS_KEYS'][$key]['ts']) {
                    $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']--;
                    if (-1 === $metadata['EXPIRE_HOPS_KEYS'][$key]['hops']) {
                        unset($metadata['EXPIRE_HOPS_KEYS'][$key]);
                        $storage->setMetadata($name, $metadata, true);
                        unset($storage[$name][$key]);
                        continue;
                    }
                    $metadata['EXPIRE_HOPS_KEYS'][$key]['ts'] = $ts;
                }
            }
            $storage->setMetadata($name, $metadata, true);

            return false;
        }

        return false;
    }

    /**
     * Store a value within the container
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->expireKeys($key);
        $storage = $this->verifyNamespace();
        $name    = $this->getName();
        $storage[$name][$key] = $value;
    }

    /**
     * Determine if the key exists
     *
     * @param  string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        // If no container exists, we can't inspect it
        if (null === ($storage = $this->verifyNamespace(false))) {
            return false;
        }
        $name = $this->getName();

        // Return early if the key isn't set
        if (!isset($storage[$name][$key])) {
            return false;
        }

        $expired = $this->expireKeys($key);

        return !$expired;
    }

    /**
     * Retrieve a specific key in the container
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (!$this->offsetExists($key)) {
            return null;
        }
        $storage = $this->getStorage();
        $name = $this->getName();

        return $storage[$name][$key];
    }

    /**
     * Unset a single key in the container
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        if (!$this->offsetExists($key)) {
            return;
        }
        $storage = $this->getStorage();
        $name    = $this->getName();
        unset($storage[$name][$key]);
    }

    /**
     * Exchange the current array with another array or object.
     *
     * @param  array|object $input
     * @return array        Returns the old array
     * @see ArrayObject::exchangeArray()
     */
    public function exchangeArray($input)
    {
        // handle arrayobject, iterators and the like:
        if (is_object($input) && ($input instanceof ArrayObject || $input instanceof \ArrayObject)) {
            $input = $input->getArrayCopy();
        }
        if (!is_array($input)) {
            $input = (array) $input;
        }

        $storage = $this->verifyNamespace();
        $name    = $this->getName();

        $old = $storage[$name];
        $storage[$name] = $input;
        if ($old instanceof ArrayObject) {
            return $old->getArrayCopy();
        }

        return $old;
    }

    /**
     * Iterate over session container
     *
     * @return Iterator
     */
    public function getIterator()
    {
        $this->expireKeys();
        $storage   = $this->getStorage();
        $container = $storage[$this->getName()];

        if ($container instanceof Traversable) {
            return $container;
        }

        return new ArrayIterator($container);
    }

    /**
     * Set expiration TTL
     *
     * Set the TTL for the entire container, a single key, or a set of keys.
     *
     * @param  int                                $ttl  TTL in seconds
     * @param  string|array|null                  $vars
     * @return Container
     * @throws Exception\InvalidArgumentException
     */
    public function setExpirationSeconds($ttl, $vars = null)
    {
        $storage = $this->getStorage();
        $ts      = $_SERVER['REQUEST_TIME'] + $ttl;
        if (is_scalar($vars) && null !== $vars) {
            $vars = (array) $vars;
        }

        if (null === $vars) {
            $this->expireKeys(); // first we need to expire global key, since it can already be expired
            $data = array('EXPIRE' => $ts);
        } elseif (is_array($vars)) {
            // Cannot pass "$this" to a lambda
            $container = $this;

            // Filter out any items not in our container
            $expires   = array_filter($vars, function ($value) use ($container) {
                return $container->offsetExists($value);
            });

            // Map item keys => timestamp
            $expires   = array_flip($expires);
            $expires   = array_map(function ($value) use ($ts) {
                return $ts;
            }, $expires);

            // Create metadata array to merge in
            $data = array('EXPIRE_KEYS' => $expires);
        } else {
            throw new Exception\InvalidArgumentException(
                'Unknown data provided as second argument to ' . __METHOD__
            );
        }

        $storage->setMetadata(
            $this->getName(),
            $data
        );

        return $this;
    }

    /**
     * Set expiration hops for the container, a single key, or set of keys
     *
     * @param  int                                $hops
     * @param  null|string|array                  $vars
     * @throws Exception\InvalidArgumentException
     * @return Container
     */
    public function setExpirationHops($hops, $vars = null)
    {
        $storage = $this->getStorage();
        $ts      = $storage->getRequestAccessTime();

        if (is_scalar($vars) && (null !== $vars)) {
            $vars = (array) $vars;
        }

        if (null === $vars) {
            $this->expireKeys(); // first we need to expire global key, since it can already be expired
            $data = array('EXPIRE_HOPS' => array('hops' => $hops, 'ts' => $ts));
        } elseif (is_array($vars)) {
            // Cannot pass "$this" to a lambda
            $container = $this;

            // FilterInterface out any items not in our container
            $expires   = array_filter($vars, function ($value) use ($container) {
                return $container->offsetExists($value);
            });

            // Map item keys => timestamp
            $expires   = array_flip($expires);
            $expires   = array_map(function ($value) use ($hops, $ts) {
                return array('hops' => $hops, 'ts' => $ts);
            }, $expires);

            // Create metadata array to merge in
            $data = array('EXPIRE_HOPS_KEYS' => $expires);
        } else {
            throw new Exception\InvalidArgumentException(
                'Unknown data provided as second argument to ' . __METHOD__
            );
        }

        $storage->setMetadata(
            $this->getName(),
            $data
        );

        return $this;
    }
}
