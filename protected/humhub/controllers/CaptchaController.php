<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\captcha\AltchaCaptcha;
use humhub\components\captcha\AltchaCaptchaAction;
use humhub\components\captcha\YiiCaptcha;
use humhub\components\Controller;
use Yii;
use yii\captcha\CaptchaAction;

/**
 * Controller for build-in captcha actions
 *
 * @since 1.18
 */
class CaptchaController extends Controller
{
    public $access = ControllerAccess::class;

    public function actions()
    {
        if (Yii::$app->captcha instanceof AltchaCaptcha) {
            return [
                'altcha' => [
                    'class' => AltchaCaptchaAction::class,
                ],
            ];
        } elseif (Yii::$app->captcha instanceof YiiCaptcha) {
            return [
                'yii' => [
                    'class' => CaptchaAction::class,
                    'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                ],
            ];
        }
    }

    /**
     * Serves the ALTCHA Web Worker from the application origin.
     *
     * The `Worker` constructor is bound by the same-origin policy, so the worker
     * script cannot be loaded from a cross-origin assets mount (e.g. an S3/CDN
     * assets mount). Serving it here - and pointing the widget's `workerurl` at
     * this action - keeps the worker same-origin regardless of where assets are
     * published.
     *
     * @see \humhub\components\captcha\AltchaCaptchaInput
     * @since 1.19
     */
    public function actionWorker()
    {
        return Yii::$app->response->sendFile(
            Yii::getAlias('@npm/altcha/dist_external/worker.js'),
            'altcha-worker.js',
            ['mimeType' => 'text/javascript', 'inline' => true],
        );
    }
}
