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
        ['pattern' => 'oauth/authorize', 'route' => 'oauth/authorize/index', 'verb' => ['GET', 'POST']],
        ['pattern' => 'oauth/token', 'route' => 'oauth/token/token', 'verb' => 'POST'],
        ['pattern' => 'oauth/revoke', 'route' => 'oauth/token/revoke', 'verb' => 'POST'],
        ['pattern' => '.well-known/oauth-authorization-server', 'route' => 'oauth/metadata/index', 'verb' => ['GET', 'HEAD']],
    ],
    'events' => [
        [CronController::class, CronController::EVENT_ON_DAILY_RUN, [Events::class, 'onCronDailyRun']],
    ],
];
