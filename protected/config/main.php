<?php

/**
 * Web Application Default Configuration
 *
 * This file not affects console applications!
 */
Yii::setPathOfAlias('modules_core', dirname(__FILE__) . '/../modules_core');


$defaults = require (dirname(__FILE__) . '/_defaults.php');
$pre_config = CMap::mergeArray($defaults, require ($defaults['params']['dynamicConfigFile']));

return CMap::mergeArray($pre_config, array(
            // preloading 'log' component
            'preload' => array('log'),
            // application components
            'components' => array(
                // Session specific settings
                'session' => array(
                ),
                'user' => array(
                    // enable cookie-based authentication
                    'allowAutoLogin' => true,
                    'class' => 'application.modules_core.user.components.WebUser',
                    'loginUrl' => array('//user/auth/login'),
                ),
                'request' => array(
                    'class' => 'HHttpRequest',
                    'enableCsrfValidation' => true,
                ),
                'clientScript' => array(
                    'class' => 'HClientScript',
                ),
                // uncomment the following to enable URLs in path-format
                'urlManager' => array(
                    'rules' => array(
                        '<controller:\w+>/<id:\d+>' => '<controller>/view',
                        '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                        '/' => '//'
                    ),
                ),
                'themeManager' => array(
                    'themeClass' => 'HTheme',
                ),
                'errorHandler' => array(
                    // use 'site/error' action to display errors
                    'errorAction' => '//site/error',
                ),
                'log' => array(
                    'class' => 'CLogRouter',
                    'routes' => array(
                        array(
                            'class' => 'CFileLogRoute',
                            'levels' => 'error, warning',
                        ),
                    ),
                ),
            ),
        ));