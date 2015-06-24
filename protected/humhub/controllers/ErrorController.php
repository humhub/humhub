<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

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
