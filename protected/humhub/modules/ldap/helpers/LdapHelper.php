<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\helpers;

use humhub\components\SettingsManager;
use Yii;
use Zend\Ldap\Ldap;

/**
 * This class contains a lot of html helpers for the views
 *
 * @since 0.5
 */
class LdapHelper
{

    public static function getLdapConnection()
    {
        /** @var SettingsManager $settings */
        $settings = Yii::$app->getModule('user')->settings;

        $options = [
            'host' => $settings->get('auth.ldap.hostname'),
            'port' => $settings->get('auth.ldap.port'),
            'username' => $settings->get('auth.ldap.username'),
            'password' => $settings->get('auth.ldap.password'),
            'useStartTls' => ($settings->get('auth.ldap.encryption') == 'tls'),
            'useSsl' => ($settings->get('auth.ldap.encryption') == 'ssl'),
            'bindRequiresDn' => true,
            'baseDn' => $settings->get('auth.ldap.baseDn'),
            'accountFilterFormat' => $settings->get('auth.ldap.loginFilter'),
        ];

        $ldap = new Ldap($options);
        $ldap->bind();

        return $ldap;
    }

    /**
     * Checks if LDAP support is enabled
     * 
     * @return boolean is LDAP support is enabled
     */
    public static function isLdapEnabled()
    {
        return (boolean) Yii::$app->getModule('user')->settings->get('auth.ldap.enabled');
    }

    /**
     * Checks if LDAP is supported
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
   
    
    

}
