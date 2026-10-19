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
use yii\helpers\FileHelper;
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

    public function testTokenStoresDefaultFileRelativeToHumhubAlias()
    {
        $data = StringHelper::base64UrlDecode($this->getToken($this->createAssetImage()->getUrl(self::OPTIONS, true)));

        $this->assertStringContainsString('"@humhub/resources/img/default_user.jpg"', $data);
    }

    public function testTokenResolvesDefaultFileBelowSymlinkedWebroot()
    {
        // In web requests `@webroot` is the script directory, which may be a symlink to the
        // installation, while default files are built from resolved paths
        $real = Yii::getAlias('@runtime/asset-image-real');
        $link = Yii::getAlias('@runtime/asset-image-link');
        FileHelper::createDirectory($real);
        @unlink($link);
        symlink($real, $link);
        $webroot = Yii::getAlias('@webroot');
        Yii::setAlias('@webroot', $link);

        try {
            $image = new AssetImage(['file' => '/tests/asset-image/missing.jpg', 'defaultFile' => $real . '/default.jpg']);
            $data = StringHelper::base64UrlDecode($this->getToken($image->getUrl(self::OPTIONS, true)));

            $this->assertStringContainsString('"@webroot/default.jpg"', $data);
        } finally {
            Yii::setAlias('@webroot', $webroot);
            unlink($link);
            FileHelper::removeDirectory($real);
        }
    }

    public function testTokenIsCompact()
    {
        // The token is part of every image URL in an email: a raw 128-bit signature instead of a
        // 64-character hex hash keeps it short.
        $this->assertLessThan(200, strlen($this->getToken($this->createAssetImage()->getUrl(self::OPTIONS, true))));
    }

    public function testCookieSignedPayloadIsRejected()
    {
        // The installation secret also validates cookies: a replayed cookie value must neither pass
        // the token validation nor break the decoding
        $cookie = Yii::$app->security->hashData(serialize(['_csrf', 'x']), Yii::$app->settings->get('secret'));

        $this->assertNull(AssetImage::fromToken(StringHelper::base64UrlEncode($cookie)));
    }

    public function testValidlySignedNonJsonPayloadIsRejected()
    {
        $this->assertNull(AssetImage::fromToken($this->signToken('not json')));
        $this->assertNull(AssetImage::fromToken($this->signToken('{"file":"x"}')));
        $this->assertNull(AssetImage::fromToken($this->signToken('["", [], null]')));
        $this->assertNull(AssetImage::fromToken($this->signToken('123')));
    }

    private function createAssetImage(): AssetImage
    {
        return new AssetImage([
            'file' => '/tests/asset-image/absolute-url.jpg',
            'defaultFile' => '@humhub/resources/img/default_user.jpg',
        ]);
    }

    /**
     * Signs a payload like {@see AssetImage::getUrl()} does: 128-bit raw HMAC with a key derived
     * from the installation secret for this purpose only, followed by the payload.
     */
    private function signToken(string $data): string
    {
        $key = Yii::$app->security->hkdf('sha256', Yii::$app->settings->get('secret'), null, 'asset-image');

        return StringHelper::base64UrlEncode(substr(hash_hmac('sha256', $data, $key, true), 0, 16) . $data);
    }

    private function getToken(string $url): string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $query['i'];
    }
}
