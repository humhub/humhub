<?php

namespace humhub\models;

use Yii;


/**
 * This is the model class for table "url_oembed".
 *
 * @property string $url
 * @property string $preview
 */
class UrlOembed extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'url_oembed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'preview'], 'required'],
            [['preview'], 'string'],
            [['url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'url' => 'Url',
            'preview' => 'Preview',
        ];
    }

    /**
     * Returns OEmbed Code for a given URL
     *
     * If no oembed code is found, null is returned
     *
     * @param type $url
     */
    public static function GetOEmbed($url)
    {

        $url = trim($url);

        // Check if the given URL has OEmbed Support
        if (UrlOembed::HasOEmbedSupport($url)) {

            // Lookup Cached OEmebed Item from Datbase
            $urlOembed = UrlOembed::findOne(['url'=>$url]);
            if ($urlOembed !== null) {
                return $urlOembed->preview;
            } else {
                return self::loadUrl($url);
            }
        }


        return null;
    }

    /**
     * Prebuilds oembeds for all urls in a given text
     *
     * @param type $text
     */
    public static function preload($text)
    {
        preg_replace_callback('/http(.*?)(\s|$)/i', function($match) {

            $url = $match[0];

            // Already looked up?
            if (UrlOembed::findOne(['url'=>$url]) !== null) {
                return;
            }
            UrlOembed::loadUrl($url);
        }, $text);
    }

    /**
     * Loads OEmbed Data from a given URL and writes them to the database
     *
     * @param type $url
     * @return string
     */
    public static function loadUrl($url)
    {

        $urlOembed = new UrlOembed();
        $urlOembed->url = $url;
        $html = "";

        if ($urlOembed->getProviderUrl() != "") {
            // Build OEmbed Preview
            $jsonOut = UrlOembed::fetchUrl($urlOembed->getProviderUrl());
            if ($jsonOut != "") {
                try {
                    $data = \yii\helpers\Json::decode($jsonOut);
                    if (isset($data['type']) && ($data['type'] === "video" || $data['type'] === 'rich' || $data['type'] === 'photo')) {
                        $html = "<div class='oembed_snippet' data-url='" . \yii\helpers\Html::encode($url) . "'>" . $data['html'] . "</div>";
                    }
                } catch (\yii\base\InvalidParamException $ex) {
                    Yii::warning($ex->getMessage());
                }
            }
        }

        if ($html != "") {
            $urlOembed->preview = $html;
            $urlOembed->save();
        }

        return $html;
    }

    /**
     * Returns the OEmbed API Url if exists
     */
    public function getProviderUrl()
    {
        foreach (UrlOembed::getProviders() as $providerBaseUrl => $providerAPI) {
            if (strpos($this->url, $providerBaseUrl) !== false) {
                return str_replace("%url%", urlencode($this->url), $providerAPI);
            }
        }
        return "";
    }

    /**
     * Checks if a given URL Supports OEmbed
     *
     * @param type $url
     * @return boolean
     */
    public static function HasOEmbedSupport($url)
    {
        foreach (UrlOembed::getProviders() as $providerBaseUrl => $providerAPI) {
            if (strpos($url, $providerBaseUrl) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetches a given URL and returns content
     *
     * @param type $url
     * @return type
     */
    public static function fetchUrl($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        // Not available when open_basedir is set.
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

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
        return $return;
    }

    /**
     * Returns all available OEmbed providers
     *
     * @return array
     */
    public static function getProviders()
    {
        $providers = Yii::$app->settings->get('oembedProviders');
        if ($providers != "") {
            return \yii\helpers\Json::decode($providers);
        }
        return array();
    }

    /**
     * Saves an array of available OEmbed providers
     *
     * @param array $providers
     */
    public static function setProviders($providers)
    {
        Yii::$app->settings->set('oembedProviders', \yii\helpers\Json::encode($providers));
    }

}
