<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\Controller;

/**
 * ErrorController
 *
 * @author luke
 * @since 0.11
 */
class ErrorController extends Controller
{

    /**
     * This is the action to handle external exceptions.
     */
    public function actionIndex()
    {
        if ($error = Yii::app()->errorHandler->error) {

            if (Yii::app()->request->isAjaxRequest) {
                echo CHtml::encode($error['message']);
                return;
            }

            /**
             * Switch to plain base layout, in case the user is not logged in
             * and public access is disabled.
             */
            if (Yii::app()->user->isGuest && !HSetting::Get('allowGuestAccess', 'authentication_internal')) {
                $this->layout = "application.views.error._layout";
            }

            if ($error['type'] == 'CHttpException') {
                switch ($error['code']) {
                    case 401:
                        Yii::app()->user->returnUrl = Yii::app()->request->requestUri;
                        return $this->render('401', $error);
                        break;
                }
            }

            $this->render('index', $error);
        }
    }

}
