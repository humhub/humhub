<?php

use humhub\core\notification\Module;
use humhub\core\notification\Events;
use humhub\core\user\models\User;
use humhub\core\space\models\Space;
use humhub\commands\IntegrityController;
use humhub\commands\CronController;
use humhub\components\ActiveRecord;

return [
    'id' => 'notification',
    'class' => Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onUserDelete')),
        array('class' => Space::className(), 'event' => Space::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onSpaceDelete')),
        array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')),
        array('class' => CronController::className(), 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => array(Events::className(), 'onCronDailyRun')),
        array('class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onActiveRecordDelete'])
    ),
];
?>