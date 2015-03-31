<?php

return CMap::mergeArray(require (dirname(__FILE__) . '/main.php'), array(
            'components' => array(
                'fixture' => array(
                    'class' => 'application.components.HDbFixtureManager',
                    'basePath' => realpath(dirname(__FILE__) . '/../tests/fixtures'),
                ),
                'db' => array(
                    'connectionString' => 'mysql:host=localhost;dbname=humhub_test',
                    'username' => 'root',
                    'password' => '',
                ),
                'cache' => array(
                    'class' => 'CDummyCache',
                ),
                'themeManager' => array(
                    'basePath' => realpath(dirname(__FILE__) . '/../themes'),
                ),
            ),
            'import' => array(
                'system.test.*',
                'system.test.libs.*',
            ),
            'params' => array(
                'installed' => false,
                'dynamicConfigFile' => dirname(__FILE__) . '/local/_settings_test.php'
            ),
        ));
