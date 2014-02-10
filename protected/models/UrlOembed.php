<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * This is the model class for table "url".
 *
 * This table holds all preview / oembed code for urls.
 *
 * The followings are the available columns in table 'url':
 * @property string $url
 * @property string $preview
 *
 * @package humhub.models
 * @since 0.5 */
class UrlOembed extends HActiveRecord {

    /**
     * List of OEmbed Providers
     *
     * @todo Make me dynamic
     * @var type
     */
    public static $provider = array(
        "vimeo.com" => "http://vimeo.com/api/oembed.json?scheme=https&url=",
        "youtube.com" => "http://www.youtube.com/oembed?scheme=https&url=",
        "youtu.be" => "http://www.youtube.com/oembed?scheme=https&url=",
        "soundcloud.com" => "https://soundcloud.com/oembed?url=",
        "slideshare.net" => "https://www.slideshare.net/api/oembed/2?url=",
    );

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Url the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'url_oembed';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('url, preview', 'required'),
            array('url', 'length', 'max' => 255),
        );
    }

    /**
     * Returns OEmbed Code for a given URL
     *
     * If no oembed code is found, null is returned
     *
     * @param type $url
     */
    public static function GetOEmbed($url) {

        // Check if the given URL has OEmbed Support
        if (UrlOembed::HasOEmbedSupport($url)) {

            // Lookup Cached OEmebed Item from Datbase
            $urlOembed = UrlOembed::model()->findByPk($url);
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
    public static function preload($text) {


        preg_replace_callback('/http(.*?)(\s|$)/i', function($match) {

            $url = $match[0];

            // Already looked up?
            if (UrlOembed::model()->findByPk($url) !== null) {
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
    public static function loadUrl($url) {

        $urlOembed = new UrlOembed();
        $urlOembed->url = $url;
        $html = "";

        if ($urlOembed->getProviderUrl() != "") {
            // Build OEmbed Preview
            $jsonOut = UrlOembed::fetchUrl($urlOembed->getProviderUrl());
            if ($jsonOut != "") {
                $data = CJSON::decode($jsonOut);
                if ($data['type'] === "video" || $data['type'] === 'rich') {
                    $html = "<div class='oembed_snippet'>" . $data['html'] . "</div>";
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
    public function getProviderUrl() {
        foreach (UrlOembed::$provider as $providerBaseUrl => $providerAPI) {
            if (strpos($this->url, $providerBaseUrl) !== false) {
                return $providerAPI . urlencode($this->url) . "&format=json&maxwidth=450";
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
    public static function HasOEmbedSupport($url) {
        foreach (UrlOembed::$provider as $providerBaseUrl => $providerAPI) {
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
    public static function fetchUrl($url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        $return = curl_exec($curl);
        curl_close($curl);
        return $return;
    }

}
