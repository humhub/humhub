<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use AltchaOrg\Altcha\Altcha;
use Exception;
use JsonException;
use Yii;
use yii\validators\Validator;

/**
 * @since 1.18
 */
class AltchaCaptchaValidator extends Validator
{
    /**
     * @var bool whether to skip this validator if the input is empty.
     */
    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        try {
            $payload = json_decode(base64_decode((string) $model->$attribute), true, 512, JSON_THROW_ON_ERROR);
            if (Altcha::verifySolution($payload, static::getHmacKey(), true)) {
                return;
            }
        } catch (Exception|JsonException $e) {
            Yii::error('AltchaCaptcha verification error: ' . $e->getMessage());
        }

        $this->addError(
            $model,
            $attribute,
            Yii::t('base', 'We couldn\'t verify that you\'re human. Please check the box again.'),
        );
    }

    public static function getHmacKey(): ?string
    {
        $secretKey = Yii::$app->settings->get('captcha.altcha.hmacKey');

        if (!$secretKey) {
            try {
                $secretKey = bin2hex(random_bytes(32));
                Yii::$app->settings->set('captcha.altcha.hmacKey', $secretKey);
            } catch (Exception $e) {
                Yii::error('AltchaCaptcha failed to generate secret key: ' . $e->getMessage());
                return null;
            }
        }

        return $secretKey;
    }
}
