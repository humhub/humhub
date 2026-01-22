<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\InstallationState;
use humhub\helpers\ArrayHelper;
use humhub\helpers\Html;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\marketplace\Module;
use humhub\services\MailLinkService;
use humhub\services\MigrationService;
use Yii;
use yii\helpers\UnsetArrayValue;
use yii\helpers\Url;

/**
 * SelfTest is a helper class which checks all dependencies of the application.
 *
 * @package humhub.libs
 * @since 0.5
 * @author Luke
 */
class SelfTest
{
    public const PHP_INFO_CACHE_KEY = 'cron_php_info';

    private const STATE_OK = 'OK';
    private const STATE_WARNING = 'WARNING';
    private const STATE_ERROR = 'ERROR';

    private const MIN_MEMORY_LIMIT = 64 * 1024 * 1024;
    private const RECOMMENDED_COLLATION = 'utf8mb4';
    private const RECOMMENDED_ENGINE = 'InnoDB';

    public static function getResults(): array
    {
        return [
            ...self::getPhpChecks(),
            ...self::getDatabaseResults(),
            ...self::getSettingsChecks(),
            ...self::getCronChecks(),
            ...self::getPermissionChecks(),
            ...self::getMarketplaceResults(),
        ];
    }

    private static function getPhpChecks(): array
    {
        return [
            ...self::checkPhpVersion(),
            ...self::checkRequiredExtensions(),
            ...self::checkOptionalExtensions(),
            ...self::checkPhpSettings(),
            ...self::checkDisabledFunctions(),
        ];
    }

    private static function checkPhpVersion(): array
    {
        $version = PHP_VERSION;
        $title = 'PHP - ' . Yii::t('AdminModule.information', 'Version') . ' - ' . $version;

        if (version_compare($version, Yii::$app->minRecommendedPhpVersion, '>=')) {
            return [self::createCheck($title, self::STATE_OK)];
        }

        $isSupported = version_compare($version, Yii::$app->minSupportedPhpVersion, '>=');

        return [self::createCheck(
            $title,
            $isSupported ? self::STATE_WARNING : self::STATE_ERROR,
            Yii::t('AdminModule.information', 'Minimum Version {minVersion}', [
                'minVersion' => Yii::$app->minSupportedPhpVersion
            ])
        )];
    }

