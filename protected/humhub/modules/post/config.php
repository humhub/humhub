<?php

use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallCreateContentMenu;
use humhub\modules\post\Events;

return [
    'id' => 'post',
    'class' => \humhub\modules\post\Module::class,
    'isCoreModule' => true,
    'events' => [
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
        [WallCreateContentMenu::class, WallCreateContentMenu::EVENT_INIT, [Events::class, 'onInitWallCreateContentMenu']],
    ]
];