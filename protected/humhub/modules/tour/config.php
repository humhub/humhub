<?php

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\Module;

return [
    'id' => 'tour',
    'class' => Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => Sidebar::className(), 'event' => Sidebar::EVENT_INIT, 'callback' => [Module::className(), 'onDashboardSidebarInit']],
    ],
];
