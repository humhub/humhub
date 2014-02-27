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
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        // Installed Flag
        'installed' => false,
        // Authentication Options
        'auth' => array(
            // Available Registration Types
            'modes' => array('local'),
            // Local Authentication Options
            'local' => array(
                'freeRegistration' => true,
            ),
        ),
        'dynamicConfigFile' => dirname(__FILE__) . '/_settings.php',
    ),
);
?>
