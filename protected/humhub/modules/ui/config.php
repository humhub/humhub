<?php

use humhub\components\console\Application;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'ui',
    'class' => \humhub\modules\ui\Module::class,
    'consoleControllerMap' => [
        'theme' => '\humhub\modules\ui\commands\ThemeController'
    ],
    'isCoreModule' => true
];
