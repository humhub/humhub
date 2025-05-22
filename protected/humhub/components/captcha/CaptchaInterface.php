<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

interface CaptchaInterface
{
    public function createInputWidget($config = []): string;

    public function getValidatorClass(): string;

}
