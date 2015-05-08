<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Storage;

use ArrayIterator;
use IteratorAggregate;
use Zend\Session\Exception;

/**
 * Session storage in $_SESSION
 *
 * Replaces the $_SESSION superglobal with an ArrayObject that allows for
 * property access, metadata storage, locking, and immutability.
 */
abstract class AbstractSessionArrayStorage implements
    IteratorAggregate,
    StorageInterface,
    StorageInitializationInterface
{
    /**
     * Constructor
     *
     * @param array|null $input
     */
    public function __construct($input = null)
    {
        // this is here for B.C.
        $this->init($input);
    }


    /**
     * Initialize Storage
     *
     * @param  array $input
     * @return void
     */
    public function init($input = null)
    {
        if ((null === $input) && isset($_SESSION)) {
            $input = $_SESSION;
            if (is_object($input) && !$_SESSION instanceof \ArrayObject) {
                $input = (array) $input;
            }
        } elseif (null === $input) {
            $input = array();
        }
        $_SESSION = $input;
        $this->setRequestAccessTime(microtime(true));
    }

    /**
     * Get Offset
     *
     * @param  mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Set Offset
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    /**
     * Isset Offset
     *
     * @param  mixed   $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset Offset
     *
     * @param  mixed $key
     * @return void
     */
    public function __unset($key)
    {
        return $this->offsetUnset($key);
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        return ;
    }

    /**
     * Offset Exists
     *
     * @param  mixed   $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Offset Get
     *
     * @param  mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }

    /**
     * Offset Set
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Offset Unset
     *
     * @param  mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Count
     *
     * @return int
     */
    public function count()
    {
        return count($_SESSION);
    }

    /**
     * Seralize
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($_SESSION);
    }

    /**
     * Unserialize
     *
     * @param  string $session
     * @return mixed
     */
    public function unserialize($session)
    {
        return unserialize($session);
    }

    /**
     * Get Iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($_SESSION);
    }

    /**
     * Load session object from an existing array
     *
     * Ensures $_SESSION is set to an instance of the object when complete.
     *
     * @param  array          $array
     * @return SessionStorage
     */
    public function fromArray(array $array)
    {
        $ts = $this->getRequestAccessTime();
        $_SESSION = $array;
        $this->setRequestAccessTime($ts);

        return $this;
    }

    /**
     * Mark object as isImmutable
     *
     * @return SessionStorage
     */
    public function markImmutable()
    {
        $_SESSION['_IMMUTABLE'] = true;

        return $this;
    }

    /**
     * Determine if this object is isImmutable
     *
     * @return bool
     */
    public function isImmutable()
    {
        return (isset($_SESSION['_IMMUTABLE']) && $_SESSION['_IMMUTABLE']);
    }

    /**
     * Lock this storage instance, or a key within it
     *
     * @param  null|int|string $key
     * @return ArrayStorage
     */
    public function lock($key = null)
    {
        if (null === $key) {
            $this->setMetadata('_READONLY', true);

            return $this;
        }
        if (isset($_SESSION[$key])) {
            $this->setMetadata('_LOCKS', array($key => true));
        }

        return $this;
    }

    /**
     * Is the object or key marked as locked?
     *
     * @param  null|int|string $key
     * @return bool
     */
    public function isLocked($key = null)
    {
        if ($this->isImmutable()) {
            // isImmutable trumps all
            return true;
        }

        if (null === $key) {
            // testing for global lock
            return $this->getMetadata('_READONLY');
        }

        $locks    = $this->getMetadata('_LOCKS');
        $readOnly = $this->getMetadata('_READONLY');

        if ($readOnly && !$locks) {
            // global lock in play; all keys are locked
            return true;
        }
        if ($readOnly && $locks) {
            return array_key_exists($key, $locks);
        }

        // test for individual locks
        if (!$locks) {
            return false;
        }

        return array_key_exists($key, $locks);
    }

    /**
     * Unlock an object or key marked as locked
     *
     * @param  null|int|string $key
     * @return ArrayStorage
     */
    public function unlock($key = null)
    {
        if (null === $key) {
            // Unlock everything
            $this->setMetadata('_READONLY', false);
            $this->setMetadata('_LOCKS', false);

            return $this;
        }

        $locks = $this->getMetadata('_LOCKS');
        if (!$locks) {
            if (!$this->getMetadata('_READONLY')) {
                return $this;
            }
            $array = $this->toArray();
            $keys  = array_keys($array);
            $locks = array_flip($keys);
            unset($array, $keys);
        }

        if (array_key_exists($key, $locks)) {
            unset($locks[$key]);
            $this->setMetadata('_LOCKS', $locks, true);
        }

        return $this;
    }

    /**
     * Set storage metadata
     *
     * Metadata is used to store information about the data being stored in the
     * object. Some example use cases include:
     * - Setting expiry data
     * - Maintaining access counts
     * - localizing session storage
     * - etc.
     *
     * @param  string                     $key
     * @param  mixed                      $value
     * @param  bool                       $overwriteArray Whether to overwrite or merge array values; by default, merges
     * @return ArrayStorage
     * @throws Exception\RuntimeException
     */
    public function setMetadata($key, $value, $overwriteArray = false)
    {
        if ($this->isImmutable()) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot set key "%s" as storage is marked isImmutable', $key
            ));
        }

        if (!isset($_SESSION['__ZF'])) {
            $_SESSION['__ZF'] = array();
        }
        if (isset($_SESSION['__ZF'][$key]) && is_array($value)) {
            if ($overwriteArray) {
                $_SESSION['__ZF'][$key] = $value;
            } else {
                $_SESSION['__ZF'][$key] = array_replace_recursive($_SESSION['__ZF'][$key], $value);
            }
        } else {
            if ((null === $value) && isset($_SESSION['__ZF'][$key])) {
                $array = $_SESSION['__ZF'];
                unset($array[$key]);
                $_SESSION['__ZF'] = $array;
                unset($array);
            } elseif (null !== $value) {
                $_SESSION['__ZF'][$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Retrieve metadata for the storage object or a specific metadata key
     *
     * Returns false if no metadata stored, or no metadata exists for the given
     * key.
     *
     * @param  null|int|string $key
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        if (!isset($_SESSION['__ZF'])) {
            return false;
        }

        if (null === $key) {
            return $_SESSION['__ZF'];
        }

        if (!array_key_exists($key, $_SESSION['__ZF'])) {
            return false;
        }

        return $_SESSION['__ZF'][$key];
    }

    /**
     * Clear the storage object or a subkey of the object
     *
     * @param  null|int|string            $key
     * @return ArrayStorage
     * @throws Exception\RuntimeException
     */
    public function clear($key = null)
    {
        if ($this->isImmutable()) {
            throw new Exception\RuntimeException('Cannot clear storage as it is marked immutable');
        }
        if (null === $key) {
            $this->fromArray(array());

            return $this;
        }

        if (!isset($_SESSION[$key])) {
            return $this;
        }

        // Clear key data
        unset($_SESSION[$key]);

        // Clear key metadata
        $this->setMetadata($key, null)
             ->unlock($key);

        return $this;
    }

    /**
     * Retrieve the request access time
     *
     * @return float
     */
    public function getRequestAccessTime()
    {
        return $this->getMetadata('_REQUEST_ACCESS_TIME');
    }

    /**
     * Set the request access time
     *
     * @param  float        $time
     * @return ArrayStorage
     */
    protected function setRequestAccessTime($time)
    {
        $this->setMetadata('_REQUEST_ACCESS_TIME', $time);

        return $this;
    }

    /**
     * Cast the object to an array
     *
     * @param  bool $metaData Whether to include metadata
     * @return array
     */
    public function toArray($metaData = false)
    {
        if (isset($_SESSION)) {
            $values = $_SESSION;
        } else {
            $values = array();
        }

        if ($metaData) {
            return $values;
        }

        if (isset($values['__ZF'])) {
            unset($values['__ZF']);
        }

        return $values;
    }
}
