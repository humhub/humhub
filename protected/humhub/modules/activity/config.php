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
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\AccountMenu;
use yii\db\BaseActiveRecord;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'activity',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
        [CronController::class, CronController::EVENT_ON_HOURLY_RUN, [Events::class, 'onCronHourlyRun']],
        [CronController::class, CronController::EVENT_ON_DAILY_RUN, [Events::class, 'onCronDailyRun']],
        [AccountMenu::class, AccountMenu::EVENT_INIT, [Events::class, 'onAccountMenuInit']],
        [SettingsMenu::class, BaseActiveRecord::EVENT_INIT, [Events::class, 'onSettingsMenuInit']],
        [RecordMap::class, BaseActiveRecord::EVENT_BEFORE_DELETE, [Events::class, 'onBeforeRecordMapDelete']],
        [User::class, BaseActiveRecord::EVENT_BEFORE_DELETE, [Events::class, 'onBeforeUserDelete']],
        [
            ContentContainerActiveRecord::class,
            BaseActiveRecord::EVENT_BEFORE_DELETE,
            [Events::class, 'onBeforeContentContainerDelete'],
        ],
        [ContentActiveRecord::class, BaseActiveRecord::EVENT_BEFORE_DELETE, [Events::class, 'onBeforeContentRecordDelete']],
    ],
    'consoleControllerMap' => [
        'activity' => 'humhub\modules\activity\commands\TestController',
    ],
];
