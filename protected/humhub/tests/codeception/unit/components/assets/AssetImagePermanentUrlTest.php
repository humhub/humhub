<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\assets;

use humhub\components\assets\AssetImage;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\Url;

/**
 * The permanent URL of an AssetImage is used wherever the URL leaves the platform (e.g. in emails):
 * it must never point into the volatile published assets.
 */
class AssetImagePermanentUrlTest extends HumHubDbTestCase
{
    public function testRegistryImageUrlPointsToController()
    {
        $url = Yii::$app->img->icon->getPermanentUrl(); // Has a default file, so it always resolves

        $this->assertSame(Url::to(['/asset-image/registry', 'name' => 'icon'], true), $url);
        $this->assertStringNotContainsString('/assets/', $url);
    }

    public function testContainerImagesHaveDistinctRoutes()
    {
        $user = User::findOne(['id' => 1]);

        $this->assertSame(
            Url::to(['/asset-image/container', 'guid' => $user->guid], true),
            $user->image->getPermanentUrl(),
        );
        $this->assertSame(
            Url::to(['/asset-image/container', 'guid' => $user->guid, 'type' => 'banner'], true),
            $user->bannerImage->getPermanentUrl(),
        );
    }

    public function testMailLogoSharesTheLogoFile()
    {
        $this->assertSame(Yii::$app->img->logo->file, Yii::$app->img->mailLogo->file);
        $this->assertNotEmpty(Yii::$app->img->mailLogo->defaultOptions);
    }

    public function testImageWithoutRouteFallsBackToAbsoluteUrl()
    {
        $image = new AssetImage([
            'file' => '/tests/asset-image/no-route.jpg',
            'defaultFile' => '@humhub/resources/img/default_user.jpg',
        ]);

        $this->assertSame($image->getUrl(null, true), $image->getPermanentUrl());
        $this->assertStringStartsWith('http', $image->getPermanentUrl());
    }

    public function testImageWithoutFileAndDefaultHasNoPermanentUrl()
    {
        $image = new AssetImage([
            'file' => '/tests/asset-image/missing.jpg',
            'permanentUrlRoute' => ['/asset-image/registry', 'name' => 'missing'],
        ]);

        $this->assertSame('', $image->getPermanentUrl());
    }
}
