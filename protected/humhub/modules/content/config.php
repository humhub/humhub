<?php

use humhub\commands\CronController;
use humhub\commands\IntegrityController;
use humhub\modules\content\Events;
use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

return [
    'id' => 'content',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => Events::onIntegrityCheck(...)],
        ['class' => WallEntryAddons::class, 'event' => WallEntryAddons::EVENT_INIT, 'callback' => Events::onWallEntryAddonInit(...)],
        ['class' => User::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => Events::onUserDelete(...)],
        ['class' => User::class, 'event' => User::EVENT_BEFORE_SOFT_DELETE, 'callback' => Events::onUserSoftDelete(...)],
        ['class' => Space::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => Events::onSpaceDelete(...)],
        ['class' => Content::class, 'event' => Content::EVENT_AFTER_DELETE, 'callback' => Events::onContentAfterDelete(...)],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => Events::onCronDailyRun(...)],
        ['class' => CronController::class, 'event' => CronController::EVENT_BEFORE_ACTION, 'callback' => Events::onCronBeforeAction(...)],
    ],
    'consoleControllerMap' => [
        'content-search' => '\humhub\modules\content\commands\SearchController',
    ],

];
