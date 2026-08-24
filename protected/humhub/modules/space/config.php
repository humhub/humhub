<?php

use humhub\components\api\ApiRules;
use humhub\modules\space\widgets\HeaderControlsMenu;
use humhub\modules\user\models\User;
use humhub\modules\space\Events;
use humhub\modules\space\Module;
use humhub\commands\IntegrityController;
use humhub\widgets\TopMenu;

return [
    'id' => 'space',
    'class' => Module::class,
    'isCoreModule' => true,
    // The API rules come first: the space UrlRule below matches container prefixes greedily,
    // and an endpoint must never depend on losing that race (see docs/develop/concept-api.md).
    'urlManagerRules' => array_merge(ApiRules::v2([
        ['pattern' => 'space/<id:\d+>/membership', 'route' => 'space/api/membership/state', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'space/<id:\d+>/membership', 'route' => 'space/api/membership/affirm', 'verb' => 'POST'],
        ['pattern' => 'space/<id:\d+>/membership', 'route' => 'space/api/membership/remove', 'verb' => 'DELETE'],
    ]), [
        ['class' => 'humhub\modules\space\components\UrlRule'],
        'spaces' => 'space/spaces',
        '<spaceContainer>/home' => 'space/space/home',
        '<spaceContainer>/about' => 'space/space/about',
    ]),
    'modules' => [
        'manage' => [
            'class' => 'humhub\modules\space\modules\manage\Module',
        ],
    ],
    'consoleControllerMap' => [
        'space' => 'humhub\modules\space\commands\SpaceController',
    ],
    'events' => [
        [User::class, User::EVENT_BEFORE_SOFT_DELETE, [Events::class, 'onUserSoftDelete']],
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
        [TopMenu::class, TopMenu::EVENT_INIT, [Events::class, 'onTopMenuInit']],
        [HeaderControlsMenu::class, HeaderControlsMenu::EVENT_INIT, 'callback' => [Events::class, 'onSpaceHeaderControlsMenuInit']],
    ],
];
