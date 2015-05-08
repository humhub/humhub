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
use Zend\Cache\Storage\ClearByPrefixInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\IterableInterface;
use Zend\Session\Container as SessionContainer;

class Session extends AbstractAdapter implements
    ClearByPrefixInterface,
    FlushableInterface,
    IterableInterface
{

    /**
     * Set options.
     *
     * @param  array|\Traversable|SessionOptions $options
     * @return Memory
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof SessionOptions) {
            $options = new SessionOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @return SessionOptions
     * @see setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new SessionOptions());
        }
        return $this->options;
    }

    /**
     * Get the session container
     *
     * @return SessionContainer
     */
    protected function getSessionContainer()
    {
        $sessionContainer = $this->getOptions()->getSessionContainer();
        if (!$sessionContainer) {
            throw new Exception\RuntimeException("No session container configured");
        }
        return $sessionContainer;
    }

    /* IterableInterface */

    /**
     * Get the storage iterator
     *
     * @return KeyListIterator
     */
    public function getIterator()
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $keys = array_keys($cntr->offsetGet($ns));
        } else {
            $keys = array();
        }

        return new KeyListIterator($this, $keys);
    }

    /* FlushableInterface */

    /**
     * Flush the whole session container
     *
     * @return bool
     */
    public function flush()
    {
        $this->getSessionContainer()->exchangeArray(array());
        return true;
    }

    /* ClearByPrefixInterface */

    /**
     * Remove items matching given prefix
     *
     * @param string $prefix
     * @return bool
     */
    public function clearByPrefix($prefix)
    {
        $prefix = (string) $prefix;
        if ($prefix === '') {
            throw new Exception\InvalidArgumentException('No prefix given');
        }

        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return true;
        }

        $data    = $cntr->offsetGet($ns);
        $prefixL = strlen($prefix);
        foreach ($data as $key => & $item) {
            if (substr($key, 0, $prefixL) === $prefix) {
                unset($data[$key]);
            }
        }
        $cntr->offsetSet($ns, $data);

        return true;
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
        $cntr    = $this->getSessionContainer();
        $ns      = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            $success = false;
            return null;
        }

        $data    = $cntr->offsetGet($ns);
        $success = array_key_exists($normalizedKey, $data);
        if (!$success) {
            return null;
        }

        $casToken = $value = $data[$normalizedKey];
        return $value;
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return array();
        }

        $data   = $cntr->offsetGet($ns);
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            if (array_key_exists($normalizedKey, $data)) {
                $result[$normalizedKey] = $data[$normalizedKey];
            }
        }

        return $result;
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return false;
        }

        $data = $cntr->offsetGet($ns);
        return array_key_exists($normalizedKey, $data);
    }

    /**
     * Internal method to test multiple items.
     *
     * @param array $normalizedKeys
     * @return array Array of found keys
     */
    protected function internalHasItems(array & $normalizedKeys)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return array();
        }

        $data   = $cntr->offsetGet($ns);
        $result = array();
        foreach ($normalizedKeys as $normalizedKey) {
            if (array_key_exists($normalizedKey, $data)) {
                $result[] = $normalizedKey;
            }
        }

        return $result;
    }

    /**
     * Get metadata of an item.
     *
     * @param  string $normalizedKey
     * @return array|bool Metadata on success, false on failure
     * @throws Exception\ExceptionInterface
     *
     * @triggers getMetadata.pre(PreEvent)
     * @triggers getMetadata.post(PostEvent)
     * @triggers getMetadata.exception(ExceptionEvent)
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        return $this->internalHasItem($normalizedKey) ? array() : false;
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();
        $data = $cntr->offsetExists($ns) ? $cntr->offsetGet($ns) : array();
        $data[$normalizedKey] = $value;
        $cntr->offsetSet($ns, $data);
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = array_merge($cntr->offsetGet($ns), $normalizedKeyValuePairs);
        } else {
            $data = $normalizedKeyValuePairs;
        }
        $cntr->offsetSet($ns, $data);

        return array();
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);

            if (array_key_exists($normalizedKey, $data)) {
                return false;
            }

            $data[$normalizedKey] = $value;
        } else {
            $data = array($normalizedKey => $value);
        }

        $cntr->offsetSet($ns, $data);
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        $result = array();
        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);

            foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
                if (array_key_exists($normalizedKey, $data)) {
                    $result[] = $normalizedKey;
                } else {
                    $data[$normalizedKey] = $value;
                }
            }
        } else {
            $data = $normalizedKeyValuePairs;
        }

        $cntr->offsetSet($ns, $data);
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return false;
        }

        $data = $cntr->offsetGet($ns);
        if (!array_key_exists($normalizedKey, $data)) {
            return false;
        }
        $data[$normalizedKey] = $value;
        $cntr->offsetSet($ns, $data);

        return true;
    }

    /**
     * Internal method to replace multiple existing items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalReplaceItems(array & $normalizedKeyValuePairs)
    {
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();
        if (!$cntr->offsetExists($ns)) {
            return array_keys($normalizedKeyValuePairs);
        }

        $data   = $cntr->offsetGet($ns);
        $result = array();
        foreach ($normalizedKeyValuePairs as $normalizedKey => $value) {
            if (!array_key_exists($normalizedKey, $data)) {
                $result[] = $normalizedKey;
            } else {
                $data[$normalizedKey] = $value;
            }
        }
        $cntr->offsetSet($ns, $data);

        return $result;
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if (!$cntr->offsetExists($ns)) {
            return false;
        }

        $data = $cntr->offsetGet($ns);
        if (!array_key_exists($normalizedKey, $data)) {
            return false;
        }

        unset($data[$normalizedKey]);

        if (!$data) {
            $cntr->offsetUnset($ns);
        } else {
            $cntr->offsetSet($ns, $data);
        }

        return true;
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);
        } else {
            $data = array();
        }

        if (array_key_exists($normalizedKey, $data)) {
            $data[$normalizedKey]+= $value;
            $newValue = $data[$normalizedKey];
        } else {
            // initial value
            $newValue             = $value;
            $data[$normalizedKey] = $newValue;
        }

        $cntr->offsetSet($ns, $data);
        return $newValue;
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
        $cntr = $this->getSessionContainer();
        $ns   = $this->getOptions()->getNamespace();

        if ($cntr->offsetExists($ns)) {
            $data = $cntr->offsetGet($ns);
        } else {
            $data = array();
        }

        if (array_key_exists($normalizedKey, $data)) {
            $data[$normalizedKey]-= $value;
            $newValue = $data[$normalizedKey];
        } else {
            // initial value
            $newValue             = -$value;
            $data[$normalizedKey] = $newValue;
        }

        $cntr->offsetSet($ns, $data);
        return $newValue;
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
                $this->capabilities = new Capabilities(
                $this,
                $this->capabilityMarker,
                array(
                    'supportedDatatypes' => array(
                        'NULL'     => true,
                        'boolean'  => true,
                        'integer'  => true,
                        'double'   => true,
                        'string'   => true,
                        'array'    => 'array',
                        'object'   => 'object',
                        'resource' => false,
                    ),
                    'supportedMetadata'  => array(),
                    'minTtl'             => 0,
                    'maxKeyLength'       => 0,
                    'namespaceIsPrefix'  => false,
                    'namespaceSeparator' => '',
                )
            );
        }

        return $this->capabilities;
    }
}
