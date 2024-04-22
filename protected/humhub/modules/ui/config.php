<?php

use humhub\components\console\Application;
use humhub\modules\ui\Module;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'ui',
    'class' => Module::class,
    'consoleControllerMap' => [
        'theme' => '\humhub\modules\ui\commands\ThemeController',
    ],
    'isCoreModule' => true,
];
