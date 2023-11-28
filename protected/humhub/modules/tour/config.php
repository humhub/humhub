<?php

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\Events;
use humhub\modules\tour\Module;
use humhub\modules\user\controllers\AuthController;

return [
    'id' => 'tour',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [Sidebar::class, Sidebar::EVENT_INIT, [Events::class, 'onDashboardSidebarInit']],
        [AuthController::class, AuthController::EVENT_AFTER_LOGIN, [Events::class, 'onAfterLogin']],
    ],
];
