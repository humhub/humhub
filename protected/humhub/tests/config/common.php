<?php

return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=humhub_test',
            'username' => 'travis',
            'password' => '',
            'charset' => 'utf8',
            'attributes'=>[
                PDO::ATTR_PERSISTENT => true
            ]
        ], 
        'view' => 
            array (
              'theme' => 
                    array (
                      'name' => 'HumHub',
                      'basePath' => '@humhub/themes/HumHub',
                    ),
            ),
        ],
    'params' => [
        'installed' => true,
        'moduleAutoloadPaths' => ['D:/codebase/humhub/modules'],
    ]
];
