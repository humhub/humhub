<?php

use humhub\modules\search\engine\Search;
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
    'events' => [
        ['class' => Search::class, 'event' => Search::EVENT_ON_REBUILD, 'callback' => [Events::class, 'onSearchRebuild']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onContentDelete']],
        ['class' => ContentAddonActiveRecord::class, 'event' => ContentAddonActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onContentDelete']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => CronController::class, 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::class, 'onHourlyCron']],
    ]
];
?>