<?php

use humhub\modules\search\engine\Search;
use humhub\modules\user\models\User;
use humhub\modules\space\Events;
use humhub\modules\space\Module;
use humhub\components\console\Application;
use humhub\commands\IntegrityController;

return [
    'id' => 'space',
    'class' => Module::class,
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
        [User::class, User::EVENT_BEFORE_SOFT_DELETE, [Events::class, 'onUserSoftDelete']],
        [Search::class, Search::EVENT_ON_REBUILD, [Events::class, 'onSearchRebuild']],
        [Application::class, Application::EVENT_ON_INIT, [Events::class, 'onConsoleApplicationInit']],
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
    ],
];
?>