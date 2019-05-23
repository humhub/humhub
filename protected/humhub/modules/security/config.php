<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
use humhub\modules\security\Events;
use humhub\modules\security\Module;
use humhub\modules\user\controllers\AuthController;
use yii\web\Controller;

return [
    'id' => 'security',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [Controller::class, Controller::EVENT_BEFORE_ACTION, [Events::class, 'onBeforeAction']],
        [AuthController::class, AuthController::EVENT_AFTER_LOGIN, [Events::class, 'onAfterLogin']],
    ],
];
