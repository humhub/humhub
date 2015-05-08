<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node\Schema;

use ArrayAccess;
use Countable;
use Zend\Ldap\Exception;

/**
 * This class provides a base implementation for managing schema
 * items like objectClass and attributeType.
 */
abstract class AbstractItem implements ArrayAccess, Countable
{
    /**
     * The underlying data
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setData($data);
    }

    /**
     * Sets the data
     *
     * @param  array $data
     * @return AbstractItem Provides a fluid interface
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gets a specific attribute from this item
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * Checks whether a specific attribute exists.
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return (array_key_exists($name, $this->data));
    }

    /**
     * Always throws Zend\Ldap\Exception\BadMethodCallException
     * Implements ArrayAccess.
     *
     * This method is needed for a full implementation of ArrayAccess
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws \Zend\Ldap\Exception\BadMethodCallException
     */
    public function offsetSet($name, $value)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * Gets a specific attribute from this item
     *
     * @param  string $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->__get($name);
    }

    /**
     * Always throws Zend\Ldap\Exception\BadMethodCallException
     * Implements ArrayAccess.
     *
     * This method is needed for a full implementation of ArrayAccess
     *
     * @param  string $name
     * @throws \Zend\Ldap\Exception\BadMethodCallException
     */
    public function offsetUnset($name)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * Checks whether a specific attribute exists.
     *
     * @param  string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->__isset($name);
    }

    /**
     * Returns the number of attributes.
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }
}
