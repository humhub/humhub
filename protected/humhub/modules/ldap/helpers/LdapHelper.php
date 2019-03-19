<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\helpers;

use humhub\modules\ldap\authclient\LdapAuth;
use Yii;

/**
 * This class contains LDAP helpers
 *
 * @since 0.5
 */
class LdapHelper
{

    /**
     * Checks if LDAP is supported by this environment.
     *
     * @return bool
     */
    public static function isLdapAvailable()
    {
        if (!class_exists('Zend\Ldap\Ldap')) {
            return false;
        }

        if (!function_exists('ldap_bind')) {
            return false;
        }

        return true;
    }

    /**
     * Checks if at least one LDAP Authclient is enabled.
     *
     * @return bool
     */
    public static function isLdapEnabled()
    {
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {
            if ($authClient instanceof LdapAuth) {
                return true;
            }
        }

        return false;

    }

}
