<?php

use humhub\modules\friendship\Events;
use humhub\modules\friendship\Module;
use humhub\modules\user\widgets\AccountMenu;

return [
    'id' => 'friendship',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => AccountMenu::class, 'event' => AccountMenu::EVENT_INIT, 'callback' => [Events::class, 'onAccountMenuInit']],
    ],
];
