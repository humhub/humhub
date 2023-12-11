<?php

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\Events;
use humhub\modules\tour\Module;
use yii\web\User;

return [
    'id' => 'tour',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [Sidebar::class, Sidebar::EVENT_INIT, [Events::class, 'onDashboardSidebarInit']],
        [User::class, User::EVENT_BEFORE_LOGIN, [Events::class, 'onUserBeforeLogin']],
    ],
];
