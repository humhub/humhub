<?php

use humhub\commands\IntegrityController;
use humhub\modules\post\Events;

return [
    'id' => 'post',
    'class' => \humhub\modules\post\Module::className(),
    'isCoreModule' => true,
    'events' => [
        [IntegrityController::className(), IntegrityController::EVENT_ON_RUN, [Events::className(), 'onIntegrityCheck']],
    ]
];
?>