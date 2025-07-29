<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

class YiiCaptcha implements CaptchaInterface
{
    public function createInputWidget($config = []): string
    {
        return YiiCaptchaInput::widget($config);
    }

    public function getValidatorClass(): string
    {
        return YiiCaptchaValidator::class;
    }
}
