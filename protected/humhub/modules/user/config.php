<?php

use humhub\modules\search\engine\Search;
use humhub\modules\user\Events;
use humhub\commands\IntegrityController;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\commands\CronController;
use humhub\widgets\TopMenu;

return [
    'id' => 'user',
    'class' => \humhub\modules\user\Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        ['class' => 'humhub\modules\user\components\UrlRule'],
        'people' => 'user/people',
        '<userContainer>/home' => 'user/profile/home',
        '<userContainer>/about' => 'user/profile/about',
    ],
    'consoleControllerMap' => [
        'user' => 'humhub\modules\user\commands\UserController'
    ],
    'events' => [
        ['class' => Search::class, 'event' => Search::EVENT_ON_REBUILD, 'callback' => [Events::class, 'onSearchRebuild']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onContentDelete']],
        ['class' => ContentAddonActiveRecord::class, 'event' => ContentAddonActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onContentDelete']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::class, 'onHourlyCron']],
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => [Events::class, 'onTopMenuInit']],
    ]
];
?>
