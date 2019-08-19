<?php

/** @noinspection MissedFieldInspection */
return [
    'id' => 'web',
    'class' => \humhub\modules\web\Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        'sw.js' => 'web/pwa-service-worker/index',
        'manifest.json' => 'web/pwa-manifest/index',
        'offline.pwa.html' => 'web/pwa-offline/index'
    ],
    'events' => []
];
