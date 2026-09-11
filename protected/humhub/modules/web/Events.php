<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web;

use humhub\components\InstallationState;
use humhub\controllers\ErrorController;
use humhub\controllers\PwaController;
use humhub\modules\web\security\helpers\Security;
use Yii;

/**
 * Event Handling Callbacks
 *
 * @package humhub\modules\web
 */
class Events
{
    public static function onBeforeAction($evt)
    {
        if (Yii::$app->request->isConsoleRequest) {
            return;
        }

        Security::applyHeader(static::generateCSPRequestCheck());
    }

    /**
     * @return bool whether or not to generate a csp header for the current request
     */
    private static function generateCSPRequestCheck()
    {
        return !Yii::$app->request->isAjax
            && Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED)
            && ($controller = Yii::$app->controller)
            && !($controller instanceof ErrorController)
            && !($controller instanceof PwaController);
    }

    public static function onAfterLogin($evt)
    {
        // Make sure a new nonce is generated after login
        Security::setNonce(null);
    }
}
