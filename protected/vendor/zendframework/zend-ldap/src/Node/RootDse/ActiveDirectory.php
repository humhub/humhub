<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node\RootDse;

use Zend\Ldap;
use Zend\Ldap\Node;

/**
 * Zend\Ldap\Node\RootDse\ActiveDirectory provides a simple data-container for
 * the RootDse node of an Active Directory server.
 */
class ActiveDirectory extends Node\RootDse
{
    /**
     * Gets the configurationNamingContext.
     *
     * @return string|null
     */
    public function getConfigurationNamingContext()
    {
        return $this->getAttribute('configurationNamingContext', 0);
    }

    /**
     * Gets the currentTime.
     *
     * @return string|null
     */
    public function getCurrentTime()
    {
        return $this->getAttribute('currentTime', 0);
    }

    /**
     * Gets the defaultNamingContext.
     *
     * @return string|null
     */
    public function getDefaultNamingContext()
    {
        return $this->getAttribute('defaultNamingContext', 0);
    }

    /**
     * Gets the dnsHostName.
     *
     * @return string|null
     */
    public function getDnsHostName()
    {
        return $this->getAttribute('dnsHostName', 0);
    }

    /**
     * Gets the domainControllerFunctionality.
     *
     * @return string|null
     */
    public function getDomainControllerFunctionality()
    {
        return $this->getAttribute('domainControllerFunctionality', 0);
    }

    /**
     * Gets the domainFunctionality.
     *
     * @return string|null
     */
    public function getDomainFunctionality()
    {
        return $this->getAttribute('domainFunctionality', 0);
    }

    /**
     * Gets the dsServiceName.
     *
     * @return string|null
     */
    public function getDsServiceName()
    {
        return $this->getAttribute('dsServiceName', 0);
    }

    /**
     * Gets the forestFunctionality.
     *
     * @return string|null
     */
    public function getForestFunctionality()
    {
        return $this->getAttribute('forestFunctionality', 0);
    }

    /**
     * Gets the highestCommittedUSN.
     *
     * @return string|null
     */
    public function getHighestCommittedUSN()
    {
        return $this->getAttribute('highestCommittedUSN', 0);
    }

    /**
     * Gets the isGlobalCatalogReady.
     *
     * @return string|null
     */
    public function getIsGlobalCatalogReady()
    {
        return $this->getAttribute('isGlobalCatalogReady', 0);
    }

    /**
     * Gets the isSynchronized.
     *
     * @return string|null
     */
    public function getIsSynchronized()
    {
        return $this->getAttribute('isSynchronized', 0);
    }

    /**
     * Gets the ldapServiceName.
     *
     * @return string|null
     */
    public function getLDAPServiceName()
    {
        return $this->getAttribute('ldapServiceName', 0);
    }

    /**
     * Gets the rootDomainNamingContext.
     *
     * @return string|null
     */
    public function getRootDomainNamingContext()
    {
        return $this->getAttribute('rootDomainNamingContext', 0);
    }

    /**
     * Gets the schemaNamingContext.
     *
     * @return string|null
     */
    public function getSchemaNamingContext()
    {
        return $this->getAttribute('schemaNamingContext', 0);
    }

    /**
     * Gets the serverName.
     *
     * @return string|null
     */
    public function getServerName()
    {
        return $this->getAttribute('serverName', 0);
    }

    /**
     * Determines if the capability is supported
     *
     * @param string|string|array $oids capability(s) to check
     * @return bool
     */
    public function supportsCapability($oids)
    {
        return $this->attributeHasValue('supportedCapabilities', $oids);
    }

    /**
     * Determines if the control is supported
     *
     * @param string|array $oids control oid(s) to check
     * @return bool
     */
    public function supportsControl($oids)
    {
        return $this->attributeHasValue('supportedControl', $oids);
    }

    /**
     * Determines if the version is supported
     *
     * @param string|array $policies policy(s) to check
     * @return bool
     */
    public function supportsPolicy($policies)
    {
        return $this->attributeHasValue('supportedLDAPPolicies', $policies);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_ACTIVEDIRECTORY;
    }

    /**
     * Returns the schema DN
     *
     * @return \Zend\Ldap\Dn
     */
    public function getSchemaDn()
    {
        $schemaDn = $this->getSchemaNamingContext();
        return Ldap\Dn::fromString($schemaDn);
    }
}
