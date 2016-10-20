<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016r HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;

/**
 * SelfTest is a helper class which checks all dependencies of the application.
 *
 * @package humhub.libs
 * @since 0.5
 * @author Luke
 */
class SelfTest
{

    /**
     * Get Results of the Application SelfTest.
     *
     * Fields
     *  - title
     *  - state (OK, WARNING or ERROR)
     *  - hint
     *
     * @return Array
     */
    public static function getResults()
    {
        /**
         * ['title']
         * ['state']    = OK, WARNING, ERROR
         * ['hint']
         */
        $checks = array();

        // Checks PHP Version
        $title = 'PHP - Version - ' . PHP_VERSION;
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Minimum 5.4'
            );
        }

        // Checks GD Extension
        $title = 'PHP - GD Extension';
        if (function_exists('gd_info')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install GD Extension'
            );
        }

        $title = 'PHP - INTL Extension';
        if (function_exists('collator_create')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install INTL Extension'
            );
        }

        $icuVersion = (defined('INTL_ICU_VERSION')) ? INTL_ICU_VERSION : 0;
        $icuMinVersion = '4.8.1';
        $title = 'PHP - INTL Extension - ICU Version (' . $icuVersion . ')';
        if (version_compare($icuVersion, $icuMinVersion, '>=')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'ICU Data ' . $icuMinVersion . ' or higher is required'
            );
        }
        $icuDataVersion = (defined('INTL_ICU_DATA_VERSION')) ? INTL_ICU_DATA_VERSION : 0;
        $icuMinDataVersion = '4.8.1';
        $title = 'PHP - INTL Extension - ICU Data Version (' . $icuDataVersion . ')';
        if (version_compare($icuDataVersion, $icuMinDataVersion, '>=')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'ICU Data ' . $icuMinDataVersion . ' or higher is required'
            );
        }

        // Checks GD Extension
        $title = 'PHP - EXIF Extension';
        if (function_exists('exif_read_data')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install EXIF Extension'
            );
        }

        // Check FileInfo Extension
        $title = 'PHP - FileInfo Extension';
        if (extension_loaded('fileinfo')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install FileInfo Extension'
            );
        }

        // Checks CURL Extension
        $title = 'PHP - Multibyte String Functions';
        if (function_exists('mb_substr')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install PHP Multibyte Extension'
            );
        }

        // Checks CURL Extension
        $title = 'PHP - Curl Extension';
        if (function_exists('curl_version')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install Curl Extension'
            );
        }
        // Checks ZIP Extension
        $title = 'PHP - ZIP Extension';
        if (class_exists('ZipArchive')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install PHP Zip Extension'
            );
        }

        // Checks LDAP Extension
        $title = 'LDAP Support';
        if (\humhub\modules\user\authclient\ZendLdapClient::isLdapAvailable()) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional - Install PHP LDAP Extension and Zend LDAP Composer Package'
            );
        }

        // Checks APC Extension
        $title = 'PHP - APC Support';
        if (function_exists('apc_add')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional - Install APC Extension for APC Caching'
            );
        }

        // Checks SQLite3 Extension
        $title = 'PHP - SQLite3 Support';
        if (class_exists('SQLite3')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional - Install SQLite3 Extension for DB Caching'
            );
        }

        $title = 'PHP - PDO Mysql Extension';
        if (extension_loaded('pdo_mysql')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install PDO Mysql Extension'
            );
        }

        // Checks Writeable Config
        /*
          $title = 'Permissions - Config';
          $configFile = dirname(Yii::$app->params['dynamicConfigFile']);
          if (is_writeable($configFile)) {
          $checks[] = array(
          'title' => Yii::t('base', $title),
          'state' => 'OK'
          );
          } else {
          $checks[] = array(
          'title' => Yii::t('base', $title),
          'state' => 'ERROR',
          'hint' => 'Make ' . $configFile . " writable for the webserver/php!"
          );
          }
         */

        // Check Runtime Directory
        $title = 'Permissions - Runtime';
        $path = Yii::getAlias('@runtime');
        if (is_writeable($path)) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the webserver/php!"
            );
        }

        // Check Assets Directory
        $title = 'Permissions - Assets';
        $path = Yii::getAlias('@webroot/assets');
        if (is_writeable($path)) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the webserver/php!"
            );
        }


        // Check Uploads Directory
        $title = 'Permissions - Uploads';
        $path = Yii::getAlias('@webroot/uploads');
        if (is_writeable($path)) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the webserver/php!"
            );
        }

        // Check Custom Modules Directory
        $title = 'Permissions - Module Directory';

        $path = Yii::getAlias(Yii::$app->params['moduleMarketplacePath']);
        if (is_writeable($path)) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the webserver/php!"
            );
        }
        // Check Custom Modules Directory
        $title = 'Permissions - Dynamic Config';

        $path = Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
        if (!is_file($path)) {
            $path = dirname($path);
        }

        if (is_writeable($path)) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the webserver/php!"
            );
        }
        return $checks;
    }

}
