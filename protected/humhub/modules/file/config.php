<?php

use humhub\modules\file\Module;
use humhub\modules\search\engine\Search;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\commands\CronController;
use humhub\commands\IntegrityController;
use humhub\modules\file\Events;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;

return [
    'id' => 'file',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => WallEntryAddons::class, 'event' => WallEntryAddons::EVENT_INIT, 'callback' => [Events::class, 'onWallEntryAddonInit']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => [Events::class, 'onCronDailyRun']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => ActiveRecord::class, 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onBeforeActiveRecordDelete']],
        ['class' => User::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onUserDelete']],
        ['class' => Search::class, 'event' => Search::EVENT_SEARCH_ATTRIBUTES, 'callback' => [Events::class, 'onSearchAttributes']]
    ],
];
