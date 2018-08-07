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
        ['class' => Application::class, 'event' => Application::EVENT_ON_INIT, 'callback' => [Events::class, 'onConsoleApplicationInit']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::class, 'onHourlyCron']],
    ],
    'urlManagerRules' => [
        'search' => 'search/search/index',
    ]    
];
