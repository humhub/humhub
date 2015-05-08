<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Node\RootDse;

use Zend\Ldap\Node;

/**
 * Zend\Ldap\Node\RootDse\eDirectory provides a simple data-container for the
 * RootDse node of a Novell eDirectory server.
 */
class eDirectory extends Node\RootDse
{
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
     * Gets the vendorName.
     *
     * @return string|null
     */
    public function getVendorName()
    {
        return $this->getAttribute('vendorName', 0);
    }

    /**
     * Gets the vendorVersion.
     *
     * @return string|null
     */
    public function getVendorVersion()
    {
        return $this->getAttribute('vendorVersion', 0);
    }

    /**
     * Gets the dsaName.
     *
     * @return string|null
     */
    public function getDsaName()
    {
        return $this->getAttribute('dsaName', 0);
    }

    /**
     * Gets the server statistics "errors".
     *
     * @return string|null
     */
    public function getStatisticsErrors()
    {
        return $this->getAttribute('errors', 0);
    }

    /**
     * Gets the server statistics "securityErrors".
     *
     * @return string|null
     */
    public function getStatisticsSecurityErrors()
    {
        return $this->getAttribute('securityErrors', 0);
    }

    /**
     * Gets the server statistics "chainings".
     *
     * @return string|null
     */
    public function getStatisticsChainings()
    {
        return $this->getAttribute('chainings', 0);
    }

    /**
     * Gets the server statistics "referralsReturned".
     *
     * @return string|null
     */
    public function getStatisticsReferralsReturned()
    {
        return $this->getAttribute('referralsReturned', 0);
    }

    /**
     * Gets the server statistics "extendedOps".
     *
     * @return string|null
     */
    public function getStatisticsExtendedOps()
    {
        return $this->getAttribute('extendedOps', 0);
    }

    /**
     * Gets the server statistics "abandonOps".
     *
     * @return string|null
     */
    public function getStatisticsAbandonOps()
    {
        return $this->getAttribute('abandonOps', 0);
    }

    /**
     * Gets the server statistics "wholeSubtreeSearchOps".
     *
     * @return string|null
     */
    public function getStatisticsWholeSubtreeSearchOps()
    {
        return $this->getAttribute('wholeSubtreeSearchOps', 0);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_EDIRECTORY;
    }
}
