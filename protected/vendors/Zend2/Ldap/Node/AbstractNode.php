<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node;

use ArrayAccess;
use Countable;
use Zend\Ldap;
use Zend\Ldap\Exception;

/**
 * This class provides a base implementation for LDAP nodes
 */
abstract class AbstractNode implements ArrayAccess, Countable
{
    protected static $systemAttributes = array('createtimestamp', 'creatorsname',
                                               'entrycsn', 'entrydn', 'entryuuid', 'hassubordinates', 'modifiersname',
                                               'modifytimestamp', 'structuralobjectclass', 'subschemasubentry',
                                               'distinguishedname', 'instancetype', 'name', 'objectcategory',
                                               'objectguid',
                                               'usnchanged', 'usncreated', 'whenchanged', 'whencreated');

    /**
     * Holds the node's DN.
     *
     * @var \Zend\Ldap\Dn
     */
    protected $dn;

    /**
     * Holds the node's current data.
     *
     * @var array
     */
    protected $currentData;

    /**
     * Constructor.
     *
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param  \Zend\Ldap\Dn $dn
     * @param  array         $data
     * @param  bool       $fromDataSource
     */
    protected function __construct(Ldap\Dn $dn, array $data, $fromDataSource)
    {
        $this->dn = $dn;
        $this->loadData($data, $fromDataSource);
    }

    /**
     * @param array   $data
     * @param  bool $fromDataSource
     */
    protected function loadData(array $data, $fromDataSource)
    {
        if (array_key_exists('dn', $data)) {
            unset($data['dn']);
        }
        ksort($data, SORT_STRING);
        $this->currentData = $data;
    }

    /**
     * Reload node attributes from LDAP.
     *
     * This is an online method.
     *
     * @param  \Zend\Ldap\Ldap $ldap
     * @return AbstractNode Provides a fluid interface
     */
    public function reload(Ldap\Ldap $ldap = null)
    {
        if ($ldap !== null) {
            $data = $ldap->getEntry($this->_getDn(), array('*', '+'), true);
            $this->loadData($data, true);
        }

        return $this;
    }

    /**
     * Gets the DN of the current node as a Zend\Ldap\Dn.
     *
     * This is an offline method.
     *
     * @return \Zend\Ldap\Dn
     */
    protected function _getDn()
    {
        return $this->dn;
    }

    /**
     * Gets the DN of the current node as a Zend\Ldap\Dn.
     * The method returns a clone of the node's DN to prohibit modification.
     *
     * This is an offline method.
     *
     * @return \Zend\Ldap\Dn
     */
    public function getDn()
    {
        $dn = clone $this->_getDn();
        return $dn;
    }

    /**
     * Gets the DN of the current node as a string.
     *
     * This is an offline method.
     *
     * @param  string $caseFold
     * @return string
     */
    public function getDnString($caseFold = null)
    {
        return $this->_getDn()->toString($caseFold);
    }

    /**
     * Gets the DN of the current node as an array.
     *
     * This is an offline method.
     *
     * @param  string $caseFold
     * @return array
     */
    public function getDnArray($caseFold = null)
    {
        return $this->_getDn()->toArray($caseFold);
    }

    /**
     * Gets the RDN of the current node as a string.
     *
     * This is an offline method.
     *
     * @param  string $caseFold
     * @return string
     */
    public function getRdnString($caseFold = null)
    {
        return $this->_getDn()->getRdnString($caseFold);
    }

    /**
     * Gets the RDN of the current node as an array.
     *
     * This is an offline method.
     *
     * @param  string $caseFold
     * @return array
     */
    public function getRdnArray($caseFold = null)
    {
        return $this->_getDn()->getRdn($caseFold);
    }

    /**
     * Gets the objectClass of the node
     *
     * @return array
     */
    public function getObjectClass()
    {
        return $this->getAttribute('objectClass', null);
    }

    /**
     * Gets all attributes of node.
     *
     * The collection contains all attributes.
     *
     * This is an offline method.
     *
     * @param  bool $includeSystemAttributes
     * @return array
     */
    public function getAttributes($includeSystemAttributes = true)
    {
        $data = array();
        foreach ($this->getData($includeSystemAttributes) as $name => $value) {
            $data[$name] = $this->getAttribute($name, null);
        }
        return $data;
    }

    /**
     * Returns the DN of the current node. {@see getDnString()}
     *
     * @return string
     */
    public function toString()
    {
        return $this->getDnString();
    }

    /**
     * Cast to string representation {@see toString()}
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Returns an array representation of the current node
     *
     * @param  bool $includeSystemAttributes
     * @return array
     */
    public function toArray($includeSystemAttributes = true)
    {
        $attributes = $this->getAttributes($includeSystemAttributes);
        return array_merge(array('dn' => $this->getDnString()), $attributes);
    }

