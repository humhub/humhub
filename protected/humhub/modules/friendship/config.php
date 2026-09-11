<?php

use humhub\components\api\ApiRules;
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
    'urlManagerRules' => ApiRules::v2([
        ['pattern' => 'user/<id:\d+>/friendship', 'route' => 'friendship/api/friendship/state', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'user/<id:\d+>/friendship', 'route' => 'friendship/api/friendship/affirm', 'verb' => 'POST'],
        ['pattern' => 'user/<id:\d+>/friendship', 'route' => 'friendship/api/friendship/remove', 'verb' => 'DELETE'],
    ]),
];
