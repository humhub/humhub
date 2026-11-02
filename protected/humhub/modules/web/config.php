<?php

/** @noinspection MissedFieldInspection */

use humhub\modules\web\Events;
use humhub\modules\user\controllers\AuthController;
use humhub\modules\web\Module;
use yii\web\Controller;

return [
    'id' => 'web',
    'class' => Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        // Without extension: web servers serving `.js` URLs as static files would answer with a 404
        'sw-js' => 'web/pwa-service-worker/index',
        // URL until 1.19.3, still used by browsers updating a service worker registered with it
        'sw.js' => 'web/pwa-service-worker/index',
        'manifest.json' => 'web/pwa-manifest/index',
        'offline.pwa.html' => 'web/pwa-offline/index',
    ],
    'events' => [
        [Controller::class, Controller::EVENT_BEFORE_ACTION, [Events::class, 'onBeforeAction']],
        [AuthController::class, AuthController::EVENT_AFTER_LOGIN, [Events::class, 'onAfterLogin']],
    ],
];
