<?php

namespace humhub\tests\codeception\unit\models;

use humhub\models\UrlOembed;
use tests\codeception\_support\HumHubDbTestCase;

class UrlOembedTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function _before()
    {
        parent::_before();
        UrlOembedMock::setClient(new UrlOembedClientMock());
        UrlOembedMock::setProviders([
            'Test.de' => [
                'pattern' => '/test\.de/',
                'endpoint' => UrlOembedMock::TEST_PROVIDER_URL_PREFIX . '%url%',
            ],
        ]);
        UrlOembedMock::flush();
    }

    public function testProviderUrl()
    {
        $oembed = new UrlOembedMock(['url' => UrlOembedMock::TEST_VIDEO_URL_A]);
        $this->assertEquals(UrlOembedMock::TEST_PROVIDER_URL_PREFIX . urlencode(UrlOembedMock::TEST_VIDEO_URL_A), $oembed->getProviderUrl());
    }

    public function testInvalidProviderUrl()
    {
        $oembed = new UrlOembed(['url' => 'https://www.youtube.com']);
        $this->assertNull($oembed->getProviderUrl());
    }

    public function testHasOEmbedSupport()
    {
        $this->assertTrue(UrlOembed::hasOEmbedSupport(UrlOembedMock::TEST_VIDEO_URL_A));
        $this->assertFalse(UrlOembed::hasOEmbedSupport('https://www.youtube.com'));
    }

    public function testFetchValidUrl()
    {
        $result = UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A);
        $this->assertNotNull($result);
        $this->assertStringContainsString(UrlOembedMock::TEST_VIDEO_A_PREVIEW, $result);
        $this->assertEquals(1, $this->getOembedRecordCount());
    }

    public function testFetchInvalidUrl()
    {
        $result = UrlOembedMock::getOEmbed('https://www.youtube.com');
        $this->assertNull($result);
        $this->assertEquals(0, $this->getOembedRecordCount());
    }

    public function testGetOembedFetchLimit()
    {
        UrlOembedMock::$maxUrlFetchLimit = 3;
        UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=1');
        UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=2');
        UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=3');

        UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=4');
        UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=5');
        UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=6');

        $this->assertEquals(3, UrlOembedMock::getFetchUrlCount());
        $this->assertEquals(3, $this->getOembedRecordCount());

        $this->assertTrue(UrlOembedMock::isCached(UrlOembedMock::TEST_VIDEO_URL_A . '&a=1'));
        $this->assertTrue(UrlOembedMock::isCached(UrlOembedMock::TEST_VIDEO_URL_A . '&a=2'));
        $this->assertTrue(UrlOembedMock::isCached(UrlOembedMock::TEST_VIDEO_URL_A . '&a=3'));

        $this->assertFalse(UrlOembedMock::isCached(UrlOembedMock::TEST_VIDEO_URL_A . '&a=4'));
        $this->assertFalse(UrlOembedMock::isCached(UrlOembedMock::TEST_VIDEO_URL_A . '&a=5'));
        $this->assertFalse(UrlOembedMock::isCached(UrlOembedMock::TEST_VIDEO_URL_A . '&a=6'));
    }

    public function testPreloadFetchLimit()
    {
        UrlOembedMock::$maxUrlFetchLimit = 3;

        $text = UrlOembedMock::TEST_VIDEO_URL_A . '&a=1 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=2 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=3 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=4 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=5 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=6 ';

        UrlOembedMock::preload($text);
        $this->assertEquals(3, UrlOembedMock::getFetchUrlCount());
    }

    public function testUrlLoadException()
    {
        UrlOembedMock::$maxUrlFetchLimit = 6;

        $text = UrlOembedMock::TEST_VIDEO_URL_A . '&a=1 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=2 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=3 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=4 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=5 '
            . UrlOembedMock::TEST_VIDEO_URL_A . '&a=6 ';

        UrlOembedMock::preload($text);

        $this->assertNotNull(UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=1'));
        $this->assertNotNull(UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=2'));
        $this->assertNotNull(UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=3'));

        $this->assertEquals(6, $this->getOembedRecordCount());
        $this->assertEquals(6, UrlOembedMock::getFetchUrlCount());

        UrlOembedMock::$maxUrlLoadLimit = 2;

        UrlOembedMock::flush();

        $this->assertNotNull(UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=1'));
        $this->assertNotNull(UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=2'));
        $this->assertNull(UrlOembedMock::getOEmbed(UrlOembedMock::TEST_VIDEO_URL_A . '&a=3'));

        $this->assertEquals(2, UrlOembedMock::getFetchLoadCount());
    }

    private function getOembedRecordCount()
    {
        return UrlOembedMock::find()->count();
    }


}
