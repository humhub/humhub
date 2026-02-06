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
    public function validateAttribute($model, $attribute): void
    {
        $errorMessage = null;
        if ($model->$attribute) {
            try {
                $decodedValue = base64_decode((string)$model->$attribute, true);
                if ($decodedValue === false) {
                    $errorMessage = 'Invalid base64 encoding';
                } else {
                    $payload = json_decode($decodedValue, true, 512, JSON_THROW_ON_ERROR);
                    $altcha = new Altcha(static::getHmacKey());
                    if ($altcha->verifySolution($payload, true)) {
                        return;
                    }
                }
            } catch (JsonException $e) {
                $errorMessage = 'JSON decode error: ' . $e->getMessage();
            } catch (Exception $e) {
                $errorMessage = 'Undefined error: ' . $e->getMessage();
            }
        }

        if ($errorMessage) {
            Yii::error(sprintf(
                'AltchaCaptcha verification error: %s (Model: %s, Attribute: %s, Value length: %d)',
                $errorMessage,
                get_class($model),
                $attribute,
                strlen((string)$model->$attribute),
            ));
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
