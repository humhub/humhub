<?php

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
    'urlManagerRules' => [
        // The general space list - before the membership rules, so the fixed path is not
        // shadowed by a pattern.
        ['pattern' => 'api/v2/space/states', 'route' => 'space/api/space/states', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/space', 'route' => 'space/api/space/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/space/<id:\d+>/membership', 'route' => 'space/api/membership/state', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/space/<id:\d+>/membership', 'route' => 'space/api/membership/affirm', 'verb' => 'PUT'],
        ['pattern' => 'api/v2/space/<id:\d+>/membership', 'route' => 'space/api/membership/remove', 'verb' => 'DELETE'],
        ['pattern' => 'api/v2/space/<id:\d+>/member', 'route' => 'space/api/member/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/space/<id:\d+>/member/bulk/remove', 'route' => 'space/api/member/bulk-remove', 'verb' => 'POST'],
        ['pattern' => 'api/v2/space/<id:\d+>/member/<userId:\d+>/role/<role:admin|moderator|member>', 'route' => 'space/api/member/role', 'verb' => 'POST'],
        ['pattern' => 'api/v2/space/<id:\d+>/member/<userId:\d+>/remove', 'route' => 'space/api/member/remove', 'verb' => 'POST'],
        ['pattern' => 'api/v2/space/<id:\d+>/invitation', 'route' => 'space/api/invitation/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/space/<id:\d+>/invitation/bulk/cancel', 'route' => 'space/api/invitation/bulk-cancel', 'verb' => 'POST'],
        ['pattern' => 'api/v2/space/<id:\d+>/invitation/<userId:\d+>/cancel', 'route' => 'space/api/invitation/cancel', 'verb' => 'POST'],
        ['pattern' => 'api/v2/space/<id:\d+>/applicant', 'route' => 'space/api/applicant/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/space/<id:\d+>/applicant/bulk/<action:accept|decline>', 'route' => 'space/api/applicant/bulk', 'verb' => 'POST'],
        ['pattern' => 'api/v2/space/<id:\d+>/applicant/<userId:\d+>/<action:accept|decline>', 'route' => 'space/api/applicant/<action>', 'verb' => 'POST'],
        ['pattern' => 'api/v2/space/<id:\d+>/follow', 'route' => 'space/api/follow/state', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/space/<id:\d+>/follow', 'route' => 'space/api/follow/follow', 'verb' => 'PUT'],
        ['pattern' => 'api/v2/space/<id:\d+>/follow', 'route' => 'space/api/follow/unfollow', 'verb' => 'DELETE'],
        ['class' => 'humhub\modules\space\components\UrlRule'],
        'spaces' => 'space/spaces',
        '<spaceContainer>/home' => 'space/space/home',
        '<spaceContainer>/about' => 'space/space/about',
    ],
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
        [HeaderControlsMenu::class, HeaderControlsMenu::EVENT_INIT, [Events::class, 'onSpaceHeaderControlsMenuInit']],
    ],
];
