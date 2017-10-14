<?php

use humhub\modules\notification\Module;
use humhub\modules\notification\Events;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\commands\IntegrityController;
use humhub\commands\CronController;
use humhub\components\ActiveRecord;
use humhub\widgets\LayoutAddons;

return [
    'id' => 'notification',
    'class' => Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onUserDelete']],
        ['class' => Space::className(), 'event' => Space::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onSpaceDelete']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::className(), 'onIntegrityCheck']],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => [Events::className(), 'onCronDailyRun']],
        ['class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onActiveRecordDelete']],
        ['class' => LayoutAddons::className(), 'event' => LayoutAddons::EVENT_BEFORE_RUN, 'callback' => [Events::className(), 'onLayoutAddons']]
    ],
];
?>