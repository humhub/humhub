<?php

use humhub\modules\search\engine\Search;
use humhub\modules\user\Events;
use humhub\commands\IntegrityController;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\commands\CronController;

return [
    'id' => 'user',
    'class' => \humhub\modules\user\Module::className(),
    'isCoreModule' => true,
    'urlManagerRules' => [
        ['class' => 'humhub\modules\user\components\UrlRule']
    ],
    'events' => [
        ['class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => array(Events::className(), 'onSearchRebuild')],
        ['class' => ContentActiveRecord::className(), 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')],
        ['class' => ContentAddonActiveRecord::className(), 'event' => ContentAddonActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::className(), 'onHourlyCron']],
    ]
];
?>