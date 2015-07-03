<?php

use humhub\modules\content\Events;
use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\commands\CronController;

return [
    'id' => 'content',
    'class' => \humhub\modules\content\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')],
        ['class' => WallEntryControls::className(), 'event' => WallEntryControls::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryControlsInit')],
        ['class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryAddonInit')],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::className(), 'onCronRun']],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => [Events::className(), 'onCronRun']],
    ),
];
?>