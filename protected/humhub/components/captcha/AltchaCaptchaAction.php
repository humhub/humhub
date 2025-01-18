<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\ChallengeOptions;
use DateTime;
use yii\base\Action;

/**
 * @since 1.18
 */
class AltchaCaptchaAction extends Action
{
    public function run()
    {
        $options = new ChallengeOptions([
            'hmacKey' => AltchaCaptchaValidator::getHmacKey(),
            'maxNumber' => 50000,
            'algorithm' => 'SHA-256',
            'saltLength' => 12,
            'expires' => new DateTime('+5 minutes'),
        ]);

        return $this->controller->asJson(Altcha::createChallenge($options));
    }
}
