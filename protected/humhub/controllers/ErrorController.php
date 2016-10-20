<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use Yii;
use humhub\components\Controller;

use yii\web\HttpException;
use yii\base\UserException;

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
        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            return '';
        }

        if ($exception instanceof UserException || $exception instanceof HttpException) {
            $message = $exception->getMessage();
        } else {
            $message = Yii::t('error', 'An internal server error occurred.');
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            return [
                'error' => true,
                'message' => $message
            ];
        }

        /**
         * Show special login required view for guests
         */
        if (Yii::$app->user->isGuest && $exception instanceof HttpException && $exception->statusCode == "401" && Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess')) {
            return $this->render('@humhub/views/error/401_guests', ['message' => $message]);
        }

        return $this->render('@humhub/views/error/index', [
                    'message' => $message
        ]);
    }

}
