<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
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
        # && version_compare(PHP_VERSION, '5.9.0', '<')
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            );
        } elseif (version_compare(PHP_VERSION, '5.4.0', '<=')) {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Untested on this version!'
            );
        } else {
            $checks[] = array(
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Minimum 5.3'
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
        if (\humhub\modules\user\libs\Ldap::isAvailable()) {
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
