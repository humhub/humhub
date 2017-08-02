<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
Yii::setAlias('@webroot', realpath(__DIR__ . '/../../../'));
Yii::setAlias('@app', '@webroot/protected');
Yii::setAlias('@humhub', '@app/humhub');
Yii::setAlias('@config', '@app/config');

$config = [
    'name' => 'HumHub',
    'version' => '1.2.2',
    'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR,
    'bootstrap' => ['log', 'humhub\components\bootstrap\ModuleAutoLoader', 'queue'],
    'sourceLanguage' => 'en',
    'components' => [
        'moduleManager' => [
            'class' => '\humhub\components\ModuleManager'
        ],
        'notification' => [
            'class' => 'humhub\modules\notification\components\NotificationManager',
            'targets' => [
                [
                    'class' => 'humhub\modules\notification\targets\WebTarget',
                    'renderer' => ['class' => 'humhub\modules\notification\renderer\WebRenderer']
                ],
                [
                    'class' => 'humhub\modules\notification\targets\MailTarget',
                    'renderer' => ['class' => 'humhub\modules\notification\renderer\MailRenderer']
                ],
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_SERVER'],
                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:404', 'yii\web\HttpException:403', 'yii\web\HttpException:401'],
                    'logVars' => ['_GET', '_SERVER'],
                ],
            ],
        ],
        'search' => array(
            'class' => 'humhub\modules\search\engine\ZendLuceneSearch',
        ),
        'settings' => array(
            'class' => 'humhub\components\SettingsManager',
            'moduleId' => 'base',
        ),
        'i18n' => [
            'class' => 'humhub\components\i18n\I18N',
            'translations' => [
                'base' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@humhub/messages'
                ],
                'security' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@humhub/messages'
                ],
                'error' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@humhub/messages'
                ],
                'widgets_views_markdownEditor' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@humhub/messages'
                ],
            ],
        ],
        'formatter' => [
            'class' => 'humhub\components\i18n\Formatter',
        ],
        /**
         * Deprecated
         */
        'formatterApp' => [
            'class' => 'yii\i18n\Formatter',
        ],
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'mailer' => [
            'class' => 'humhub\components\mail\Mailer',
            'viewPath' => '@humhub/views/mail',
            'view' => [
                'class' => '\yii\web\View',
                'theme' => [
                    'class' => '\humhub\components\Theme',
                    'name' => 'HumHub'
                ],
            ],
        ],
        'assetManager' => [
            'class' => '\humhub\components\AssetManager',
            'appendTimestamp' => true,
            'bundles' => require(__DIR__ . '/' . (YII_ENV_PROD || YII_ENV_TEST ? 'assets-prod.php' : 'assets-dev.php')),
        ],
        'view' => [
            'class' => '\humhub\components\View',
            'theme' => [
                'class' => '\humhub\components\Theme',
                'name' => 'HumHub',
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=humhub',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'on afterOpen' => ['humhub\libs\Helpers', 'SqlMode'],
        ],
        'authClientCollection' => [
            'class' => 'humhub\modules\user\authclient\Collection',
            'clients' => [],
        ],
        'queue' => [
            'class' => 'humhub\components\queue\driver\Sync',
        ],
        'live' => [
            'class' => 'humhub\modules\live\components\Sender',
            'driver' => [
                'class' => 'humhub\modules\live\driver\Database',
            ],
        ],
    ],
    'params' => [
        'installed' => false,
        'databaseInstalled' => false,
        'dynamicConfigFile' => '@config/dynamic.php',
        'moduleAutoloadPaths' => ['@app/modules', '@humhub/modules'],
        'moduleMarketplacePath' => '@app/modules',
        'availableLanguages' => [
            'en' => 'English (US)',
            'en_gb' => 'English (UK)',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
            'pt' => 'Português',
            'pt_br' => 'Português do Brasil',
            'es' => 'Español',
            'ca' => 'Català',
            'it' => 'Italiano',
            'th' => 'ไทย',
            'tr' => 'Türkçe',
            'ru' => 'Русский',
            'uk' => 'українська',
            'el' => 'Ελληνικά',
            'ja' => '日本語',
            'hu' => 'Magyar',
            'nb_no' => 'Nnorsk bokmål',
            'zh_cn' => '中文(简体)',
            'zh_tw' => '中文(台灣)',
            'an' => 'Aragonés',
            'vi' => 'Tiếng Việt',
            'sv' => 'Svenska',
            'cs' => 'čeština',
            'da' => 'dansk',
            'uz' => 'Ўзбек',
            'fa_ir' => 'فارسی',
            'bg' => 'български',
            'sk' => 'slovenčina',
            'ro' => 'română',
            'ar' => 'العربية/عربي‎‎',
            'ko' => '한국어',
            'id' => 'Bahasa Indonesia',
            'lt' => 'lietuvių kalba',
            'ht' => 'Kreyòl ayisyen',
            'lv' => 'Latvijas',
        ],
        'ldap' => [
            // LDAP date field formats
            'dateFields' => [
            //'birthday' => 'Y.m.d'
            ],
        ],
        'formatter' => [
            // Default date format, used especially in DatePicker widgets
            // Deprecated: Use Yii::$app->formatter->dateInputFormat instead.
            'defaultDateFormat' => 'short',
            // Seconds before switch from relative time to date format
            // Set to false to always use relative time in TimeAgo Widget
            'timeAgoBefore' => 172800,
            // Use static timeago instead of timeago js
            'timeAgoStatic' => false,
            // Seconds before hide time from timeago date
            // Set to false to always display time
            'timeAgoHideTimeAfter' => 259200,
        // Optional: Callback for TimageAgo FullDateFormat
        //'timeAgoFullDateCallBack' => function($timestamp) {
        //    return 'formatted';
        //}
        ],
        'humhub' => [
            // Marketplace / New Version Check
            'apiEnabled' => true,
            'apiUrl' => 'https://api.humhub.com',
        ],
        'search' => [
            'zendLucenceDataDir' => '@runtime/searchdb',
        ],
        'curl' => [
            // Check SSL certificates on cURL requests
            'validateSsl' => true,
        ],
        // Allowed languages limitation (optional)
        'allowedLanguages' => [],
        'defaultPermissions' => [],
        'tour' => [
            'acceptableNames' => ['interface', 'administration', 'profile', 'spaces']
        ],
        'enablePjax' => true,
    ]
];

return $config;
