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
    'events' => [
        [Controller::class, Controller::EVENT_BEFORE_ACTION, [Events::class, 'onBeforeAction']],
        [AuthController::class, AuthController::EVENT_AFTER_LOGIN, [Events::class, 'onAfterLogin']],
    ],
];
