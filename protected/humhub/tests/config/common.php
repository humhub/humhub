<?php

return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=humhub_test',
            'username' => 'travis',
            'password' => '',
            'charset' => 'utf8',
            'attributes' => [
                PDO::ATTR_PERSISTENT => true
            ]
        ],
        'view' => [
            'theme' =>
            [
                'name' => 'HumHub',
                'basePath' => '@webroot/themes/HumHub',
            ],
        ],
        'queue' => [
            'class' => 'humhub\components\queue\driver\Instant',
        ],
    ],
    'params' => [
        'installed' => true,
        'moduleAutoloadPaths' => ['/home/travis/build/humhub'],
    ]
];
