<?php

use humhub\widgets\TopMenuRightStack;
use humhub\modules\search\Events;
use humhub\components\console\Application;
use humhub\commands\CronController;

return [
    'isCoreModule' => true,
    'id' => 'search',
    'class' => \humhub\modules\search\Module::class,
    'events' => [
        ['class' => TopMenuRightStack::class, 'event' => TopMenuRightStack::EVENT_INIT, 'callback' => [Events::class, 'onTopMenuRightInit']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::class, 'onHourlyCron']],
    ],
    'consoleControllerMap' => [
        'search' => '\humhub\modules\search\commands\SearchController'
    ],
    'urlManagerRules' => [
        'search' => 'search/search/index',
    ]
];
