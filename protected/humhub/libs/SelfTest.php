<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\marketplace\Module;
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
        $checks = [];

        // Checks PHP Version
        $title = 'PHP - Version - ' . PHP_VERSION;

        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } elseif (version_compare(PHP_VERSION, '7.0', '>=')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Minimum Version 7.1'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Minimum Version 7.1'
            ];
        }

        // Checks GD Extension
        $title = 'PHP - GD Extension';
        if (function_exists('gd_info')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install GD Extension'
            ];
        }

        // Checks GD JPEG Extension
        $title = 'PHP - GD Extension - JPEG Support';
        if (function_exists('imageCreateFromJpeg')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install GD Extension - JPEG Support'
            ];
        }

        // Checks GD PNG Extension
        $title = 'PHP - GD Extension - PNG Support';
        if (function_exists('imageCreateFromPng')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install GD Extension - PNG Support'
            ];
        }

        // Checks INTL Extension
        $title = 'PHP - INTL Extension';

        if (function_exists('collator_create')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install INTL Extension'
            ];
        }

        $icuVersion = (defined('INTL_ICU_VERSION')) ? INTL_ICU_VERSION : 0;
        $icuMinVersion = '4.8.1';

        $title = 'PHP - INTL Extension - ICU Version (' . $icuVersion . ')';

        if (version_compare($icuVersion, $icuMinVersion, '>=')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'ICU Data ' . $icuMinVersion . ' or higher is required'
            ];
        }
        $icuDataVersion = (defined('INTL_ICU_DATA_VERSION')) ? INTL_ICU_DATA_VERSION : 0;
        $icuMinDataVersion = '4.8.1';

        $title = 'PHP - INTL Extension - ICU Data Version (' . $icuDataVersion . ')';

        if (version_compare($icuDataVersion, $icuMinDataVersion, '>=')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'ICU Data ' . $icuMinDataVersion . ' or higher is required'
            ];
        }

        // Checks EXIF Extension
        $title = 'PHP - EXIF Extension';

        if (function_exists('exif_read_data')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install EXIF Extension'
            ];
        }

        // Checks XML Extension
        $title = 'PHP - XML Extension';

        if (function_exists('libxml_get_errors')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Install XML Extension'
            ];
        }

        // Check FileInfo Extension
        $title = 'PHP - FileInfo Extension';

        if (extension_loaded('fileinfo')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install FileInfo Extension'
            ];
        }

        // Checks Multibyte Extension
        $title = 'PHP - Multibyte String Functions';

        if (function_exists('mb_substr')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install PHP Multibyte Extension'
            ];
        }

        // Checks iconv Extension
        $title = 'PHP - iconv Extension';
        if (function_exists('iconv_strlen')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install PHP iconv Extension'
            ];
        }

        // Checks cURL Extension
        $title = 'PHP - cURL Extension';

        if (function_exists('curl_version')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install Curl Extension'
            ];
        }
        // Checks ZIP Extension
        $title = 'PHP - ZIP Extension';

        if (class_exists('ZipArchive')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install PHP ZIP Extension'
            ];
        }

        // Checks OpenSSL Extension
        $title = 'PHP - OpenSSL Extension';

        if (function_exists('openssl_encrypt')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional - Install OpenSSL Extension for e-mail S/MIME support.'
            ];
        }

        // Checks ImageMagick Extension
        $title = 'PHP - ImageMagick Extension';

        if (class_exists('Imagick', false)) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional'
            ];
        }

        // Checks GraphicsMagick Extension
        $title = 'PHP - GraphicsMagick Extension';

        if (class_exists('Gmagick', false)) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional'
            ];
        }


        $memoryLimit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memoryLimit, $m)) {
            if ($m[2] == 'G') {
                $memoryLimit = $m[1] * 1024 * 1024 * 1024;
            } elseif ($m[2] == 'M') {
                $memoryLimit = $m[1] * 1024 * 1024;
            } elseif ($m[2] == 'K') {
                $memoryLimit = $m[1] * 1024;
            }
        }

        // Check PHP Memory Limit
        $title = 'PHP - Memory Limit (64 MB)';
        if ($memoryLimit >= 64 * 1024 * 1024) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK',
                'hint' => 'Current limit is: ' . Yii::$app->formatter->asShortSize($memoryLimit, 0)
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Increase memory limit in php.ini - Current limit is: ' . Yii::$app->formatter->asShortSize($memoryLimit, 0)
            ];
        }

        // Checks LDAP Extension
        $title = 'PHP - LDAP Support';

        if (LdapHelper::isLdapAvailable()) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional - Install PHP LDAP Extension'
            ];
        }


        // Checks APC(u) Extension
        $title = 'PHP - APC(u) Support';

        if (function_exists('apc_add') || function_exists('apcu_add')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional - Install APCu Extension for APC Caching'
            ];
        }

        // Checks SQLite3 Extension
        $title = 'PHP - SQLite3 Support';

        if (class_exists('SQLite3')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'WARNING',
                'hint' => 'Optional - Install SQLite3 Extension for DB Caching'
            ];
        }

        // Checks PDO MySQL Extension
        $title = 'PHP - PDO MySQL Extension';

        if (extension_loaded('pdo_mysql')) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Install PDO MySQL Extension'
            ];
        }

        // Check Runtime Directory
        $title = 'Permissions - Runtime';

        $path = Yii::getAlias('@runtime');
        if (is_writeable($path)) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the Webserver/PHP!"
            ];
        }

        // Check Assets Directory
        $title = 'Permissions - Assets';

        $path = Yii::getAlias('@webroot/assets');
        if (is_writeable($path)) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the Webserver/PHP!"
            ];
        }

        // Check Uploads Directory
        $title = 'Permissions - Uploads';

        $path = Yii::getAlias('@webroot/uploads');
        if (is_writeable($path)) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the Webserver/PHP!"
            ];
        }

        // Check Custom Modules Directory
        $title = 'Permissions - Module Directory';

        /** @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $path = Yii::getAlias($marketplaceModule->modulesPath);
        if (is_writeable($path)) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the Webserver/PHP!"
            ];
        }
        // Check Custom Modules Directory
        $title = 'Permissions - Dynamic Config';

        $path = Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
        if (!is_file($path)) {
            $path = dirname($path);
        }

        if (is_writeable($path)) {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => Yii::t('base', $title),
                'state' => 'ERROR',
                'hint' => 'Make ' . $path . " writable for the Webserver/PHP!"
            ];
        }

        return $checks;
    }
}
