<?php

return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=humhub_test',
            'username' => 'root',
            'password' => '12manyhacks',
            'charset' => 'utf8',
        ], 
        'view' => 
            array (
              'theme' => 
                    array (
                      'name' => 'HumHub',
                      'basePath' => 'D:/codebase/humhub/v1.2-dev/themes/HumHub',
                    ),
            ),
        ],
    'params' => [
        'installed' => true,
        'moduleAutoloadPaths' => ['D:/codebase/humhub/modules'],
    ]
];
