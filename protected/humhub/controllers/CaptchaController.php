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
}
