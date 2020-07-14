<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web;

use humhub\modules\web\pwa\controllers\OfflineController;
use humhub\modules\web\pwa\controllers\ServiceWorkerController;
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
    public static function onAfterAction($evt)
    {
        if(Yii::$app->request->isConsoleRequest) {
            return;
        }

        Security::applyHeader(static::generateCSP());
    }

    /**
     * @return bool whether or not to generate a csp header for the current request
     */
    private static function generateCSP()
    {
        return !Yii::$app->request->isAjax
            && Yii::$app->response->format === 'html'
            && Setting::isInstalled()
            && !(Yii::$app->controller instanceof ErrorController)
            && !(Yii::$app->controller instanceof OfflineController)
            && !(Yii::$app->controller instanceof ServiceWorkerController);
    }

    public static function onAfterLogin($evt)
    {
        // Make sure a new nonce is generated after login
        Security::setNonce(null);
    }
}
