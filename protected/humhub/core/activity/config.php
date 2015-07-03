<?php

use humhub\core\activity\Events;
use humhub\core\user\models\User;
use humhub\core\space\models\Space;
use humhub\components\ActiveRecord;
use humhub\commands\IntegrityController;

return [
    'id' => 'activity',
    'class' => humhub\core\activity\Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => User::className(), 'event' => User::EVENT_AFTER_DELETE, 'callback' => [Events::className(), 'onUserDelete']],
        ['class' => Space::className(), 'event' => Space::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onSpaceDelete']],
        ['class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onActiveRecordDelete']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::className(), 'onIntegrityCheck']],
    ],
];
?>