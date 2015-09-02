<?php

/**
 * Console Default Configuration
 *
 * This configuration file only affects only the console application.
 */

$defaults = require (dirname(__FILE__) . '/_defaults.php');
$pre_config = CMap::mergeArray($defaults, require ($defaults['params']['dynamicConfigFile']));

return CMap::mergeArray($pre_config, array(
            'preload' => array('log'),
            'behaviors' => array(
                'viewRenderer' => 'HConsoleApplicationBehavior',
            ),
            'components' => array(
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
                'application.commands.shell.*',
                'application.modules_core.user.components.*',
                'ext.EZendAutoloader.EZendAutoloader',
                'ext.migrate-command.*',
            ),
            'commandMap' => array(
                'message' => 'application.commands.shell.ZMessageCommand',
                'search_rebuild' => 'application.commands.shell.SearchIndexer.Rebuilder',
                'search_optimize' => 'application.commands.shell.SearchIndexer.Optimize',
                'integritychecker' => 'application.commands.shell.Maintain.IntegrityChecker',
                'space' => 'application.modules_core.space.console.SpaceCliTool',
                'emailing' => 'application.commands.shell.EMailing.EMailing',
                'cron' => 'application.commands.shell.ZCron.ZCronRunner',
                'cache' => 'application.commands.shell.HCacheCommand',
                'module' => 'application.modules_core.admin.console.ModuleTool',
                'update' => 'application.commands.shell.HUpdateCommand',
                'migrate' => array(
                    'class' => 'application.commands.shell.ZMigrateCommand',
                    'migrationPath' => 'application.migrations',
                    'migrationTable' => 'migration',
                ),
            ),
        ));