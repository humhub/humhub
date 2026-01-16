<?php

use humhub\commands\CronController;
use humhub\models\RecordMap;
use humhub\modules\activity\Events;
use humhub\commands\IntegrityController;
use humhub\modules\activity\Module;
use humhub\modules\admin\widgets\SettingsMenu;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\user\widgets\AccountMenu;
use yii\db\BaseActiveRecord;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'activity',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [
            'class' => IntegrityController::class,
            'event' => IntegrityController::EVENT_ON_RUN,
            'callback' => [Events::class, 'onIntegrityCheck']
        ],
        [
            'class' => CronController::class,
            'event' => CronController::EVENT_ON_HOURLY_RUN,
            'callback' => [Events::class, 'onCronHourlyRun']
        ],
        [
            'class' => CronController::class,
            'event' => CronController::EVENT_ON_DAILY_RUN,
            'callback' => [Events::class, 'onCronDailyRun']
        ],
        [
            'class' => AccountMenu::class,
            'event' => AccountMenu::EVENT_INIT,
            'callback' => [Events::class, 'onAccountMenuInit']
        ],
        [
            'class' => SettingsMenu::class,
            'event' => SettingsMenu::EVENT_INIT,
            'callback' => [Events::class, 'onSettingsMenuInit']
        ],
        [
            'class' => RecordMap::class,
            'event' => RecordMap::EVENT_BEFORE_DELETE,
            'callback' => [Events::class, 'onBeforeRecordMapDelete']
        ],
        [
            'class' => ContentContainerActiveRecord::class,
            'event' => ContentContainerActiveRecord::EVENT_BEFORE_DELETE,
            'callback' => [Events::class, 'onBeforeContentContainerDelete']
        ],
        [
            'class' => Content::class,
            'event' => BaseActiveRecord::EVENT_BEFORE_DELETE,
            'callback' => [Events::class, 'onBeforeContentDelete']
        ],
        [
            'class' => \humhub\modules\user\models\User::class,
            'event' => BaseActiveRecord::EVENT_BEFORE_DELETE,
            'callback' => [Events::class, 'onBeforeUserDelete']
        ],
    ],
    'consoleControllerMap' => [
        'activity' => 'humhub\modules\activity\commands\TestController',
    ],
];
