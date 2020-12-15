<?php

use humhub\modules\content\models\Content;
use humhub\modules\search\engine\Search;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\Events;
use humhub\commands\IntegrityController;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\commands\CronController;

return [
    'id' => 'user',
    'class' => \humhub\modules\user\Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        ['class' => 'humhub\modules\user\components\UrlRule']
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
        ['class' => Membership::class, 'event' => Membership::EVENT_MEMBER_ADDED, 'callback' => [Events::class, 'onMemberEvent']],
        ['class' => Membership::class, 'event' => Membership::EVENT_MEMBER_REMOVED, 'callback' => [Events::class, 'onMemberEvent']],
        ['class' => Content::class, 'event' => Content::EVENT_CONTENT_VISIBILITY_CHANGED, 'callback' => [Events::class, 'onContentVisibilityChanged']],
        ['class' => Space::class, 'event' => Space::EVENT_SPACE_VISIBILITY_CHANGED, 'callback' => [Events::class, 'onSpaceVisibilityChanged']],
    ]
];
?>
