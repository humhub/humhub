<?php


namespace humhub\modules\security\helpers;


use Yii;
use yii\base\InvalidArgumentException;

class Security
{
    const HEADER_CONTENT_SECRUITY_POLICY = 'Content-Security-Policy';

    const HEADER_X_CONTENT_TYPE_VALUE_NOSNIFF = 'nosniff';
    const HEADER_X_XSS_PROTECTION = 'X-XSS-Protection';
    const HEADER_X_CONTENT_TYPE = 'X-Content-Type-Options';
    const HEADER_STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    const HEADER_STRICT_TRANSPORT_SECURITY_VALUE = 'max-age=31536000';
    const HEADER_X_FRAME_OPTIONS = 'X-Frame-Options';

    const SESSION_KEY_NONCE = 'security-script-src-nonce';

    public static function setNonce($nonce = null)
    {
        if($nonce !== null && !is_string($nonce)) {
            throw new InvalidArgumentException('Invalid nonce value provided.');
        }

        Yii::$app->session->set(static::SESSION_KEY_NONCE, $nonce);
    }

    /**
     * @return string
     */
    public static function getNonce()
    {
        return Yii::$app->session->get(static::SESSION_KEY_NONCE);
    }
}