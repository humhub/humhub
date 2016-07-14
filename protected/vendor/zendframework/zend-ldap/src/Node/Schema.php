<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node;

use Zend\Ldap;

/**
 * Zend\Ldap\Node\Schema provides a simple data-container for the Schema node.
 */
class Schema extends AbstractNode
{
    const OBJECTCLASS_TYPE_UNKNOWN    = 0;
    const OBJECTCLASS_TYPE_STRUCTURAL = 1;
    const OBJECTCLASS_TYPE_ABSTRACT   = 3;
    const OBJECTCLASS_TYPE_AUXILIARY  = 4;

    /**
     * Factory method to create the Schema node.
     *
     * @param  \Zend\Ldap\Ldap $ldap
     * @return Schema
     */
    public static function create(Ldap\Ldap $ldap)
    {
        $dn   = $ldap->getRootDse()->getSchemaDn();
        $data = $ldap->getEntry($dn, array('*', '+'), true);
        switch ($ldap->getRootDse()->getServerType()) {
            case RootDse::SERVER_TYPE_ACTIVEDIRECTORY:
                return new Schema\ActiveDirectory($dn, $data, $ldap);
            case RootDse::SERVER_TYPE_OPENLDAP:
                return new Schema\OpenLdap($dn, $data, $ldap);
            case RootDse::SERVER_TYPE_EDIRECTORY:
            default:
                return new static($dn, $data, $ldap);
        }
    }

    /**
     * Constructor.
     *
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param  \Zend\Ldap\Dn   $dn
     * @param  array           $data
     * @param  \Zend\Ldap\Ldap $ldap
     */
    protected function __construct(Ldap\Dn $dn, array $data, Ldap\Ldap $ldap)
    {
        parent::__construct($dn, $data, true);
        $this->parseSchema($dn, $ldap);
    }

    /**
     * Parses the schema
     *
     * @param  \Zend\Ldap\Dn   $dn
     * @param  \Zend\Ldap\Ldap $ldap
     * @return Schema Provides a fluid interface
     */
    protected function parseSchema(Ldap\Dn $dn, Ldap\Ldap $ldap)
    {
        return $this;
    }

    /**
     * Gets the attribute Types
     *
     * @return array
     */
    public function getAttributeTypes()
    {
        return array();
    }

    /**
     * Gets the object classes
     *
     * @return array
     */
    public function getObjectClasses()
    {
        return array();
    }
}
