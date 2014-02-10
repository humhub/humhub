<?php

/**
 * Console Default Configuration
 *
 * This configuration file only affects only the console application.
 */
$pre_config = CMap::mergeArray(require (dirname(__FILE__) . '/_defaults.php'), require (dirname(__FILE__) . '/_settings.php'));
return CMap::mergeArray($pre_config, array(
            'preload' => array('log'),
            'behaviors' => array(
                'viewRenderer' => 'HConsoleApplicationBehavior',
            ),
            'components' => array(
                'urlManager' => array(
                    'urlFormat' => 'path',
                    'showScriptName' => false,
                    'rules' => array(
                        '<controller:\w+>/<id:\d+>' => '<controller>/view',
                        '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                    ),
                ),
                'request' => array(
                    'class' => 'HHttpRequest',
                ),
                'user' => array(
                    'class' => 'ConsoleUser',
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
            // autoloading model and component classes
            'import' => array(
                'application.models.*',
                'application.forms.*',
                'application.components.*',
                'application.vendors.yii.cli.commands.*',
                'application.libs.*',
                'application.modules_core.user.components.*',
                'ext.EZendAutoloader.EZendAutoloader',
                'ext.migrate-command.*',
            ),
            'commandMap' => array(
                'message' => 'application.commands.shell.ZMessageCommand',
                'search_rebuild' => 'application.commands.shell.SearchIndexer.Rebuilder',
                'search_optimize' => 'application.commands.shell.SearchIndexer.Optimize',
                'integritychecker' => 'application.commands.shell.Maintain.IntegrityChecker',
                'search' => 'application.modules.zstunden.commands.Search',
                'emailing' => 'application.commands.shell.EMailing.EMailing',
                'emailing_test' => 'application.commands.shell.EMailing.TestMail',
                'cron' => 'application.commands.shell.ZCron.ZCronRunner',
                'migrate' => array(
                    'class' => 'application.commands.shell.ZMigrateCommand',
                    'migrationPath' => 'application.migrations',
                    'migrationTable' => 'migration',
                ),
            ),
        ));