<?php

Yii::setAlias('@webroot', realpath(__DIR__ . '/../../../'));

Yii::setAlias('@app', '@webroot/protected');
Yii::setAlias('@humhub', '@app/humhub');
Yii::setAlias('@module', '@app/modules');

# Default Path for thirdparty modules
# Also used by Marketplace to install modules on-the-fly
Yii::setAlias('@modules', '@app/modules');

$config = [
    'version' => '0.20',
    'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR,
    'bootstrap' => ['log', 'humhub\components\bootstrap\ModuleAutoLoader'],
    'sourceLanguage' => 'en',
    'components' => [
        'moduleManager' => [
            'class' => '\humhub\components\ModuleManager'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'search' => array(
            'class' => 'humhub\modules\search\engine\ZendLuceneSearch',
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
        'formatterApp' => [
            // Used to format date/times in applications timezone
            'class' => 'yii\i18n\Formatter',
        ],
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@humhub/views/mail',
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
            'dsn' => 'mysql:host=localhost;dbname=develop2',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
        ],
    ],
    'params' => [
        'installed' => false,
        'dynamicConfigFile' => '@app/config/dynamic.php',
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
        ]
    ]
];




return $config;
