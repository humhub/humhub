<?php

use humhub\components\console\Application;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'ui',
    'class' => \humhub\modules\ui\Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        'sw.js' => 'ui/service-worker/index'
    ],
    'events' => [
        [Application::class, Application::EVENT_ON_INIT, ['humhub\modules\ui\Events', 'onConsoleApplicationInit']],
    ]
];
