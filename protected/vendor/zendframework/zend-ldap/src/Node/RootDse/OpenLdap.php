<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node\RootDse;

use Zend\Ldap\Node;

/**
 * Zend\Ldap\Node\RootDse\OpenLdap provides a simple data-container for the
 * RootDse node of an OpenLDAP server.
 */
class OpenLdap extends Node\RootDse
{
    /**
     * Gets the configContext.
     *
     * @return string|null
     */
    public function getConfigContext()
    {
        return $this->getAttribute('configContext', 0);
    }

    /**
     * Gets the monitorContext.
     *
     * @return string|null
     */
    public function getMonitorContext()
    {
        return $this->getAttribute('monitorContext', 0);
    }

    /**
     * Determines if the control is supported
     *
     * @param  string|array $oids control oid(s) to check
     * @return bool
     */
    public function supportsControl($oids)
    {
        return $this->attributeHasValue('supportedControl', $oids);
    }

    /**
     * Determines if the extension is supported
     *
     * @param  string|array $oids oid(s) to check
     * @return bool
     */
    public function supportsExtension($oids)
    {
        return $this->attributeHasValue('supportedExtension', $oids);
    }

    /**
     * Determines if the feature is supported
     *
     * @param  string|array $oids feature oid(s) to check
     * @return bool
     */
    public function supportsFeature($oids)
    {
        return $this->attributeHasValue('supportedFeatures', $oids);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_OPENLDAP;
    }
}
