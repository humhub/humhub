<?php

use humhub\commands\CronController;
use humhub\modules\oauth\commands\OauthController;
use humhub\modules\oauth\Events;
use humhub\modules\oauth\Module;

return [
    'id' => 'oauth',
    'class' => Module::class,
    'isCoreModule' => true,
    'consoleControllerMap' => [
        'oauth' => OauthController::class,
    ],
    'urlManagerRules' => [
    ],
    'events' => [
        [CronController::class, CronController::EVENT_ON_DAILY_RUN, [Events::class, 'onCronDailyRun']],
    ],
];
