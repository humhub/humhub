<?php

Yii::setAlias('@webroot', realpath(__DIR__ . '/../../../'));

Yii::setAlias('@app', '@webroot/protected');
Yii::setAlias('@humhub', '@app/humhub');
Yii::setAlias('@module', '@app/modules');

$config = [
    'version' => '0.20',
    'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR,
    'bootstrap' => ['log', 'humhub\components\bootstrap\ModuleAutoLoader'],
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
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@humhub/messages'
                ],
                'security' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@humhub/messages'
                ],
                'error' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@humhub/messages'
                ],
                'widgets_views_markdownEditor' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@humhub/messages'
                ],
            ],
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
            'dsn' => 'mysql:host=localhost;dbname=humhub',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    'params' => [
        'installed' => false,
        'dynamicConfigFile' => '@app/config/dynamic.php',
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


if (YII_ENV_DEV) {
// configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
