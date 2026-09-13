<?php

use humhub\modules\installer\Events;
use yii\db\Connection;

return [
    'id' => 'installer',
    'class' => humhub\modules\installer\Module::class,
    'isCoreModule' => true,
    'consoleControllerMap' => [
        'installer' => 'humhub\modules\installer\commands\InstallController',
    ],
    'events' => [
        ['class' => Connection::class, 'event' => Connection::EVENT_AFTER_OPEN, 'callback' => [Events::class, 'onConnectionAfterOpen']],
    ],
];
