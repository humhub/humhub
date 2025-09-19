<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use yii\captcha\CaptchaValidator;

/**
 * @since 1.18
 */
class YiiCaptchaValidator extends CaptchaValidator
{
    public $captchaAction = '/captcha/yii';
}
