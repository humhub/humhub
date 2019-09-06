<?php


namespace humhub\modules\web\security\helpers;


use humhub\models\Setting;
use Yii;
use humhub\modules\web\security\models\SecuritySettings;

class Security
{
    const SESSION_KEY_NONCE = 'security-script-src-nonce';

    /**
     * @throws \Exception
     */
    public static function applyHeader($withCsp = false)
    {
        $settings = new SecuritySettings();

        // Make sure we only update nonces and set CSP Header in full page loads
        if ($withCsp) {
            $header = $settings->getCSPHeader();
            foreach ($settings->getCSPHeaderKeys() as $key) {
                static::setHeader($key, $header);
            }

            if($settings->hasSection(SecuritySettings::CSP_SECTION_REPORT_ONLY)) {
                $reportOnlySettings = new SecuritySettings(['cspSection' => SecuritySettings::CSP_SECTION_REPORT_ONLY]);
                $header = $reportOnlySettings->getHeader(SecuritySettings::HEADER_CONTENT_SECRUITY_POLICY_REPORT_ONLY);
                foreach ($reportOnlySettings->getCSPHeaderKeys() as $key) {
                    static::setHeader($key, $header);
                }
            }
        }

        /*if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            static::setHeader(SecuritySettings::HEADER_STRICT_TRANSPORT_SECURITY, $settings->getHeader(SecuritySettings::HEADER_STRICT_TRANSPORT_SECURITY));
        }*/

        foreach ($settings->getHeaders() as $header => $value) {
            if (!$settings->isCSPHeaderKey($header)) {
                static::setHeader($header, $value);
            }
        }
    }

    private static function setHeader($key, $value)
    {
        if($value) {
            Yii::$app->response->headers->add($key, $value);
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private static function createNonce()
    {
        return base64_encode(\random_bytes(18));
    }

    public static function setNonce($nonce = null)
    {
        Yii::$app->session->set(static::SESSION_KEY_NONCE, $nonce);
    }

    /**
     * @param bool $create creates a new nonce if none given
     * @return string
     * @throws \Exception
     */
    public static function getNonce($create = false)
    {
        if(!Setting::isInstalled()) {
            return null;
        }

        $nonce = Yii::$app->session->get(static::SESSION_KEY_NONCE);

        if($create && !$nonce) {
            $nonce = static::createNonce();
            static::setNonce($nonce);
        }

        return $nonce;
    }
}