    private static function checkRequiredExtensions(): array
    {
        $required = [
            'GD' => fn() => function_exists('gd_info'),
            'GD (JPEG)' => fn() => function_exists('imageCreateFromJpeg'),
            'GD (PNG)' => fn() => function_exists('imageCreateFromPng'),
            'INTL' => fn() => function_exists('collator_create'),
            'EXIF' => fn() => function_exists('exif_read_data'),
            'FileInfo' => fn() => extension_loaded('fileinfo'),
            'Multibyte String' => fn() => function_exists('mb_substr'),
            'iconv' => fn() => function_exists('iconv_strlen'),
            'json' => fn() => extension_loaded('json'),
            'cURL' => fn() => function_exists('curl_version'),
            'ZIP' => fn() => class_exists('ZipArchive'),
            'PDO MySQL' => fn() => extension_loaded('pdo_mysql'),
        ];

        $checks = [];

        foreach ($required as $name => $callback) {
            $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} Extension', ['phpExtension' => $name]);
            $checks[] = $callback()
                ? self::createCheck($title, self::STATE_OK)
                : self::createCheck($title, self::STATE_ERROR, 
                    Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => $name]));
        }

        $checks[] = self::checkIcuVersion();
        $checks[] = self::checkIcuDataVersion();

        return $checks;
    }

    private static function checkOptionalExtensions(): array
    {
        $optional = [
            'XML' => [
                'check' => fn() => function_exists('libxml_get_errors'),
                'hint' => Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'XML']),
            ],
            'OpenSSL' => [
                'check' => fn() => function_exists('openssl_encrypt'),
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - ' .
                    Yii::t('AdminModule.information', 'Install {phpExtension} Extension for e-mail S/MIME support.', ['phpExtension' => 'OpenSSL']),
            ],
            'ImageMagick' => [
                'check' => fn() => class_exists('Imagick', false),
                'hint' => Yii::t('AdminModule.information', 'Optional'),
            ],
            'LDAP' => [
                'check' => fn() => LdapHelper::isLdapAvailable(),
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - ' .
                    Yii::t('AdminModule.information', 'Install {phpExtension} Extension', ['phpExtension' => 'PHP LDAP']),
            ],
            'APC(u)' => [
                'check' => fn() => function_exists('apc_add') || function_exists('apcu_add'),
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - ' .
                    Yii::t('AdminModule.information', 'Install {phpExtension} Extension for APC Caching', ['phpExtension' => 'APCu']),
            ],
            'Sodium' => [
                'check' => fn() => extension_loaded('sodium'),
                'hint' => Yii::t('AdminModule.information', 'Optional') . ' - ' .
                    Yii::t('AdminModule.information', 'Install {phpExtension} Extension for Mercure push live driver', ['phpExtension' => 'Sodium']),
            ],
        ];

        $checks = [];
        foreach ($optional as $name => $config) {
            $title = 'PHP - ' . Yii::t('AdminModule.information', '{phpExtension} ' . 
                (str_contains($name, '(') ? 'Support' : 'Extension'), ['phpExtension' => $name]);

            $checks[] = $config['check']()
                ? self::createCheck($title, self::STATE_OK)
                : self::createCheck($title, self::STATE_WARNING, $config['hint']);
        }

        return $checks;
    }

    private static function checkPhpSettings(): array
    {
        $checks = [];

        $memoryLimit = self::parseMemoryLimit(ini_get('memory_limit'));
        $title = 'PHP - ' . Yii::t('AdminModule.information', 'Memory Limit ({memoryLimit})', ['memoryLimit' => '64 MB']);
        $currentHint = Yii::t('AdminModule.information', 'Current limit is: {currentLimit}', [
            'currentLimit' => Yii::$app->formatter->asShortSize($memoryLimit, 0)
        ]);

        $checks[] = $memoryLimit >= self::MIN_MEMORY_LIMIT
            ? self::createCheck($title, self::STATE_OK, $currentHint)
            : self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', 'Increase memory limit in {fileName}', ['fileName' => 'php.ini']) . ' - ' . $currentHint);

        return $checks;
    }

    private static function checkDisabledFunctions(): array
    {
        $criticalFunctions = [
            'proc_open' => ['severity' => self::STATE_ERROR, 'hint' => 'Required for background processes and command execution'],
            'exec' => ['severity' => self::STATE_WARNING, 'hint' => 'Required for some system operations'],
            'shell_exec' => ['severity' => self::STATE_WARNING, 'hint' => 'Required for command execution features'],
            'set_time_limit' => ['severity' => self::STATE_WARNING, 'hint' => 'Required for script timeout control. May cause issues with long-running operations.'],
        ];

        $importantFunctions = [
            'system' => 'Used for system command execution',
            'passthru' => 'Used for command output streaming',
            'popen' => 'Used for process pipe operations',
            'pcntl_exec' => 'Used for process control',
            'pcntl_alarm' => 'Used for process signaling',

            'unlink' => 'Required for file deletion',
            'rmdir' => 'Required for directory removal',
            'rename' => 'Required for file/directory renaming',
            'chmod' => 'Required for file permission changes',
            'chown' => 'Required for file ownership changes',
            'mkdir' => 'Required for directory creation',
            'symlink' => 'Required for symbolic link creation',
            'move_uploaded_file' => 'Required for file uploads',

            'ini_set' => 'Required for runtime configuration',
            'ini_alter' => 'Required for runtime configuration changes',

            'fsockopen' => 'Required for network connections',
            'pfsockopen' => 'Required for persistent network connections',

            'putenv' => 'Used for environment variable manipulation',
            'getenv' => 'Used for reading environment variables',

            'mail' => 'Required for email functionality',
        ];

        $disabledFunctions = self::getDisabledFunctions();
        $checks = [];

        foreach ($criticalFunctions as $func => $config) {
            $title = 'PHP - ' . Yii::t('AdminModule.information', 'Critical Function') . ' - ' . $func;

            if (!in_array($func, $disabledFunctions, true) && function_exists($func)) {
                $checks[] = self::createCheck($title, self::STATE_OK);
            } else {
                $checks[] = self::createCheck(
                    $title, 
                    $config['severity'], 
                    Yii::t('AdminModule.information', $config['hint'])
                );
            }
        }

        $disabledImportant = [];
        foreach ($importantFunctions as $func => $hint) {
            if (in_array($func, $disabledFunctions, true) || !function_exists($func)) {
                $disabledImportant[$func] = $hint;
            }
        }

        if (!empty($disabledImportant)) {
            $funcList = implode(', ', array_keys($disabledImportant));
            $checks[] = self::createCheck(
                'PHP - ' . Yii::t('AdminModule.information', 'Disabled Functions'),
                self::STATE_WARNING,
                Yii::t('AdminModule.information', 'The following functions are disabled: {functions}. Some features may be limited.', [
                    'functions' => $funcList
                ])
            );
        }

        return $checks;
    }

    /**
     * Get list of disabled PHP functions
     * 
     * @return array List of disabled function names (lowercase)
     */
    private static function getDisabledFunctions(): array
    {
        $disabled = [];

        $disableFunctionsIni = ini_get('disable_functions');
        if ($disableFunctionsIni) {
            $disabled = array_map('trim', explode(',', strtolower($disableFunctionsIni)));
        }

        if (extension_loaded('suhosin')) {
            $suhosinDisabled = ini_get('suhosin.executor.func.blacklist');
            if ($suhosinDisabled) {
                $suhosinFuncs = array_map('trim', explode(',', strtolower($suhosinDisabled)));
                $disabled = array_merge($disabled, $suhosinFuncs);
            }
        }

        $functionsToVerify = ['set_time_limit', 'ini_set', 'ini_alter'];
        foreach ($functionsToVerify as $func) {
            if (!in_array($func, $disabled, true)) {
                if (function_exists($func) && !is_callable($func)) {
                    $disabled[] = $func;
                }
            }
        }

        return array_unique($disabled);
    }

    private static function getSettingsChecks(): array
    {
        if (Yii::$app->controller->id === 'setup') {
            return [];
        }

        $checks = [];

        if (Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            $checks[] = self::checkPrettyUrls();
            $checks[] = self::checkBaseUrl();
        }

        return $checks;
    }

    private static function getCronChecks(): array
    {
        if (!Yii::$app->cache->exists(self::PHP_INFO_CACHE_KEY)) {
            return [];
        }

        $cronInfo = Yii::$app->cache->get(self::PHP_INFO_CACHE_KEY);
        $checks = [];

        if ($version = ArrayHelper::getValue($cronInfo, 'version')) {
            $title = Yii::t('AdminModule.information', 'Settings') . ' - ' . 
                Yii::t('AdminModule.information', 'Web Application and Cron uses the same PHP version');

            $webVersion = phpversion();

            if ($version === $webVersion) {
                $checks[] = self::createCheck($title, self::STATE_OK);
            } else {
                $cronMeetsMin = version_compare($version, Yii::$app->minSupportedPhpVersion, '>=');
                $cronMeetsRecommended = version_compare($version, Yii::$app->minRecommendedPhpVersion, '>=');

                if (!$cronMeetsMin) {
                    $state = self::STATE_ERROR;
                    $hint = Yii::t('AdminModule.information', 'Cron PHP version {cronPhpVersion} is below minimum required {minVersion}. Update cron to use PHP {webPhpVersion} or higher.', [
                        'cronPhpVersion' => $version,
                        'minVersion' => Yii::$app->minSupportedPhpVersion,
                        'webPhpVersion' => $webVersion
                    ]);
                } elseif (!$cronMeetsRecommended) {
                    $state = self::STATE_WARNING;
                    $hint = Yii::t('AdminModule.information', 'Cron uses PHP {cronPhpVersion} (below recommended {recommendedVersion}). Web uses {webPhpVersion}. Update cron job to use the same PHP version.', [
                        'cronPhpVersion' => $version,
                        'recommendedVersion' => Yii::$app->minRecommendedPhpVersion,
                        'webPhpVersion' => $webVersion
                    ]);
                } else {
                    $state = self::STATE_WARNING;
                    $hint = Yii::t('AdminModule.information', 'Web Application PHP version: `{webPhpVersion}`, Cron PHP Version: `{cronPhpVersion}`', [
                        'webPhpVersion' => $webVersion,
                        'cronPhpVersion' => $version
                    ]);
                }

                $checks[] = self::createCheck($title, $state, $hint);
            }
        }

        if ($user = ArrayHelper::getValue($cronInfo, 'user')) {
            $title = Yii::t('AdminModule.information', 'Settings') . ' - ' . 
                Yii::t('AdminModule.information', 'Web Application and Cron uses the same user');

            $webUser = get_current_user();

            if ($user === $webUser) {
                $checks[] = self::createCheck($title, self::STATE_OK);
            } else {
                $checks[] = self::createCheck(
                    $title,
                    self::STATE_WARNING,
                    Yii::t('AdminModule.information', 'Web Application user: `{webUser}`, Cron user: `{cronUser}`', [
                        'webUser' => $webUser,
                        'cronUser' => $user
                    ])
                );
            }
        }

        return $checks;
    }

    private static function getPermissionChecks(): array
    {
        $paths = [
            'Runtime' => '@runtime',
            'Assets' => '@webroot/assets',
            'Uploads' => '@webroot/uploads',
            'Uploads - File' => '@webroot/uploads/file',
            'Profile Image' => '@webroot/uploads/profile_image',
            'Module Directory' => Yii::$app->getModule('marketplace')->modulesPath ?? '@app/modules',
        ];

        $checks = [];

        foreach ($paths as $name => $alias) {
            $path = realpath(Yii::getAlias($alias));
            $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', $name);

            $checks[] = is_writable($path)
                ? self::createCheck($title, self::STATE_OK)
                : self::createCheck($title, self::STATE_ERROR,
                    Yii::t('AdminModule.information', 'Make {filePath} writable for the Webserver/PHP!', ['filePath' => $path]));
        }

        return $checks;
    }

    public static function getDatabaseResults(array $checks = []): array
    {
        $driver = self::getDatabaseDriverInfo();

        if (!$driver) {
            return $checks;
        }

        $checks[] = self::checkDatabaseDriver($driver);
        $checks[] = self::checkDatabaseVersion($driver);
        $checks[] = self::checkDatabaseCollation($driver);

        if (Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            $checks = [...$checks, ...self::checkTableSettings($driver)];
        }

        if (Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            $checks[] = self::checkPendingMigrations();
        }

        return $checks;
    }

    public static function getMarketplaceResults(array $checks = []): array
    {
        $prefix = Yii::t('AdminModule.information', 'HumHub') . ' - ';

        $title = $prefix . Yii::t('AdminModule.information', 'Marketplace API Connection');
        $checks[] = empty(HumHubAPI::getLatestHumHubVersion(false))
            ? self::createCheck($title, self::STATE_WARNING)
            : self::createCheck($title, self::STATE_OK);

        if (!Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            return $checks;
        }

        $checks = [...$checks, ...self::checkModules($prefix)];

        $checks[] = self::checkPushService($prefix);

        $checks[] = self::checkConfigFile($prefix);

        return $checks;
    }

    private static function createCheck(string $title, string $state, ?string $hint = null): array
    {
        return array_filter([
            'title' => $title,
            'state' => $state,
            'hint' => $hint,
        ]);
    }

    private static function parseMemoryLimit(string $limit): int
    {
        if (preg_match('/^(\d+)([GMK]?)$/i', $limit, $m)) {
            $value = (int)$m[1];
            return match(strtoupper($m[2] ?? '')) {
                'G' => $value * 1024 * 1024 * 1024,
                'M' => $value * 1024 * 1024,
                'K' => $value * 1024,
                default => $value,
            };
        }

        return 0;
    }

    private static function checkIcuVersion(): array
    {
        $version = defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '0';
        $minVersion = '49.0';
        $title = 'PHP - INTL - ' . Yii::t('AdminModule.information', 'ICU Version ({version})', ['version' => $version]);

        return version_compare($version, $minVersion, '>=')
            ? self::createCheck($title, self::STATE_OK)
            : self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', 'ICU {icuMinVersion} or higher is required', ['icuMinVersion' => $minVersion]));
    }

    private static function checkIcuDataVersion(): array
    {
        $version = defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : '0';
        $minVersion = '4.8.1';
        $title = 'PHP - INTL - ' . Yii::t('AdminModule.information', 'ICU Data Version ({version})', ['version' => $version]);

        return version_compare($version, $minVersion, '>=')
            ? self::createCheck($title, self::STATE_OK)
            : self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', 'ICU Data {icuMinVersion} or higher is required', ['icuMinDataVersion' => $minVersion]));
    }

    private static function checkPrettyUrls(): array
    {
        $title = Yii::t('AdminModule.information', 'Settings') . ' - ' . Yii::t('AdminModule.information', 'Pretty URLs');

        return Yii::$app->urlManager->enablePrettyUrl
            ? self::createCheck($title, self::STATE_OK)
            : self::createCheck($title, self::STATE_WARNING,
                Html::a(Yii::t('AdminModule.information', 'HumHub Documentation'), 'https://docs.humhub.org/docs/admin/installation#pretty-urls'));
    }

    private static function checkBaseUrl(): array
    {
        $title = Yii::t('AdminModule.information', 'Settings') . ' - ' . Yii::t('AdminModule.information', 'Base URL');
        $current = Url::base(true);
        $saved = Yii::$app->settings->get('baseUrl');

        return $current === $saved
            ? self::createCheck($title, self::STATE_OK)
            : self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', 'Detected URL: {currentBaseUrl}', ['currentBaseUrl' => $current]));
    }

    private static function checkDatabaseDriver(array $driver): array
    {
        $title = Yii::t('AdminModule.information', 'Database driver - {driver}', ['driver' => $driver['title']]);

        if ($driver['isSupportedDriver']) {
            return self::createCheck($title, self::STATE_OK);
        }

        $allowed = array_column(self::getSupportedDatabaseDrivers(), 'title');

        return self::createCheck($title, self::STATE_WARNING,
            Yii::t('AdminModule.information', 'Supported drivers: {drivers}', ['drivers' => implode(', ', $allowed)]));
    }

    private static function checkDatabaseVersion(array $driver): array
    {
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Version') . ' - ' . $driver['version'];

        return $driver['isAllowedVersion']
            ? self::createCheck($title, self::STATE_OK)
            : self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', 'Minimum Version {minVersion}', ['minVersion' => $driver['minVersion']]));
    }

    private static function checkDatabaseCollation(array $driver): array
    {
        $charset = Yii::$app->getDb()->createCommand('SELECT @@collation_database')->queryScalar();
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Database collation') . ' - ' . $charset;

        return str_starts_with(strtolower($charset), self::RECOMMENDED_COLLATION)
            ? self::createCheck($title, self::STATE_OK)
            : self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', 'Recommended collation is {collation}', ['collation' => self::RECOMMENDED_COLLATION]));
    }

    private static function checkTableSettings(array $driver): array
    {
        $tables = Yii::$app->getDb()->createCommand('SHOW TABLE STATUS WHERE Comment != "VIEW"')->queryAll();

        $collations = [];
        $wrongCollations = [];
        $engines = [];
        $wrongEngines = [];

        foreach ($tables as $table) {
            $name = $table['Name'];
            $collation = $table['Collation'] ?? '';
            $engine = $table['Engine'] ?? '';

            $collations[$name] = $collation;

            if (!str_starts_with(strtolower($collation), self::RECOMMENDED_COLLATION)) {
                $wrongCollations[] = $name;
            }

            if (!in_array($engine, $engines)) {
                $engines[] = $engine;
            }

            if (strtolower($engine) !== strtolower(self::RECOMMENDED_ENGINE)) {
                $wrongEngines[] = $name;
            }
        }

        return [
            self::checkTableCollations($driver, $collations, $wrongCollations),
            self::checkTableEngines($driver, $engines, $wrongEngines),
        ];
    }

    private static function checkTableCollations(array $driver, array $collations, array $wrong): array
    {
        $uniqueCollations = array_unique(array_values($collations));
        $collationList = implode(', ', $uniqueCollations);
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Table collations') . ' - ' . $collationList;

        if (empty($wrong) && count($uniqueCollations) === 1) {
            return self::createCheck($title, self::STATE_OK);
        }

        $hints = [];

        if (count($uniqueCollations) > 1) {
            $collationDetails = [];
            foreach ($collations as $table => $collation) {
                $collationDetails[] = "{$table}={$collation}";
            }
            $hints[] = Yii::t('AdminModule.information', 'Different table collations detected: {tables}', [
                'tables' => implode(', ', $collationDetails)
            ]);
        }

        if (!empty($wrong)) {
            $hints[] = Yii::t('AdminModule.information', 'Recommended collation is {collation} for the tables: {tables}', [
                'collation' => self::RECOMMENDED_COLLATION,
                'tables' => implode(', ', $wrong)
            ]);
        }

        return self::createCheck($title, self::STATE_WARNING, implode('. ', $hints));
    }

    private static function checkTableEngines(array $driver, array $engines, array $wrong): array
    {
        $title = $driver['title'] . ' - ' . Yii::t('AdminModule.information', 'Table engines') . ' - ' . implode(', ', $engines);

        if (empty($wrong)) {
            return self::createCheck($title, self::STATE_OK);
        }

        if (count($engines) > 1) {
            $title .= ' - ' . Yii::t('AdminModule.information', 'Varying table engines are not supported.');
        }

        return self::createCheck(
            $title,
            count($engines) > 1 ? self::STATE_ERROR : self::STATE_WARNING,
            Yii::t('AdminModule.information', 'Recommended engine is {engine} for the tables: {tables}', [
                'engine' => self::RECOMMENDED_ENGINE,
                'tables' => implode(', ', $wrong)
            ])
        );
    }

    private static function checkPendingMigrations(): array
    {
        $title = Yii::t('AdminModule.information', 'Database') . ' - ';
        $migrations = MigrationService::create()->getPendingMigrations();

        return empty($migrations)
            ? self::createCheck($title . Yii::t('AdminModule.information', 'No pending migrations'), self::STATE_OK)
            : self::createCheck(
                $title . Yii::t('AdminModule.information', 'New migrations should be applied: {migrations}', [
                    'migrations' => implode(', ', $migrations)
                ]),
                self::STATE_ERROR
            );
    }

    private static function checkModules(string $prefix): array
    {
        $modules = Yii::$app->moduleManager->getModules();
        $deprecated = [];
        $custom = [];

        foreach ($modules as $module) {
            $online = $module->getOnlineModule();
            if ($online === null) {
                $custom[] = $module->name;
            } elseif ($online->isDeprecated) {
                $deprecated[] = $module->name;
            }
        }

        $checks = [];

        if (!empty($deprecated)) {
            $checks[] = self::createCheck(
                $prefix . Yii::t('AdminModule.information', 'Deprecated Modules ({modules})', ['modules' => implode(', ', $deprecated)]),
                self::STATE_ERROR,
                Yii::t('AdminModule.information', 'The module(s) are no longer maintained and should be uninstalled.')
            );
        }

        if (!empty($custom)) {
            $checks[] = self::createCheck(
                $prefix . Yii::t('AdminModule.information', 'Custom Modules ({modules})', ['modules' => implode(', ', $custom)]),
                self::STATE_WARNING,
                Yii::t('AdminModule.information', 'Must be updated manually. Check compatibility with newer HumHub versions before updating.')
            );
        }

        return $checks;
    }

    private static function checkPushService(string $prefix): array
    {
        $title = $prefix . Yii::t('AdminModule.information', 'Mobile App - Push Service');

        if (!self::isPushModuleAvailable()) {
            return self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', '"Push Notifications (Firebase)" module and setup of Firebase API Key required'));
        }

        if (!MailLinkService::instance()->isConfigured()) {
            return self::createCheck($title, self::STATE_WARNING,
                Yii::t('AdminModule.information', 'Enable <a href="{url}">Link Redirection Service</a>', [
                    'url' => Url::to(['/admin/setting/mailing-server'])
                ]));
        }

        return self::createCheck($title, self::STATE_OK);
    }

    private static function checkConfigFile(string $prefix): array
    {
        $title = $prefix . Yii::t('AdminModule.information', 'Configuration File');

        $legacy = array_keys(ArrayHelper::flatten(self::getLegacyConfigSettings()));
        $found = [];

        foreach (array_keys(ArrayHelper::flatten(Yii::$app->loadedAppConfig)) as $config) {
            foreach ($legacy as $legacyConfig) {
                if (str_starts_with((string)$config, (string)$legacyConfig)) {
                    $found[] = $config;
                }
            }
        }

        return empty($found)
            ? self::createCheck($title, self::STATE_OK)
            : self::createCheck($title, self::STATE_ERROR,
                Yii::t('AdminModule.information', 'The configuration file contains legacy settings. Invalid options: {options}', [
                    'options' => implode(', ', $found)
                ]));
    }

    public static function getSupportedDatabaseDrivers(): array
    {
        return [
            'mysql' => ['title' => 'MySQL', 'minVersion' => '5.7'],
            'mariadb' => ['title' => 'MariaDB', 'minVersion' => '10.1'],
        ];
    }

    public static function getDatabaseDriverInfo(): array|false
    {
        if (!Yii::$app->getDb()->getIsActive()) {
            return false;
        }

        $driver = ['version' => Yii::$app->getDb()->getServerVersion()];
        $supported = self::getSupportedDatabaseDrivers();

        if (preg_match('/(' . implode('|', array_keys($supported)) . ')/i', $driver['version'], $match)) {
            $driver['name'] = strtolower($match[1]);
        } else {
            $driver['name'] = Yii::$app->getDb()->getDriverName();
        }

        $driver['isSupportedDriver'] = isset($supported[$driver['name']]);

        if (!$driver['isSupportedDriver']) {
            return $driver;
        }

        $driver = [...$driver, ...$supported[$driver['name']]];
        $driver['isAllowedVersion'] = version_compare($driver['version'], $driver['minVersion'], '>=');

        if (!$driver['isAllowedVersion'] && preg_match_all('/((\d+\.?)+)-/', (string)$driver['version'], $matches)) {
            foreach ($matches[1] as $ver) {
                if (version_compare($ver, $driver['minVersion'], '>=')) {
                    $driver['isAllowedVersion'] = true;
                    break;
                }
            }
        }

        return $driver;
    }

    public static function isPushModuleAvailable(): bool
    {
        $module = Yii::$app->getModule('fcm-push');

        return $module instanceof \humhub\modules\fcmPush\Module && $module->getIsEnabled();
    }

    public static function getLegacyConfigSettings(): array
    {
        return [
            'modules' => [
                'search' => new UnsetArrayValue(),
                'directory' => new UnsetArrayValue(),
            ],
            'components' => [
                'formatterApp' => new UnsetArrayValue(),
                'search' => new UnsetArrayValue(),
            ],
        ];
    }
}
