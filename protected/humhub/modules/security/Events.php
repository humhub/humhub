<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\security;

use humhub\controllers\ErrorController;
use humhub\modules\security\models\SecuritySettings;
use Yii;
use yii\base\BaseObject;

/**
 * Events provides callbacks to handle events.
 *
 * @since 1.3
 * @author luke
 */
class Events extends BaseObject
{
    public static function onBeforeAction($evt)
    {
        $settings = new SecuritySettings();
        if(!Yii::$app->controller instanceof ErrorController && !Yii::$app->request->isAjax) {
            $settings->updateNonce();
            static::setHeader(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY, $settings->getCSPHeader());
            static::setHeader(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_IE, $settings->getCSPHeader());
        }

        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            static::setHeader(SecuritySettings::HEADER_STRICT_TRANSPORT_SECURITY, $settings->getHeader(SecuritySettings::HEADER_STRICT_TRANSPORT_SECURITY));
        }

        static::setHeader(SecuritySettings::HEADER_X_XSS_PROTECTION, $settings->getHeader(SecuritySettings::HEADER_X_XSS_PROTECTION));
        static::setHeader(SecuritySettings::HEADER_X_CONTENT_TYPE, $settings->getHeader(SecuritySettings::HEADER_X_CONTENT_TYPE));
        static::setHeader(SecuritySettings::HEADER_X_FRAME_OPTIONS, $settings->getHeader(SecuritySettings::HEADER_X_FRAME_OPTIONS));
        static::setHeader(SecuritySettings::HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES, $settings->getHeader(SecuritySettings::HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES));
        static::setHeader(SecuritySettings::HEADER_REFERRER_POLICY, $settings->getHeader(SecuritySettings::HEADER_REFERRER_POLICY));
        static::setHeader(SecuritySettings::HEADER_PUBLIC_KEY_PINS, $settings->getHeader(SecuritySettings::HEADER_PUBLIC_KEY_PINS));
    }

    private static function setHeader($key, $value)
    {
        if($value) {
            Yii::$app->response->headers->add($key, $value);
        }
    }



}
