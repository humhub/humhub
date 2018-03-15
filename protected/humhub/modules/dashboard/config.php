<?php

use humhub\modules\dashboard\Module;
use humhub\widgets\TopMenu;

return [
    'id' => 'dashboard',
    'class' => Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => ['\humhub\modules\dashboard\Events', 'onTopMenuInit']],
    ],
    'urlManagerRules' => [
        'dashboard' => 'dashboard/dashboard'
    ]
];
