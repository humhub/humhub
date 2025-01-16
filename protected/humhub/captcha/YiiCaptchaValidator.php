<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\captcha;

use yii\captcha\CaptchaValidator;

/**
 * @since 1.18
 */
class YiiCaptchaValidator extends CaptchaValidator
{
    public $captchaAction = '/captcha/yii';
}
