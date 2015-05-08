<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use ArrayObject;
use stdClass;
use Zend\Cache\Exception;
use Zend\EventManager\EventsCapableInterface;

class Capabilities
{
    /**
     * The storage instance
     *
     * @var StorageInterface
     */
    protected $storage;

    /**
     * A marker to set/change capabilities
     *
     * @var stdClass
     */
    protected $marker;

    /**
     * Base capabilities
     *
     * @var null|Capabilities
     */
    protected $baseCapabilities;

    /**
     * Expire read
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|bool
     */
    protected $expiredRead;

    /**
     * Max. key length
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|int
     */
    protected $maxKeyLength;

    /**
     * Min. TTL (0 means items never expire)
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|int
     */
    protected $minTtl;

    /**
     * Max. TTL (0 means infinite)
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|int
     */
    protected $maxTtl;

    /**
     * Namespace is prefix
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|bool
     */
    protected $namespaceIsPrefix;

    /**
     * Namespace separator
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|string
     */
    protected $namespaceSeparator;

    /**
     * Static ttl
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|bool
     */
    protected $staticTtl;

   /**
    * Supported datatypes
    *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
    *
    * @var null|array
    */
    protected $supportedDatatypes;

    /**
     * Supported metdata
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|array
     */
    protected $supportedMetadata;

    /**
     * TTL precision
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|int
     */
    protected $ttlPrecision;

    /**
     * Use request time
     *
    * If it's NULL the capability isn't set and the getter
    * returns the base capability or the default value.
     *
     * @var null|bool
     */
    protected $useRequestTime;

    /**
     * Constructor
     *
     * @param StorageInterface  $storage
     * @param stdClass          $marker
     * @param array             $capabilities
     * @param null|Capabilities $baseCapabilities
     */
    public function __construct(
        StorageInterface $storage,
        stdClass $marker,
        array $capabilities = array(),
        Capabilities $baseCapabilities = null
    ) {
        $this->storage = $storage;
        $this->marker  = $marker;
        $this->baseCapabilities = $baseCapabilities;

        foreach ($capabilities as $name => $value) {
            $this->setCapability($marker, $name, $value);
        }
    }

    /**
     * Get the storage adapter
     *
     * @return StorageInterface
     */
    public function getAdapter()
    {
        return $this->storage;
    }

    /**
     * Get supported datatypes
     *
     * @return array
     */
    public function getSupportedDatatypes()
    {
        return $this->getCapability('supportedDatatypes', array(
            'NULL'     => false,
            'boolean'  => false,
            'integer'  => false,
            'double'   => false,
            'string'   => true,
            'array'    => false,
            'object'   => false,
            'resource' => false,
        ));
    }

    /**
     * Set supported datatypes
     *
     * @param  stdClass $marker
     * @param  array $datatypes
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setSupportedDatatypes(stdClass $marker, array $datatypes)
    {
        $allTypes = array(
            'array',
            'boolean',
            'double',
            'integer',
            'NULL',
            'object',
            'resource',
            'string',
        );

        // check/normalize datatype values
        foreach ($datatypes as $type => &$toType) {
            if (!in_array($type, $allTypes)) {
                throw new Exception\InvalidArgumentException("Unknown datatype '{$type}'");
            }

            if (is_string($toType)) {
                $toType = strtolower($toType);
                if (!in_array($toType, $allTypes)) {
                    throw new Exception\InvalidArgumentException("Unknown datatype '{$toType}'");
                }
            } else {
                $toType = (bool) $toType;
            }
        }

        // add missing datatypes as not supported
        $missingTypes = array_diff($allTypes, array_keys($datatypes));
        foreach ($missingTypes as $type) {
            $datatypes[$type] = false;
        }

        return $this->setCapability($marker, 'supportedDatatypes', $datatypes);
    }

    /**
     * Get supported metadata
     *
     * @return array
     */
    public function getSupportedMetadata()
    {
        return $this->getCapability('supportedMetadata', array());
    }

    /**
     * Set supported metadata
     *
     * @param  stdClass $marker
     * @param  string[] $metadata
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setSupportedMetadata(stdClass $marker, array $metadata)
    {
        foreach ($metadata as $name) {
            if (!is_string($name)) {
                throw new Exception\InvalidArgumentException('$metadata must be an array of strings');
            }
        }
        return $this->setCapability($marker, 'supportedMetadata', $metadata);
    }

    /**
     * Get minimum supported time-to-live
     *
     * @return int 0 means items never expire
     */
    public function getMinTtl()
    {
        return $this->getCapability('minTtl', 0);
    }

    /**
     * Set minimum supported time-to-live
     *
     * @param  stdClass $marker
     * @param  int $minTtl
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setMinTtl(stdClass $marker, $minTtl)
    {
        $minTtl = (int) $minTtl;
        if ($minTtl < 0) {
            throw new Exception\InvalidArgumentException('$minTtl must be greater or equal 0');
        }
        return $this->setCapability($marker, 'minTtl', $minTtl);
    }

    /**
     * Get maximum supported time-to-live
     *
     * @return int 0 means infinite
     */
    public function getMaxTtl()
    {
        return $this->getCapability('maxTtl', 0);
    }

