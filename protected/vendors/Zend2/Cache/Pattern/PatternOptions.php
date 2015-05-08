<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Pattern;

use Traversable;
use Zend\Cache\Exception;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\StorageInterface as Storage;
use Zend\Stdlib\AbstractOptions;

class PatternOptions extends AbstractOptions
{
    /**
     * Used by:
     * - ClassCache
     * - ObjectCache
     * @var bool
     */
    protected $cacheByDefault = true;

    /**
     * Used by:
     * - CallbackCache
     * - ClassCache
     * - ObjectCache
     * @var bool
     */
    protected $cacheOutput = true;

    /**
     * Used by:
     * - ClassCache
     * @var null|string
     */
    protected $class;

    /**
     * Used by:
     * - ClassCache
     * @var array
     */
    protected $classCacheMethods = array();

    /**
     * Used by:
     * - ClassCache
     * @var array
     */
    protected $classNonCacheMethods = array();

    /**
     * Used by:
     * - CaptureCache
     * @var false|int
     */
    protected $umask = false;

    /**
     * Used by:
     * - CaptureCache
     * @var false|int
     */
    protected $dirPermission = 0700;

    /**
     * Used by:
     * - CaptureCache
     * @var false|int
     */
    protected $filePermission = 0600;

    /**
     * Used by:
     * - CaptureCache
     * @var bool
     */
    protected $fileLocking = true;

    /**
     * Used by:
     * - CaptureCache
     * @var string
     */
    protected $indexFilename = 'index.html';

    /**
     * Used by:
     * - ObjectCache
     * @var null|object
     */
    protected $object;

    /**
     * Used by:
     * - ObjectCache
     * @var bool
     */
    protected $objectCacheMagicProperties = false;

    /**
     * Used by:
     * - ObjectCache
     * @var array
     */
    protected $objectCacheMethods = array();

    /**
     * Used by:
     * - ObjectCache
     * @var null|string
     */
    protected $objectKey;

    /**
     * Used by:
     * - ObjectCache
     * @var array
     */
    protected $objectNonCacheMethods = array('__tostring');

    /**
     * Used by:
     * - CaptureCache
     * @var null|string
     */
    protected $publicDir;

    /**
     * Used by:
     * - CallbackCache
     * - ClassCache
     * - ObjectCache
     * - OutputCache
     * @var null|Storage
     */
    protected $storage;

    /**
     * Constructor
     *
     * @param  array|Traversable|null $options
     * @return PatternOptions
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        // disable file/directory permissions by default on windows systems
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $this->filePermission = false;
            $this->dirPermission = false;
        }

        parent::__construct($options);
    }

    /**
     * Set flag indicating whether or not to cache by default
     *
     * Used by:
     * - ClassCache
     * - ObjectCache
     *
     * @param  bool $cacheByDefault
     * @return PatternOptions
     */
    public function setCacheByDefault($cacheByDefault)
    {
        $this->cacheByDefault = $cacheByDefault;
        return $this;
    }

    /**
     * Do we cache by default?
     *
     * Used by:
     * - ClassCache
     * - ObjectCache
     *
     * @return bool
     */
    public function getCacheByDefault()
    {
        return $this->cacheByDefault;
    }

    /**
     * Set whether or not to cache output
     *
     * Used by:
     * - CallbackCache
     * - ClassCache
     * - ObjectCache
     *
     * @param  bool $cacheOutput
     * @return PatternOptions
     */
    public function setCacheOutput($cacheOutput)
    {
        $this->cacheOutput = (bool) $cacheOutput;
        return $this;
    }

    /**
     * Will we cache output?
     *
     * Used by:
     * - CallbackCache
     * - ClassCache
     * - ObjectCache
     *
     * @return bool
     */
    public function getCacheOutput()
    {
        return $this->cacheOutput;
    }

    /**
     * Set class name
     *
     * Used by:
     * - ClassCache
     *
     * @param  string $class
     * @throws Exception\InvalidArgumentException
     * @return PatternOptions
     */
    public function setClass($class)
    {
        if (!is_string($class)) {
            throw new Exception\InvalidArgumentException('Invalid classname provided; must be a string');
        }
        $this->class = $class;
        return $this;
    }

    /**
     * Get class name
     *
     * Used by:
     * - ClassCache
     *
     * @return null|string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set list of method return values to cache
     *
     * Used by:
     * - ClassCache
     *
     * @param  array $classCacheMethods
     * @return PatternOptions
     */
    public function setClassCacheMethods(array $classCacheMethods)
    {
        $this->classCacheMethods = $this->recursiveStrtolower($classCacheMethods);
        return $this;
    }

    /**
     * Get list of methods from which to cache return values
     *
     * Used by:
     * - ClassCache
     *
     * @return array
     */
    public function getClassCacheMethods()
    {
        return $this->classCacheMethods;
    }

