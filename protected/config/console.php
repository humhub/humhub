<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii', 'humhub\components\bootstrap\ModuleAutoLoader'],
    'controllerNamespace' => 'humhub\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'search' => array(
            'class' => 'humhub\core\search\engine\ZendLuceneSearch',
        ),
        'moduleManager' => [
            'class' => '\humhub\components\ModuleManager'
        ],
        'db' => $db,
    ],
    'params' => $params,
];
