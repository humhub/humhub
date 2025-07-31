<?php

use humhub\commands\IntegrityController;
use humhub\modules\post\Events;
use humhub\modules\post\Module;

return [
    'id' => 'post',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
    ],
];