    /**
     * Set list of method return values NOT to cache
     *
     * Used by:
     * - ClassCache
     *
     * @param  array $classNonCacheMethods
     * @return PatternOptions
     */
    public function setClassNonCacheMethods(array $classNonCacheMethods)
    {
        $this->classNonCacheMethods = $this->recursiveStrtolower($classNonCacheMethods);
        return $this;
    }

    /**
     * Get list of methods from which NOT to cache return values
     *
     * Used by:
     * - ClassCache
     *
     * @return array
     */
    public function getClassNonCacheMethods()
    {
        return $this->classNonCacheMethods;
    }

    /**
     * Set directory permission
     *
     * @param  false|int $dirPermission
     * @throws Exception\InvalidArgumentException
     * @return PatternOptions
     */
    public function setDirPermission($dirPermission)
    {
        if ($dirPermission !== false) {
            if (is_string($dirPermission)) {
                $dirPermission = octdec($dirPermission);
            } else {
                $dirPermission = (int) $dirPermission;
            }

            // validate
            if (($dirPermission & 0700) != 0700) {
                throw new Exception\InvalidArgumentException(
                    'Invalid directory permission: need permission to execute, read and write by owner'
                );
            }
        }

        $this->dirPermission = $dirPermission;
        return $this;
    }

    /**
     * Gets directory permission
     *
     * @return false|int
     */
    public function getDirPermission()
    {
        return $this->dirPermission;
    }

    /**
     * Set umask
     *
     * Used by:
     * - CaptureCache
     *
     * @param  false|int $umask
     * @throws Exception\InvalidArgumentException
     * @return PatternOptions
     */
    public function setUmask($umask)
    {
        if ($umask !== false) {
            if (is_string($umask)) {
                $umask = octdec($umask);
            } else {
                $umask = (int) $umask;
            }

            // validate
            if ($umask & 0700) {
                throw new Exception\InvalidArgumentException(
                    'Invalid umask: need permission to execute, read and write by owner'
                );
            }

            // normalize
            $umask = $umask & 0777;
        }

        $this->umask = $umask;
        return $this;
    }

    /**
     * Get umask
     *
     * Used by:
     * - CaptureCache
     *
     * @return false|int
     */
    public function getUmask()
    {
        return $this->umask;
    }

    /**
     * Set whether or not file locking should be used
     *
     * Used by:
     * - CaptureCache
     *
     * @param  bool $fileLocking
     * @return PatternOptions
     */
    public function setFileLocking($fileLocking)
    {
        $this->fileLocking = (bool) $fileLocking;
        return $this;
    }

    /**
     * Is file locking enabled?
     *
     * Used by:
     * - CaptureCache
     *
     * @return bool
     */
    public function getFileLocking()
    {
        return $this->fileLocking;
    }

    /**
     * Set file permission
     *
     * @param  false|int $filePermission
     * @throws Exception\InvalidArgumentException
     * @return PatternOptions
     */
    public function setFilePermission($filePermission)
    {
        if ($filePermission !== false) {
            if (is_string($filePermission)) {
                $filePermission = octdec($filePermission);
            } else {
                $filePermission = (int) $filePermission;
            }

            // validate
            if (($filePermission & 0600) != 0600) {
                throw new Exception\InvalidArgumentException(
                    'Invalid file permission: need permission to read and write by owner'
                );
            } elseif ($filePermission & 0111) {
                throw new Exception\InvalidArgumentException(
                    "Invalid file permission: Files shoudn't be executable"
                );
            }
        }

        $this->filePermission = $filePermission;
        return $this;
    }

    /**
     * Gets file permission
     *
     * @return false|int
     */
    public function getFilePermission()
    {
        return $this->filePermission;
    }

    /**
     * Set value for index filename
     *
     * @param  string $indexFilename
     * @return PatternOptions
     */
    public function setIndexFilename($indexFilename)
    {
        $this->indexFilename = (string) $indexFilename;
        return $this;
    }

    /**
     * Get value for index filename
     *
     * @return string
     */
    public function getIndexFilename()
    {
        return $this->indexFilename;
    }

    /**
     * Set object to cache
     *
     * @param  mixed $object
     * @throws Exception\InvalidArgumentException
     * @return PatternOptions
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an object; received "%s"', __METHOD__, gettype($object)
            ));
        }
        $this->object = $object;
        return $this;
    }

    /**
     * Get object to cache
     *
     * @return null|object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set flag indicating whether or not to cache magic properties
     *
     * Used by:
     * - ObjectCache
     *
     * @param  bool $objectCacheMagicProperties
     * @return PatternOptions
     */
    public function setObjectCacheMagicProperties($objectCacheMagicProperties)
    {
        $this->objectCacheMagicProperties = (bool) $objectCacheMagicProperties;
        return $this;
    }

    /**
     * Should we cache magic properties?
     *
     * Used by:
     * - ObjectCache
     *
     * @return bool
     */
    public function getObjectCacheMagicProperties()
    {
        return $this->objectCacheMagicProperties;
    }

