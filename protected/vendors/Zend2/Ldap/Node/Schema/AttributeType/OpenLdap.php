<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node\Schema\AttributeType;

use Zend\Ldap\Node\Schema;

/**
 * Zend\Ldap\Node\Schema\AttributeType\OpenLdap provides access to the attribute type
 * schema information on an OpenLDAP server.
 */
class OpenLdap extends Schema\AbstractItem implements AttributeTypeInterface
{
    /**
     * Gets the attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the attribute OID
     *
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Gets the attribute syntax
     *
     * @return string
     */
    public function getSyntax()
    {
        if ($this->syntax === null) {
            $parent = $this->getParent();
            if ($parent === null) {
                return null;
            } else {
                return $parent->getSyntax();
            }
        }

        return $this->syntax;
    }

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        $maxLength = $this->{'max-length'};
        if ($maxLength === null) {
            $parent = $this->getParent();
            if ($parent === null) {
                return null;
            } else {
                return $parent->getMaxLength();
            }
        }

        return (int) $maxLength;
    }

    /**
     * Returns if the attribute is single-valued.
     *
     * @return bool
     */
    public function isSingleValued()
    {
        return $this->{'single-value'};
    }

    /**
     * Gets the attribute description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->desc;
    }

    /**
     * Returns the parent attribute type in the inheritance tree if one exists
     *
     * @return OpenLdap|null
     */
    public function getParent()
    {
        if (count($this->_parents) === 1) {
            return $this->_parents[0];
        }

        return null;
    }
}
