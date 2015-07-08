<?php

return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    // Default Application Name
    'name' => 'HumHub',
    'preload' => array('log', 'input'),
    'components' => array(
        // Database
        'db' => array(
            'connectionString' => '',
            'emulatePrepare' => true,
            'charset' => 'utf8',
            'enableProfiling' => true,
            'enableParamLogging' => true,
            'schemaCachingDuration' => 3600,
        ),
        'urlManager' => array(
            'urlFormat' => 'get',
            'showScriptName' => true,
            'rules' => array(
                array(
                    'class' => 'application.modules_core.space.components.SpaceUrlRule',
                    'connectionId' => 'db',
                ),
                array(
                    'class' => 'application.modules_core.user.components.UserUrlRule',
                    'connectionId' => 'db',
                ),
                '/' => '//',
                'dashboard' => 'dashboard/dashboard',
                'directory/members' => 'directory/directory/members',
                'directory/spaces' => 'directory/directory/spaces',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        'moduleManager' => array(
            'class' => 'application.components.ModuleManager',
        ),
        'messages' => array(
            'class' => 'application.components.HPhpMessageSource',
        ),
        'input' => array(
            'class' => 'application.extensions.CmsInput',
            'cleanPost' => false,
            'cleanGet' => false,
        ),
        'interceptor' => array(
            'class' => 'HInterceptor',
        ),
        'session' => array(
            'class' => 'application.modules_core.user.components.SIHttpSession',
            'connectionID' => 'db',
            'sessionName' => 'sin',
        ),
        'request' => array(
        //'enableCsrfValidation' => true,
        ),
        // Caching (Will replaced at runtime)
        'cache' => array(
            'class' => 'CDummyCache'
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
                array(
                    'class' => 'CDbLogRoute',
                    'levels' => 'error, warning',
                    'logTableName' => 'logging',
                    'connectionID' => 'db',
                    'autoCreateLogTable' => false,
                ),
            ),
        ),
    ),
    // Modules
    'modules' => array(
    // All HumHub Modules will automatically loaded via
    // /modules/*/autostart.php
    //   or
    // /modules_core/*/autostart.php
    ),
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.forms.*',
        'application.components.*',
        'application.behaviors.*',
        'application.interfaces.*',
        'application.libs.*',
        'application.widgets.*',
        // 3rd Party Extensions
        'ext.yii-mail.YiiMailMessage',
        'ext.EZendAutoloader.EZendAutoloader',
        'ext.controller-events.*'
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        // Installed Flag
        'installed' => false,
        'availableLanguages' => array(
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
            'hr' => 'hrvatski'
        ),
        'dynamicConfigFile' => dirname(__FILE__) . '/local/_settings.php',
    ),
);
?>
