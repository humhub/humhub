<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\libs;

use Yii;

/**
 * This class contains a lot of html helpers for the views
 *
 * @since 0.5
 */
class LdapHelper
{

    public static function getLdapConnection()
    {
        $options = [
            'host' => Yii::$app->getModule('user')->settings->get('auth.ldap.hostname'),
            'port' => Yii::$app->getModule('user')->settings->get('auth.ldap.port'),
            'username' => Yii::$app->getModule('user')->settings->get('auth.ldap.username'),
            'password' => Yii::$app->getModule('user')->settings->get('auth.ldap.password'),
            'useStartTls' => (Yii::$app->getModule('user')->settings->get('auth.ldap.encryption') == 'tls'),
            'useSsl' => (Yii::$app->getModule('user')->settings->get('auth.ldap.encryption') == 'ssl'),
            'bindRequiresDn' => true,
            'baseDn' => Yii::$app->getModule('user')->settings->get('auth.ldap.baseDn'),
            'accountFilterFormat' => Yii::$app->getModule('user')->settings->get('auth.ldap.loginFilter'),
        ];

        $ldap = new \Zend\Ldap\Ldap($options);
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
