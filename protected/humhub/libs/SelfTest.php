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
     * @return array
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
        $title = 'PHP - ' . Yii::t('AdminModule.information', 'Version') . ' - ' . PHP_VERSION;
        if (version_compare(PHP_VERSION, Yii::$app->minRecommendedPhpVersion, '>=')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } elseif (version_compare(PHP_VERSION, Yii::$app->minSupportedPhpVersion, '>=')) {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Minimum Version {minVersion}', ['minVersion' => Yii::$app->minSupportedPhpVersion])
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Minimum Version {minVersion}', ['minVersion' => Yii::$app->minSupportedPhpVersion])
            ];
        }

        // Checks GD Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'GD']);
        if (function_exists('gd_info')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'GD'])
            ];
        }

        // Checks GD JPEG Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'GD'])
            . ' - ' . Yii::t('AdminModule.information', '{imageExtension} Support', ['imageExtension' => 'JPEG']);
        if (function_exists('imageCreateFromJpeg')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'GD'])
                    . ' - ' . Yii::t('AdminModule.information', '{imageExtension} Support', ['imageExtension' => 'JPEG'])
            ];
        }

        // Checks GD PNG Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'GD'])
            . ' - ' . Yii::t('AdminModule.information', '{imageExtension} Support', ['imageExtension' => 'PNG']);
        if (function_exists('imageCreateFromPng')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'GD'])
                    . ' - ' . Yii::t('AdminModule.information', '{imageExtension} Support', ['imageExtension' => 'PNG'])
            ];
        }

        // Checks INTL Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'INTL']);
        if (function_exists('collator_create')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'INTL'])
            ];
        }

        // Check ICU Version
        $icuVersion = defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : 0;
        $icuMinVersion = '4.8.1';
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'INTL'])
            . ' - ' . Yii::t('AdminModule.information', 'ICU Version ({version})', ['version' => $icuVersion]);
        if (version_compare($icuVersion, $icuMinVersion, '>=')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'ICU {icuMinVersion} or higher is required', ['icuMinVersion' => $icuMinVersion])
            ];
        }

        // Check ICU Data Version
        $icuDataVersion = (defined('INTL_ICU_DATA_VERSION')) ? INTL_ICU_DATA_VERSION : 0;
        $icuMinDataVersion = '4.8.1';
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'INTL'])
            . ' - ' . Yii::t('AdminModule.information', 'ICU Data Version ({version})', ['version' => $icuDataVersion]);
        if (version_compare($icuDataVersion, $icuMinDataVersion, '>=')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'ICU Data {icuMinVersion} or higher is required', ['icuMinDataVersion' => $icuMinDataVersion])
            ];
        }

        // Checks EXIF Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'EXIF']);
        if (function_exists('exif_read_data')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'EXIF'])
            ];
        }

        // Checks XML Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'XML']);
        if (function_exists('libxml_get_errors')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'XML'])
            ];
        }

        // Check FileInfo Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'FileInfo']);
        if (extension_loaded('fileinfo')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'FileInfo'])
            ];
        }

        // Checks Multibyte Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', 'Multibyte String Functions');
        if (function_exists('mb_substr')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'PHP Multibyte'])
            ];
        }

        // Checks iconv Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'iconv']);
        if (function_exists('iconv_strlen')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'PHP iconv'])
            ];
        }

        // Checks cURL Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'cURL']);
        if (function_exists('curl_version')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'Curl'])
            ];
        }
        // Checks ZIP Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'ZIP']);
        if (class_exists('ZipArchive')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'PHP ZIP'])
            ];
        }

        // Checks OpenSSL Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'OpenSSL']);
        if (function_exists('openssl_encrypt')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - '
                    . Yii::t('AdminModule.information', 'Install {phpExtension} Extension for e-mail S/MIME support.', ['phpExtension' => 'OpenSSL'])
            ];
        }

        // Checks ImageMagick Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'ImageMagick']);
        if (class_exists('Imagick', false)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Optional')
            ];
        }

        // Checks GraphicsMagick Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'GraphicsMagick']);
        if (class_exists('Gmagick', false)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Optional')
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
        $title = 'PHP - ' . Yii::t('AdminModule.information', 'Memory Limit ({memoryLimit})', ['memoryLimit' => '64 MB']);
        $currentLimitHint = Yii::t('AdminModule.information', 'Current limit is: {currentLimit}', ['currentLimit' => Yii::$app->formatter->asShortSize($memoryLimit, 0)]);
        if ($memoryLimit >= 64 * 1024 * 1024) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK',
                'hint' => $currentLimitHint
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Increase memory limit in {fileName}', ['fileName' => 'php.ini']) . ' - ' . $currentLimitHint
            ];
        }

        // Checks LDAP Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Support', ['phpExtension' => 'LDAP']);
        if (LdapHelper::isLdapAvailable()) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - '
                    . Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'PHP LDAP'])
            ];
        }


        // Checks APC(u) Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Support', ['phpExtension' => 'APC(u)']);
        if (function_exists('apc_add') || function_exists('apcu_add')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - '
                    . Yii::t('AdminModule.information', 'Install {phpExtension} Extension for APC Caching', ['phpExtension' => 'APCu'])
            ];
        }

        // Checks SQLite3 Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Support', ['phpExtension' => 'SQLite3']);
        if (class_exists('SQLite3')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - '
                    . Yii::t('AdminModule.information', 'Install {phpExtension} Extension for DB Caching', ['phpExtension' => 'SQLite3'])
            ];
        }

        // Checks PDO MySQL Extension
        $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => 'PDO MySQL']);
        if (extension_loaded('pdo_mysql')) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'PDO MySQL'])
            ];
        }

        // Checks Database Data
        $checks = self::getDatabaseResults($checks);

        // Timezone Setting
        if (Yii::$app->controller->id != 'setup') {
            $dbConnectionTime = TimezoneHelper::getDatabaseConnectionTime();
            $timeDiffMargin = 60;
            $timeDiff = abs($dbConnectionTime->getTimestamp() - time());

            $title = Yii::t('AdminModule.information', 'Settings') . ' - ' . Yii::t('AdminModule.information', 'Time zone');
            if ($timeDiff < $timeDiffMargin) {
                $checks[] = [
                    'title' => $title,
                    'state' => 'OK'
                ];
            } else {
                $checks[] = [
                    'title' => $title,
                    'state' => 'WARNING',
                    'hint' => Yii::t('AdminModule.information', 'Database connection time: {dbTime} - Configured time zone: {time}',
                        [
                            'dbTime' => Yii::$app->formatter->asTime($dbConnectionTime, 'short'),
                            'time' => Yii::$app->formatter->asTime(time(), 'short'),
                        ]
                    ),
                ];
            }
        }

        // Check Runtime Directory
        $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', 'Runtime');
        $path = Yii::getAlias('@runtime');
        if (is_writeable($path)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Make {filePath} writable for the Webserver/PHP!', ['filePath' => $path])
            ];
        }

        // Check Assets Directory
        $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', 'Assets');
        $path = Yii::getAlias('@webroot/assets');
        if (is_writeable($path)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Make {filePath} writable for the Webserver/PHP!', ['filePath' => $path])
            ];
        }

        // Check Uploads Directory
        $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', 'Uploads');
        $path = Yii::getAlias('@webroot/uploads');
        if (is_writeable($path)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Make {filePath} writable for the Webserver/PHP!', ['filePath' => $path])
            ];
        }

        // Check Profile Image Directory
        $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', 'Profile Image');
        $path = Yii::getAlias('@webroot/uploads/profile_image');
        if (is_writeable($path)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Make {filePath} writable for the Webserver/PHP!', ['filePath' => $path])
            ];
        }

        // Check Custom Modules Directory
        $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', 'Module Directory');
        /** @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $path = Yii::getAlias($marketplaceModule->modulesPath);
        if (is_writeable($path)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Make {filePath} writable for the Webserver/PHP!', ['filePath' => $path])
            ];
        }
        // Check Custom Modules Directory
        $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', 'Dynamic Config');
        $path = Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
        if (!is_file($path)) {
            $path = dirname($path);
        }

        if (is_writeable($path)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'ERROR',
                'hint' => Yii::t('AdminModule.information', 'Make {filePath} writable for the Webserver/PHP!', ['filePath' => $path])
            ];
        }

        return $checks;
    }

    /**
     * Get Results of the Application SelfTest for Database part.
     *
     * Fields
     *  - title
     *  - state (OK, WARNING or ERROR)
     *  - hint
     *
     * @param array Results initialized before
     * @return array
     */
    public static function getDatabaseResults($checks = [])
    {
        $driver = self::getDatabaseDriverInfo();

        if (!$driver) {
            return $checks;
        }

        $recommendedCollation = 'utf8mb4';
        $recommendedEngine = 'InnoDB';

        // Checks Database Driver
        $title = Yii::t('AdminModule.information', 'Database driver - {driver}', ['driver' => $driver['title']]);
        if ($driver['isSupportedDriver']) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $allowedDriverTitles = [];
            foreach(self::getSupportedDatabaseDrivers() as $allowedDriver) {
                $allowedDriverTitles[] = $allowedDriver['title'];
            }
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Supported drivers: {drivers}', ['drivers' => implode(', ', $allowedDriverTitles)]),
            ];
            return $checks;
            // Do NOT check below because the database driver is not supported.
        }

        // Checks Database Version
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Version') . ' - ' . $driver['version'];

        if ($driver['isAllowedVersion']) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Minimum Version {minVersion}', ['minVersion' => $driver['minVersion']])
            ];
        }

        // Checks Database Collation
        $dbCharset = Yii::$app->getDb()->createCommand('SELECT @@collation_database')->queryScalar();
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Database collation') . ' - ' . $dbCharset;

        if (stripos($dbCharset, $recommendedCollation) === 0) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Recommended collation is {collation}', ['collation' => $recommendedCollation]),
            ];
        }

        // Find collations and engines of all tables
        $dbTables = Yii::$app->getDb()->createCommand('SHOW TABLE STATUS')->queryAll();
        $tableCollations = [];
        $tablesWithNotRecommendedCollations = [];
        $tableEngines = [];
        $tablesWithNotRecommendedEngines = [];
        foreach ($dbTables as $dbTable) {
            if (!in_array($dbTable['Collation'], $tableCollations)) {
                $tableCollations[] = $dbTable['Collation'];
            }
            if (stripos($dbTable['Collation'], $recommendedCollation) !== 0) {
                $tablesWithNotRecommendedCollations[] = $dbTable['Name'];
            }
            if (!in_array($dbTable['Engine'], $tableEngines)) {
                $tableEngines[] = $dbTable['Engine'];
            }
            if (stripos($dbTable['Engine'], $recommendedEngine) !== 0) {
                $tablesWithNotRecommendedEngines[] = $dbTable['Name'];
            }
        }

        // Checks Table Collations
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Table collations') . ' - ' . implode(', ', $tableCollations);

        if (empty($tablesWithNotRecommendedCollations)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            $checks[] = [
                'title' => $title,
                'state' => 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Recommended collation is {collation} for the tables: {tables}', [
                    'collation' => $recommendedCollation,
                    'tables' => implode(', ', $tablesWithNotRecommendedCollations),
                ])
            ];
        }

        // Checks Table Engines
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Table engines') . ' - ' . implode(', ', $tableEngines);

        if (empty($tablesWithNotRecommendedEngines)) {
            $checks[] = [
                'title' => $title,
                'state' => 'OK'
            ];
        } else {
            if (count($tableEngines) > 1) {
                $title .= ' - ' . Yii::t('AdminModule.information', 'Varying table engines are not supported.');
            }
            $checks[] = [
                'title' => $title,
                'state' => count($tableEngines) > 1 ? 'ERROR' : 'WARNING',
                'hint' => Yii::t('AdminModule.information', 'Recommended engine is {engine} for the tables: {tables}', [
                    'engine' => $recommendedEngine,
                    'tables' => implode(', ', $tablesWithNotRecommendedEngines),
                ])
            ];
        }

        return $checks;
    }

    /**
     * @return array
     */
    public static function getSupportedDatabaseDrivers()
    {
        return [
            'mysql' => ['title' => 'MySQL', 'minVersion' => '5.7'],
            'mariadb' => ['title' => 'MariaDB', 'minVersion' => '10.1'],
        ];
    }

    /**
     * @return array|false
     */
    public static function getDatabaseDriverInfo()
    {
        if (!Yii::$app->getDb()->getIsActive()) {
            return false;
        }

        $driver = ['version' => Yii::$app->getDb()->getServerVersion()];

        $supportedDrivers = self::getSupportedDatabaseDrivers();

        // Firstly parse driver name from version:
        if (preg_match('/(' . implode('|', array_keys($supportedDrivers)). ')/i', $driver['version'], $verMatch)) {
            $driver['name'] = strtolower($verMatch[1]);
        } else {
            $driver['name'] = Yii::$app->getDb()->getDriverName();
        }

        $driver['isSupportedDriver'] = isset($supportedDrivers[$driver['name']]);

        if (!$driver['isSupportedDriver']) {
            return $driver;
            // Below info can be initialized only for supported drivers.
        }

        // Append title and min version
        $driver = array_merge($driver, $supportedDrivers[$driver['name']]);

        // Check min allowed version
        $driver['isAllowedVersion'] = version_compare($driver['version'], $driver['minVersion'], '>=');
        // Otherwise try to compare complex version like 5.5.5-10.3.27-MariaDB-0+deb10u1
        if (!$driver['isAllowedVersion'] &&
            preg_match_all('/((\d+\.?)+)-/', $driver['version'], $verMatches)) {
            foreach ($verMatches[1] as $verMatch) {
                if (version_compare($verMatch, $driver['minVersion'], '>=')) {
                    // If at least one version is allowed
                    $driver['isAllowedVersion'] = true;
                    break;
                }
            }
        }

        return $driver;
    }
}
