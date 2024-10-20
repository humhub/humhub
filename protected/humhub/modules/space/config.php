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
    'urlManagerRules' => [
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
        [HeaderControlsMenu::class, HeaderControlsMenu::EVENT_INIT, 'callback' => [Events::class, 'onSpaceHeaderControlsMenuInit']],
    ],
];
