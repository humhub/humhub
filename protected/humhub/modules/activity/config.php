<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\commands\CronController;
use humhub\modules\activity\Events;
use humhub\components\ActiveRecord;
use humhub\commands\IntegrityController;
use humhub\modules\activity\Module;
use humhub\modules\admin\widgets\SettingsMenu;
use humhub\modules\user\widgets\AccountMenu;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'activity',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => ActiveRecord::class, 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onActiveRecordDelete']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::class, 'onCronHourlyRun']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => [Events::class, 'onCronDailyRun']],
        ['class' => AccountMenu::class, 'event' => AccountMenu::EVENT_INIT, 'callback' => [Events::class, 'onAccountMenuInit']],
        ['class' => SettingsMenu::class, 'event' => SettingsMenu::EVENT_INIT, 'callback' => [Events::class, 'onSettingsMenuInit']]
    ],
];
