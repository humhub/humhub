<?php


namespace humhub\tests\codeception\unit\models;


use humhub\models\UrlOembed;

class UrlOembedMock extends UrlOembed
{
    const TEST_PROVIDER_URL_PREFIX = 'https://www.test.de/oembed?url=';

    const TEST_VIDEO_URL_A = 'https://www.test.de/videoA';

    const TEST_VIDEO_A_PREVIEW = 'Test Video A';

    public static function getTestProviderUrl($url)
    {
        $oembed = new static(['url' => $url]);
        return $oembed->getProviderUrl();
    }

    public static function getFetchUrlCount()
    {
        return static::$urlsFetched;
    }

    public static function getFetchLoadCount()
    {
        return static::$urlsLoaded;
    }

    public static function isCached($url)
    {
        return array_key_exists($url, static::$cache);
    }

}