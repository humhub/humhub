<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

return [
    'id' => 'humhub-console',
    'controllerNamespace' => 'humhub\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'user' => [
            'class' => 'humhub\core\user\components\User',
            'identityClass' => 'humhub\core\user\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => ['/user/auth/login']
        ],
    ],
];
