<?php

namespace humhub\tests\codeception\unit\models;

use humhub\libs\UrlOembedClient;
use yii\helpers\Json;

class UrlOembedClientMock implements UrlOembedClient
{
    public function fetchUrl($url)
    {
        $result = null;
        if (str_contains($url, UrlOembedMock::TEST_PROVIDER_URL_PREFIX)) {
            $result = [
                'html' => UrlOembedMock::TEST_VIDEO_A_PREVIEW,
                'type' => 'video',
            ];
        }

        return $result;
    }

}
