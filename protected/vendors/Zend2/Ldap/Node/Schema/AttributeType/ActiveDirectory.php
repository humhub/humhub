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
 * Zend\Ldap\Node\Schema\AttributeType\ActiveDirectory provides access to the attribute type
 * schema information on an Active Directory server.
 */
class ActiveDirectory extends Schema\AbstractItem implements AttributeTypeInterface
{
    /**
     * Gets the attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->ldapdisplayname[0];
    }

    /**
     * Gets the attribute OID
     *
     * @return string
     */
    public function getOid()
    {

    }

    /**
     * Gets the attribute syntax
     *
     * @return string
     */
    public function getSyntax()
    {

    }

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength()
    {

    }

    /**
     * Returns if the attribute is single-valued.
     *
     * @return bool
     */
    public function isSingleValued()
    {

    }

    /**
     * Gets the attribute description
     *
     * @return string
     */
    public function getDescription()
    {

    }
}
