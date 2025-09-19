<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\BaseChallengeOptions;
use AltchaOrg\Altcha\ChallengeOptions;
use AltchaOrg\Altcha\Hasher\Algorithm;
use yii\base\Action;

/**
 * @since 1.18
 */
class AltchaCaptchaAction extends Action
{
    public function run()
    {
        $options = new ChallengeOptions(
            algorithm: Algorithm::SHA256,
            maxNumber: BaseChallengeOptions::DEFAULT_MAX_NUMBER,
            expires: (new \DateTimeImmutable())->add(new \DateInterval('PT5M')), // challenge expiration time (5 minutes from now
            saltLength: 12,
        );

        $altcha = new Altcha(AltchaCaptchaValidator::getHmacKey());
        $chalenge = $altcha->createChallenge($options);

        return $this->controller->asJson($chalenge);
    }
}
