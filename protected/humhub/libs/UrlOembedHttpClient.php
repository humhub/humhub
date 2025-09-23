<?php

namespace humhub\libs;

use Yii;
use yii\helpers\Json;

class UrlOembedHttpClient implements UrlOembedClient
{
    public const RESPONSE_UNAUTHORIZED = 'Unauthorized';

    public const RESPONSE_NOT_FOUND = 'Not Found';

    public const ERROR_RESPONSES = [
        self::RESPONSE_NOT_FOUND,
        self::RESPONSE_UNAUTHORIZED,
    ];

    /**
     * @inheritdoc
     */
    public function fetchUrl($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_USERAGENT, Yii::$app->name);

        // Not available when open_basedir is set.
        if (!function_exists('ini_get') || !ini_get('open_basedir')) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        }

        if (Yii::$app->settings->get('proxy.enabled')) {
            curl_setopt($curl, CURLOPT_PROXY, Yii::$app->settings->get('proxy.server'));
            curl_setopt($curl, CURLOPT_PROXYPORT, Yii::$app->settings->get('proxy.port'));
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            if (defined('CURLOPT_PROXYUSERNAME')) {
                curl_setopt($curl, CURLOPT_PROXYUSERNAME, Yii::$app->settings->get('proxy.user'));
            }
            if (defined('CURLOPT_PROXYPASSWORD')) {
                curl_setopt($curl, CURLOPT_PROXYPASSWORD, Yii::$app->settings->get('proxy.password'));
            }
            if (defined('CURLOPT_NOPROXY')) {
                curl_setopt($curl, CURLOPT_NOPROXY, Yii::$app->settings->get('proxy.noproxy'));
            }
        }
        $return = curl_exec($curl);
        curl_close($curl);

        return $this->parseJson($return);
    }

    /**
     * @param $json
     * @return string|null
     */
    protected function parseJson($json)
    {
        try {
            if (!empty($json) && !in_array($json, static::ERROR_RESPONSES, true)) {
                return Json::decode($json);
            }
        } catch (\Exception $ex) {
            Yii::warning("Error decoding JSON from OEmbed URL:\n" . $json
                . "\n\n" . $ex->getTraceAsString());
        }

        return null;
    }
}
