<?php


namespace humhub\tests\codeception\unit\models;


use humhub\libs\UrlOembedClient;
use yii\helpers\Json;

class UrlOembedClientMock implements UrlOembedClient
{
    public function fetchUrl($url)
    {
        $result = null;
        if (strpos($url, UrlOembedMock::TEST_PROVIDER_URL_PREFIX) !== false) {
            $result = [
                'html' => UrlOembedMock::TEST_VIDEO_A_PREVIEW,
                'type' => 'video'
            ];
        }

        return $result;
    }

}