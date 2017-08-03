<?php

use humhub\modules\search\engine\Search;
use humhub\modules\user\models\User;
use humhub\modules\space\Events;
use humhub\components\console\Application;
use humhub\commands\IntegrityController;

return [
    'id' => 'space',
    'class' => \humhub\modules\space\Module::className(),
    'isCoreModule' => true,
    'urlManagerRules' => [
        ['class' => 'humhub\modules\space\components\UrlRule']
    ],
    'modules' => [
        'manage' => [
            'class' => 'humhub\modules\space\modules\manage\Module'
        ],
    ],
    'events' => [
        ['class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onUserDelete']],
        ['class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => [Events::className(), 'onSearchRebuild']],
        ['class' => Application::className(), 'event' => Application::EVENT_ON_INIT, 'callback' => [Events::className(), 'onConsoleApplicationInit']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::className(), 'onIntegrityCheck']],
    ],
];
