<?php

use humhub\commands\CronController;
use humhub\commands\IntegrityController;
use humhub\components\gates\GateManager;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\user\Events;
use humhub\modules\user\Module;
use humhub\widgets\TopMenu;

return [
    'id' => 'user',
    'class' => Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        // HTTP API (see docs/develop/concept-api.md) — the caller's own account data.
        ['pattern' => 'api/v2/account', 'route' => 'user/api/account/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/account/blocked-users', 'route' => 'user/api/account/blocked-users', 'verb' => ['GET', 'HEAD']],
        // The user list and what hangs off it. The named sub-resources come first, and the
        // `<id:\d+>` patterns match digits only, so `user/states` is never read as a user id —
        // nor are the friendship module's `user/<id>/friendship` rules shadowed.
        ['pattern' => 'api/v2/user/states', 'route' => 'user/api/user/states', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/user/field-values', 'route' => 'user/api/user/field-values', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/user/tags', 'route' => 'user/api/user/tags', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/user/picker', 'route' => 'user/api/user/picker', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/user', 'route' => 'user/api/user/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/user/<id:\d+>/follow', 'route' => 'user/api/follow/state', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/user/<id:\d+>/follow', 'route' => 'user/api/follow/follow', 'verb' => 'PUT'],
        ['pattern' => 'api/v2/user/<id:\d+>/follow', 'route' => 'user/api/follow/unfollow', 'verb' => 'DELETE'],
        ['class' => 'humhub\modules\user\components\UrlRule'],
        'people' => 'user/people',
        '<userContainer>/home' => 'user/profile/home',
        '<userContainer>/about' => 'user/profile/about',
    ],
    'consoleControllerMap' => [
        'user' => 'humhub\modules\user\commands\UserController',
    ],
    'events' => [
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onContentDelete']],
        ['class' => ContentAddonActiveRecord::class, 'event' => ContentAddonActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onContentDelete']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::class, 'onHourlyCron']],
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => [Events::class, 'onTopMenuInit']],
        ['class' => GateManager::class, 'event' => GateManager::EVENT_INIT_GATES, 'callback' => [Events::class, 'onGateInit']],
    ],
];
