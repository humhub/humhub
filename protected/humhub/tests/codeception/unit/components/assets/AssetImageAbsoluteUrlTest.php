<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\assets;

use humhub\components\assets\AssetImage;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\StringHelper;

/**
 * Absolute AssetImage URLs leave the platform (e.g. in emails) and are never rendered again,
 * so they must not point into the volatile published assets.
 */
class AssetImageAbsoluteUrlTest extends HumHubDbTestCase
{
    private const OPTIONS = ['width' => 40, 'height' => 40];

    protected function _before()
    {
        parent::_before();
        Yii::$app->settings->set('secret', 'asset-image-test-secret');
    }

    public function testAbsoluteUrlPointsToController()
    {
        $url = $this->createAssetImage()->getUrl(self::OPTIONS, true);

        $this->assertStringContainsString('asset-image', $url);
        $this->assertStringNotContainsString('/assets/', $url);
        $this->assertStringStartsWith('http', $url);
    }

    public function testRelativeUrlPointsToPublishedAssets()
    {
        $this->assertStringContainsString('/assets/', $this->createAssetImage()->getUrl(self::OPTIONS));
    }

    public function testTokenResolvesImageAndOptions()
    {
        [$image, $options] = AssetImage::fromToken($this->getToken($this->createAssetImage()->getUrl(self::OPTIONS, true)));

        $this->assertSame('/tests/asset-image/absolute-url.jpg', $image->file);
        $this->assertSame(Yii::getAlias('@humhub/resources/img/default_user.jpg'), $image->defaultFile);
        $this->assertSame(self::OPTIONS, $options);
    }

    public function testTokenIsIndependentOfInstallationPath()
    {
        $token = $this->getToken($this->createAssetImage()->getUrl(self::OPTIONS, true));

        $this->assertStringNotContainsString(Yii::getAlias('@webroot'), StringHelper::base64UrlDecode($token));
    }

    public function testTamperedTokenIsRejected()
    {
        $data = StringHelper::base64UrlDecode($this->getToken($this->createAssetImage()->getUrl(self::OPTIONS, true)));
        $tampered = str_replace('absolute-url.jpg', 'private-file.jpg', $data);

        $this->assertNull(AssetImage::fromToken(StringHelper::base64UrlEncode($tampered)));
        $this->assertNull(AssetImage::fromToken('invalid'));
    }

    public function testImageWithoutFileAndDefaultHasNoUrl()
    {
        $image = new AssetImage(['file' => '/tests/asset-image/missing.jpg']);

        $this->assertSame('', $image->getUrl(null, true));
    }

    private function createAssetImage(): AssetImage
    {
        return new AssetImage([
            'file' => '/tests/asset-image/absolute-url.jpg',
            'defaultFile' => '@humhub/resources/img/default_user.jpg',
        ]);
    }

    private function getToken(string $url): string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $query['i'];
    }
}
