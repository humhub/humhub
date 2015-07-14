<?php

use humhub\modules\content\widgets\WallEntryAddons;
use humhub\commands\CronController;
use humhub\commands\IntegrityController;
use humhub\modules\file\Events;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;

return [
    'id' => 'file',
    'class' => \humhub\modules\file\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryAddonInit')),
        array('class' => CronController::className(), 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => array(Events::className(), 'onCronDailyRun')),
        array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')),
        array('class' => ActiveRecord::className(), 'event' => \humhub\components\ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onBeforeActiveRecordDelete')),
        array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onUserDelete')),
    ),
];
?>