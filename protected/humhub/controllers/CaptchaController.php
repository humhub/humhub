<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\captcha\AltchaCaptchaAction;
use humhub\components\Controller;
use yii\captcha\CaptchaAction;

/**
 * @since 1.18
 */
class CaptchaController extends Controller
{
    public function actions()
    {
        return [
            'yii' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'altcha' => [
                'class' => AltchaCaptchaAction::class,
            ],
        ];
    }
}
