<?php

use humhub\modules\dashboard\Module;
use humhub\widgets\TopMenu;

return [
    'id' => 'dashboard',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => ['\humhub\modules\dashboard\Events', 'onTopMenuInit']],
    ],
    'urlManagerRules' => [
        'dashboard' => 'dashboard/dashboard'
    ]
];
