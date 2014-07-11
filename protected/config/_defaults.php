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
            'en' => 'English',
            'de' => 'Deutsch*',
            'fr' => 'Francais*',
            'nl' => 'Nederlands*'
        ),
        'dynamicConfigFile' => dirname(__FILE__) . '/_settings.php',
    ),
);
?>
