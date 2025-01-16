<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use Yii;
use yii\captcha\Captcha;

/**
 * @since 1.18
 */
class YiiCaptchaInput extends Captcha
{
    public $captchaAction = '/captcha/yii';

    public function init()
    {
        $this->options['placeholder'] = Yii::t('base', 'Enter security code above');
        parent::init();
    }
}
