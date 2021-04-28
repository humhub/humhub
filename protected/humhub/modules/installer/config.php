<?php

use humhub\modules\installer\Events;
use yii\db\Connection;

return [
    'id' => 'installer',
    'class' => humhub\modules\installer\Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => Connection::class, 'event' => Connection::EVENT_AFTER_OPEN, 'callback' => [Events::class, 'onConnectionAfterOpen']],
    ],
];
?>