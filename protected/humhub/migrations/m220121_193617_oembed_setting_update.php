<?php

use humhub\models\UrlOembed;
use yii\db\Migration;
use yii\helpers\Json;

/**
 * Class m220121_193617_oembed_setting_update
 */
class m220121_193617_oembed_setting_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $oembedProviders = [
            'Facebook Video' => [
                'pattern' => '/facebook\.com\/(.*)(video)/',
                'endpoint' => 'https://graph.facebook.com/v12.0/oembed_video?url=%url%&access_token='
            ],
            'Facebook Post' => [
                'pattern' => '/facebook\.com\/(.*)(post|activity|photo|permalink|media|question|note)/',
                'endpoint' => 'https://graph.facebook.com/v12.0/oembed_post?url=%url%&access_token='
            ],
            'Facebook Page' => [
                'pattern' => '/^(https\:\/\/)*(www\.)*facebook\.com\/((?!video|post|activity|photo|permalink|media|question|note).)*$/',
                'endpoint' => 'https://graph.facebook.com/v12.0/oembed_post?url=%url%&access_token='
            ],
            'Instagram' => [
                'pattern' => '/instagram\.com/',
                'endpoint' => 'https://graph.facebook.com/v12.0/instagram_oembed?url=%url%&access_token='
            ],
            'Twitter' => [
                'pattern' => '/twitter\.com/',
                'endpoint' => 'https://publish.twitter.com/oembed?url=%url%&maxwidth=450'
            ],
            'YouTube' => [
                'pattern' => '/youtube\.com|youtu.be/',
                'endpoint' => 'https://www.youtube.com/oembed?scheme=https&url=%url%&format=json&maxwidth=450'
            ],
            'Soundcloud' => [
                'pattern' => '/soundcloud\.com/',
                'endpoint' => 'https://soundcloud.com/oembed?url=%url%&format=json&maxwidth=450'
            ],
            'Vimeo' => [
                'pattern' => '/vimeo\.com/',
                'endpoint' => 'https://vimeo.com/api/oembed.json?scheme=https&url=%url%&format=json&maxwidth=450'
            ],
            'SlideShare' => [
                'pattern' => '/slideshare\.net/',
                'endpoint' => 'https://www.slideshare.net/api/oembed/2?url=%url%&format=json&maxwidth=450'
            ]
        ];

        foreach (UrlOembed::getProviders() as $providerUrl => $providerEndpoint) {
            $providerExists = false;

            foreach ($oembedProviders as $provider) {
                if (preg_match($provider['pattern'], $providerUrl)) {
                    $providerExists = true;
                }
            }

            if (!$providerExists) {
                $oembedProviders[$providerUrl] = [
                    'pattern' => '/' . preg_quote($providerUrl, '/') . '/',
                    'endpoint' => $providerEndpoint
                ];
            }
        }

        $this->update('setting', ['value' => Json::encode($oembedProviders)], ['name' => 'oembedProviders', 'module_id' => 'base']);

        Yii::$app->settings->set('oembedProviders', Json::encode($oembedProviders));
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $oembedProvidersJson = Json::encode([
            'twitter.com'    => 'https://publish.twitter.com/oembed?url=%url%&maxwidth=450',
            'instagram.com'  => 'https://graph.facebook.com/v12.0/instagram_oembed?url=%url%&access_token=',
            'vimeo.com'      => 'https://vimeo.com/api/oembed.json?scheme=https&url=%url%&format=json&maxwidth=450',
            'youtube.com'    => 'https://www.youtube.com/oembed?scheme=https&url=%url%&format=json&maxwidth=450',
            'youtu.be'       => 'https://www.youtube.com/oembed?scheme=https&url=%url%&format=json&maxwidth=450',
            'soundcloud.com' => 'https://soundcloud.com/oembed?url=%url%&format=json&maxwidth=450',
            'slideshare.net' => 'https://www.slideshare.net/api/oembed/2?url=%url%&format=json&maxwidth=450',
        ]);

        $this->update('setting', ['value' => $oembedProvidersJson], ['name' => 'oembedProviders', 'module_id' => 'base']);

        Yii::$app->settings->set('oembedProviders', $oembedProvidersJson);
    }
}