    /**
     * Returns a JSON representation of the current node
     *
     * @param  bool $includeSystemAttributes
     * @return string
     */
    public function toJson($includeSystemAttributes = true)
    {
        return json_encode($this->toArray($includeSystemAttributes));
    }

    /**
     * Gets node attributes.
     *
     * The array contains all attributes in its internal format (no conversion).
     *
     * This is an offline method.
     *
     * @param  bool $includeSystemAttributes
     * @return array
     */
    public function getData($includeSystemAttributes = true)
    {
        if ($includeSystemAttributes === false) {
            $data = array();
            foreach ($this->currentData as $key => $value) {
                if (!in_array($key, static::$systemAttributes)) {
                    $data[$key] = $value;
                }
            }
            return $data;
        }

        return $this->currentData;
    }

    /**
     * Checks whether a given attribute exists.
     *
     * If $emptyExists is false empty attributes (containing only array()) are
     * treated as non-existent returning false.
     * If $emptyExists is true empty attributes are treated as existent returning
     * true. In this case method returns false only if the attribute name is
     * missing in the key-collection.
     *
     * @param  string  $name
     * @param  bool $emptyExists
     * @return bool
     */
    public function existsAttribute($name, $emptyExists = false)
    {
        $name = strtolower($name);
        if (isset($this->currentData[$name])) {
            if ($emptyExists) {
                return true;
            }

            return count($this->currentData[$name]) > 0;
        }

        return false;
    }

    /**
     * Checks if the given value(s) exist in the attribute
     *
     * @param  string      $attribName
     * @param  mixed|array $value
     * @return bool
     */
    public function attributeHasValue($attribName, $value)
    {
        return Ldap\Attribute::attributeHasValue($this->currentData, $attribName, $value);
    }

    /**
     * Gets a LDAP attribute.
     *
     * This is an offline method.
     *
     * @param  string  $name
     * @param  int $index
     * @return mixed
     * @throws \Zend\Ldap\Exception\LdapException
     */
    public function getAttribute($name, $index = null)
    {
        if ($name == 'dn') {
            return $this->getDnString();
        }

        return Ldap\Attribute::getAttribute($this->currentData, $name, $index);
    }

    /**
     * Gets a LDAP date/time attribute.
     *
     * This is an offline method.
     *
     * @param  string  $name
     * @param  int $index
     * @return array|int
     * @throws \Zend\Ldap\Exception\LdapException
     */
    public function getDateTimeAttribute($name, $index = null)
    {
        return Ldap\Attribute::getDateTimeAttribute($this->currentData, $name, $index);
    }

    /**
     * Sets a LDAP attribute.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws \Zend\Ldap\Exception\BadMethodCallException
     */
    public function __set($name, $value)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * Gets a LDAP attribute.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @return mixed
     * @throws \Zend\Ldap\Exception\LdapException
     */
    public function __get($name)
    {
        return $this->getAttribute($name, null);
    }

    /**
     * Deletes a LDAP attribute.
     *
     * This method deletes the attribute.
     *
     * This is an offline method.
     *
     * @param  $name
     * @throws \Zend\Ldap\Exception\BadMethodCallException
     */
    public function __unset($name)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * Checks whether a given attribute exists.
     *
     * Empty attributes will be treated as non-existent.
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->existsAttribute($name, false);
    }

    /**
     * Sets a LDAP attribute.
     * Implements ArrayAccess.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @param         $value
     * @throws \Zend\Ldap\Exception\BadMethodCallException
     * @param  mixed  $value
     * @throws \Zend\Ldap\Exception\BadMethodCallException
     */
    public function offsetSet($name, $value)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * Gets a LDAP attribute.
     * Implements ArrayAccess.
     *
     * This is an offline method.
     *
     * @param  string $name
     * @return mixed
     * @throws \Zend\Ldap\Exception\LdapException
     */
    public function offsetGet($name)
    {
        return $this->getAttribute($name, null);
    }

    /**
     * Deletes a LDAP attribute.
     * Implements ArrayAccess.
     *
     * This method deletes the attribute.
     *
     * This is an offline method.
     *
     * @param  $name
     * @throws \Zend\Ldap\Exception\BadMethodCallException
     */
    public function offsetUnset($name)
    {
        throw new Exception\BadMethodCallException();
    }

    /**
     * Checks whether a given attribute exists.
     * Implements ArrayAccess.
     *
     * Empty attributes will be treated as non-existent.
     *
     * @param  string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->existsAttribute($name, false);
    }

    /**
     * Returns the number of attributes in node.
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->currentData);
    }
}