    /**
     * Set maximum supported time-to-live
     *
     * @param  stdClass $marker
     * @param  int $maxTtl
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setMaxTtl(stdClass $marker, $maxTtl)
    {
        $maxTtl = (int) $maxTtl;
        if ($maxTtl < 0) {
            throw new Exception\InvalidArgumentException('$maxTtl must be greater or equal 0');
        }
        return $this->setCapability($marker, 'maxTtl', $maxTtl);
    }

    /**
     * Is the time-to-live handled static (on write)
     * or dynamic (on read)
     *
     * @return bool
     */
    public function getStaticTtl()
    {
        return $this->getCapability('staticTtl', false);
    }

    /**
     * Set if the time-to-live handled static (on write) or dynamic (on read)
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     */
    public function setStaticTtl(stdClass $marker, $flag)
    {
        return $this->setCapability($marker, 'staticTtl', (bool) $flag);
    }

    /**
     * Get time-to-live precision
     *
     * @return float
     */
    public function getTtlPrecision()
    {
        return $this->getCapability('ttlPrecision', 1);
    }

    /**
     * Set time-to-live precision
     *
     * @param  stdClass $marker
     * @param  float $ttlPrecision
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setTtlPrecision(stdClass $marker, $ttlPrecision)
    {
        $ttlPrecision = (float) $ttlPrecision;
        if ($ttlPrecision <= 0) {
            throw new Exception\InvalidArgumentException('$ttlPrecision must be greater than 0');
        }
        return $this->setCapability($marker, 'ttlPrecision', $ttlPrecision);
    }

    /**
     * Get use request time
     *
     * @return bool
     */
    public function getUseRequestTime()
    {
        return $this->getCapability('useRequestTime', false);
    }

    /**
     * Set use request time
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     */
    public function setUseRequestTime(stdClass $marker, $flag)
    {
        return $this->setCapability($marker, 'useRequestTime', (bool) $flag);
    }

    /**
     * Get if expired items are readable
     *
     * @return bool
     */
    public function getExpiredRead()
    {
        return $this->getCapability('expiredRead', false);
    }

    /**
     * Set if expired items are readable
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     */
    public function setExpiredRead(stdClass $marker, $flag)
    {
        return $this->setCapability($marker, 'expiredRead', (bool) $flag);
    }

    /**
     * Get maximum key lenth
     *
     * @return int -1 means unknown, 0 means infinite
     */
    public function getMaxKeyLength()
    {
        return $this->getCapability('maxKeyLength', -1);
    }

    /**
     * Set maximum key length
     *
     * @param  stdClass $marker
     * @param  int $maxKeyLength
     * @throws Exception\InvalidArgumentException
     * @return Capabilities Fluent interface
     */
    public function setMaxKeyLength(stdClass $marker, $maxKeyLength)
    {
        $maxKeyLength = (int) $maxKeyLength;
        if ($maxKeyLength < -1) {
            throw new Exception\InvalidArgumentException('$maxKeyLength must be greater or equal than -1');
        }
        return $this->setCapability($marker, 'maxKeyLength', $maxKeyLength);
    }

    /**
     * Get if namespace support is implemented as prefix
     *
     * @return bool
     */
    public function getNamespaceIsPrefix()
    {
        return $this->getCapability('namespaceIsPrefix', true);
    }

    /**
     * Set if namespace support is implemented as prefix
     *
     * @param  stdClass $marker
     * @param  bool $flag
     * @return Capabilities Fluent interface
     */
    public function setNamespaceIsPrefix(stdClass $marker, $flag)
    {
        return $this->setCapability($marker, 'namespaceIsPrefix', (bool) $flag);
    }

    /**
     * Get namespace separator if namespace is implemented as prefix
     *
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->getCapability('namespaceSeparator', '');
    }

    /**
     * Set the namespace separator if namespace is implemented as prefix
     *
     * @param  stdClass $marker
     * @param  string $separator
     * @return Capabilities Fluent interface
     */
    public function setNamespaceSeparator(stdClass $marker, $separator)
    {
        return $this->setCapability($marker, 'namespaceSeparator', (string) $separator);
    }

    /**
     * Get a capability
     *
     * @param  string $property
     * @param  mixed $default
     * @return mixed
     */
    protected function getCapability($property, $default = null)
    {
        if ($this->$property !== null) {
            return $this->$property;
        } elseif ($this->baseCapabilities) {
            $getMethod = 'get' . $property;
            return $this->baseCapabilities->$getMethod();
        }
        return $default;
    }

    /**
     * Change a capability
     *
     * @param  stdClass $marker
     * @param  string $property
     * @param  mixed $value
     * @return Capabilities Fluent interface
     * @throws Exception\InvalidArgumentException
     */
    protected function setCapability(stdClass $marker, $property, $value)
    {
        if ($this->marker !== $marker) {
            throw new Exception\InvalidArgumentException('Invalid marker');
        }

        if ($this->$property !== $value) {
            $this->$property = $value;

            // trigger event
            if ($this->storage instanceof EventsCapableInterface) {
                $this->storage->getEventManager()->trigger('capability', $this->storage, new ArrayObject(array(
                    $property => $value
                )));
            }
        }

        return $this;
    }
}
