<?php


/** @noinspection MissedFieldInspection */
return [
    'id' => 'ui',
    'class' => \humhub\modules\ui\Module::class,
    'consoleControllerMap' => [
        'theme' => '\humhub\modules\ui\commands\ThemeController'
    ],
    'isCoreModule' => true
];
