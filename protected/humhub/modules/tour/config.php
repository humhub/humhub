<?php

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\Module;

return [
    'id' => 'tour',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => Sidebar::class, 'event' => Sidebar::EVENT_INIT, 'callback' => [Module::class, 'onDashboardSidebarInit']],
    ],
];
?>