    /**
     * Set list of object methods for which to cache return values
     *
     * @param  array $objectCacheMethods
     * @return PatternOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setObjectCacheMethods(array $objectCacheMethods)
    {
        $this->objectCacheMethods = $this->normalizeObjectMethods($objectCacheMethods);
        return $this;
    }

    /**
     * Get list of object methods for which to cache return values
     *
     * @return array
     */
    public function getObjectCacheMethods()
    {
        return $this->objectCacheMethods;
    }

    /**
     * Set the object key part.
     *
     * Used to generate a callback key in order to speed up key generation.
     *
     * Used by:
     * - ObjectCache
     *
     * @param  mixed $objectKey
     * @return PatternOptions
     */
    public function setObjectKey($objectKey)
    {
        if ($objectKey !== null) {
            $this->objectKey = (string) $objectKey;
        } else {
            $this->objectKey = null;
        }
        return $this;
    }

    /**
     * Get object key
     *
     * Used by:
     * - ObjectCache
     *
     * @return mixed
     */
    public function getObjectKey()
    {
        if (!$this->objectKey) {
            return get_class($this->getObject());
        }
        return $this->objectKey;
    }

    /**
     * Set list of object methods for which NOT to cache return values
     *
     * @param  array $objectNonCacheMethods
     * @return PatternOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setObjectNonCacheMethods(array $objectNonCacheMethods)
    {
        $this->objectNonCacheMethods = $this->normalizeObjectMethods($objectNonCacheMethods);
        return $this;
    }

    /**
     * Get list of object methods for which NOT to cache return values
     *
     * @return array
     */
    public function getObjectNonCacheMethods()
    {
        return $this->objectNonCacheMethods;
    }

    /**
     * Set location of public directory
     *
     * Used by:
     * - CaptureCache
     *
     * @param  string $publicDir
     * @throws Exception\InvalidArgumentException
     * @return PatternOptions
     */
    public function setPublicDir($publicDir)
    {
        $publicDir = (string) $publicDir;

        if (!is_dir($publicDir)) {
            throw new Exception\InvalidArgumentException(
                "Public directory '{$publicDir}' not found or not a directory"
            );
        } elseif (!is_writable($publicDir)) {
            throw new Exception\InvalidArgumentException(
                "Public directory '{$publicDir}' not writable"
            );
        } elseif (!is_readable($publicDir)) {
            throw new Exception\InvalidArgumentException(
                "Public directory '{$publicDir}' not readable"
            );
        }

        $this->publicDir = rtrim(realpath($publicDir), DIRECTORY_SEPARATOR);
        return $this;
    }

    /**
     * Get location of public directory
     *
     * Used by:
     * - CaptureCache
     *
     * @return null|string
     */
    public function getPublicDir()
    {
        return $this->publicDir;
    }

    /**
     * Set storage adapter
     *
     * Required for the following Pattern classes:
     * - CallbackCache
     * - ClassCache
     * - ObjectCache
     * - OutputCache
     *
     * @param  string|array|Storage $storage
     * @return PatternOptions
     */
    public function setStorage($storage)
    {
        $this->storage = $this->storageFactory($storage);
        return $this;
    }

    /**
     * Get storage adapter
     *
     * Used by:
     * - CallbackCache
     * - ClassCache
     * - ObjectCache
     * - OutputCache
     *
     * @return null|Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Recursively apply strtolower on all values of an array, and return as a
     * list of unique values
     *
     * @param  array $array
     * @return array
     */
    protected function recursiveStrtolower(array $array)
    {
        return array_values(array_unique(array_map('strtolower', $array)));
    }

    /**
     * Normalize object methods
     *
     * Recursively casts values to lowercase, then determines if any are in a
     * list of methods not handled, raising an exception if so.
     *
     * @param  array $methods
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeObjectMethods(array $methods)
    {
        $methods   = $this->recursiveStrtolower($methods);
        $intersect = array_intersect(array('__set', '__get', '__unset', '__isset'), $methods);
        if (!empty($intersect)) {
            throw new Exception\InvalidArgumentException(
                "Magic properties are handled by option 'cache_magic_properties'"
            );
        }
        return $methods;
    }

    /**
     * Create a storage object from a given specification
     *
     * @param  array|string|Storage $storage
     * @throws Exception\InvalidArgumentException
     * @return Storage
     */
    protected function storageFactory($storage)
    {
        if (is_array($storage)) {
            $storage = StorageFactory::factory($storage);
        } elseif (is_string($storage)) {
            $storage = StorageFactory::adapterFactory($storage);
        } elseif (!($storage instanceof Storage)) {
            throw new Exception\InvalidArgumentException(
                'The storage must be an instanceof Zend\Cache\Storage\StorageInterface '
                . 'or an array passed to Zend\Cache\Storage::factory '
                . 'or simply the name of the storage adapter'
            );
        }

        return $storage;
    }
}
