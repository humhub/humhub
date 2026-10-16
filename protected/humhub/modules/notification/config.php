<?php

use humhub\modules\notification\Module;
use humhub\modules\notification\Events;
use humhub\commands\IntegrityController;
use humhub\commands\CronController;
use humhub\widgets\LayoutAddons;

return [
    'id' => 'notification',
    'class' => Module::class,
    'isCoreModule' => true,
    // HTTP API (see docs/develop/concept-api.md) - the caller's own notifications and
    // notification settings, consumed by the notification islands.
    'urlManagerRules' => [
        ['pattern' => 'api/v2/notification/settings', 'route' => 'notification/api/settings/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/notification/settings', 'route' => 'notification/api/settings/update', 'verb' => 'PATCH'],
        ['pattern' => 'api/v2/notification/settings/reset', 'route' => 'notification/api/settings/reset', 'verb' => 'POST'],
        ['pattern' => 'api/v2/notification/settings/reset-all', 'route' => 'notification/api/settings/reset-all', 'verb' => 'POST'],
        ['pattern' => 'api/v2/notification', 'route' => 'notification/api/notification/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/notification/mark-as-seen', 'route' => 'notification/api/notification/mark-as-seen', 'verb' => 'POST'],
    ],
    'events' => [
        // No delete handlers: the foreign keys of the notification table and the RecordMap remove
        // the notifications of deleted users, contents, containers and other source records.
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => [Events::class, 'onCronDailyRun']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::class, 'onCronHourlyRun']],
        ['class' => LayoutAddons::class, 'event' => LayoutAddons::EVENT_BEFORE_RUN, 'callback' => [Events::class, 'onLayoutAddons']],
    ],
];
