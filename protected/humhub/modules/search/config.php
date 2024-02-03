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
    ],
    'consoleControllerMap' => [
    ],
    'urlManagerRules' => [
        'search' => 'search/search/index',
    ]
];
