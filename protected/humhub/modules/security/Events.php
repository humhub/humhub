<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\security;

use humhub\controllers\ErrorController;
use humhub\models\Setting;
use humhub\modules\security\helpers\Security;
use Yii;
use yii\base\BaseObject;

/**
 * Events provides callbacks to handle events.
 *
 * @since 1.3
 * @author luke
 */
class Events extends BaseObject
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
