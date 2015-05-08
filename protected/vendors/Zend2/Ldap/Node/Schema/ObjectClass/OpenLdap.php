<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node\Schema\ObjectClass;

use Zend\Ldap\Node\Schema;

/**
 * Zend\Ldap\Node\Schema\ObjectClass\OpenLdap provides access to the objectClass
 * schema information on an OpenLDAP server.
 */
class OpenLdap extends Schema\AbstractItem implements ObjectClassInterface
{
    /**
     * All inherited "MUST" attributes
     *
     * @var array
     */
    protected $inheritedMust = null;

    /**
     * All inherited "MAY" attributes
     *
     * @var array
     */
    protected $inheritedMay = null;


    /**
     * Gets the objectClass name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the objectClass OID
     *
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Gets the attributes that this objectClass must contain
     *
     * @return array
     */
    public function getMustContain()
    {
        if ($this->inheritedMust === null) {
            $this->resolveInheritance();
        }
        return $this->inheritedMust;
    }

    /**
     * Gets the attributes that this objectClass may contain
     *
     * @return array
     */
    public function getMayContain()
    {
        if ($this->inheritedMay === null) {
            $this->resolveInheritance();
        }
        return $this->inheritedMay;
    }

    /**
     * Resolves the inheritance tree
     *
     * @return void
     */
    protected function resolveInheritance()
    {
        $must = $this->must;
        $may  = $this->may;
        foreach ($this->getParents() as $p) {
            $must = array_merge($must, $p->getMustContain());
            $may  = array_merge($may, $p->getMayContain());
        }
        $must = array_unique($must);
        $may  = array_unique($may);
        $may  = array_diff($may, $must);
        sort($must, SORT_STRING);
        sort($may, SORT_STRING);
        $this->inheritedMust = $must;
        $this->inheritedMay  = $may;
    }

    /**
     * Gets the objectClass description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->desc;
    }

    /**
     * Gets the objectClass type
     *
     * @return int
     */
    public function getType()
    {
        if ($this->structural) {
            return Schema::OBJECTCLASS_TYPE_STRUCTURAL;
        } elseif ($this->abstract) {
            return Schema::OBJECTCLASS_TYPE_ABSTRACT;
        } elseif ($this->auxiliary) {
            return Schema::OBJECTCLASS_TYPE_AUXILIARY;
        }

        return Schema::OBJECTCLASS_TYPE_UNKNOWN;
    }

    /**
     * Returns the parent objectClasses of this class.
     * This includes structural, abstract and auxiliary objectClasses
     *
     * @return array
     */
    public function getParentClasses()
    {
        return $this->sup;
    }

    /**
     * Returns the parent object classes in the inheritance tree if one exists
     *
     * @return array of OpenLdap
     */
    public function getParents()
    {
        return $this->_parents;
    }
}
