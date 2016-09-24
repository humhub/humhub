<?php

use humhub\modules\activity\Events;
use humhub\components\ActiveRecord;
use humhub\commands\IntegrityController;

return [
    'id' => 'activity',
    'class' => humhub\modules\activity\Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onActiveRecordDelete']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::className(), 'onIntegrityCheck']],
    ],
];
?>