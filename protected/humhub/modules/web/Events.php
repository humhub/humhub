<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web;

use Yii;
use humhub\controllers\ErrorController;
use humhub\models\Setting;
use humhub\modules\web\security\helpers\Security;

/**
 * Event Handling Callbacks
 *
 * @package humhub\modules\web
 */
class Events
{
    public static function onBeforeAction($evt)
    {
        if(Yii::$app->request->isConsoleRequest) {
            return;
        }

        $withCSP = !Yii::$app->request->isAjax && Setting::isInstalled() && !(Yii::$app->controller instanceof ErrorController);
        Security::applyHeader($withCSP);
    }

    public static function onAfterLogin($evt)
    {
        // Make sure a new nonce is generated after login
        Security::setNonce(null);
    }
}
