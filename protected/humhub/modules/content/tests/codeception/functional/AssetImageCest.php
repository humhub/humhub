<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace content\functional;

use content\FunctionalTester;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;
use yii\helpers\Url;

/**
 * Absolute image URLs are linked from emails, so they are fetched without any session: they must
 * resolve for guests, even after the published assets were removed, and redirect to the image.
 */
class AssetImageCest
{
    public function _before()
    {
        Yii::$app->settings->set('secret', 'asset-image-test-secret');
    }

    public function testAbsoluteUrlRedirectsToPublishedImage(FunctionalTester $I)
    {
        $I->wantTo('ensure an absolute image URL redirects to the published image after the assets were removed');
        $I->stopFollowingRedirects();

        $user = User::findOne(['id' => 2]);
        $url = $user->image->getUrl(null, true);

        Yii::$app->assetManager->clear();

        $I->amOnPage($url);
        $I->seeResponseCodeIs(302);

        $location = Yii::$app->response->headers->get('location');
        Assert::assertStringContainsString('/assets/', $location);
        Assert::assertSame(Url::to($user->image->getUrl(), true), $location);
    }

    public function testInvalidTokenIsRejected(FunctionalTester $I)
    {
        $I->wantTo('ensure an invalid image token is rejected');
        $I->stopFollowingRedirects();

        $I->amOnPage(Url::to(['/asset-image', 'i' => 'invalid']));
        $I->seeResponseCodeIs(404);
    }
}
