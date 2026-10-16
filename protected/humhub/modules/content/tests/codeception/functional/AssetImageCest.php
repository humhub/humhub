<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace content\functional;

use content\FunctionalTester;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;
use yii\helpers\Url;

/**
 * The permanent image URLs are linked from emails, so they are fetched without any session:
 * they must resolve for guests and redirect to the current URL of the published image.
 */
class AssetImageCest
{
    public function testRegistryImageRedirectsToPublishedImage(FunctionalTester $I)
    {
        $I->wantTo('ensure a permanent registry image URL redirects to the published image');
        $I->stopFollowingRedirects();

        $I->amOnPage(Yii::$app->img->icon->getPermanentUrl());

        $this->seeRedirectTo($I, Yii::$app->img->icon->getUrl(null, true));
    }

    public function testContainerImageRedirectsToPublishedImage(FunctionalTester $I)
    {
        $I->wantTo('ensure a permanent profile image URL redirects to the published image');
        $I->stopFollowingRedirects();

        $user = User::findOne(['id' => 2]);
        $I->amOnPage($user->image->getPermanentUrl());
        $this->seeRedirectTo($I, $user->image->getUrl(null, true));

        $I->amOnPage($user->bannerImage->getPermanentUrl());
        $this->seeRedirectTo($I, $user->bannerImage->getUrl(null, true));
    }

    public function testUnknownImages(FunctionalTester $I)
    {
        $I->wantTo('ensure unknown images are rejected');
        $I->stopFollowingRedirects();

        $I->amOnPage(Url::to(['/asset-image/registry', 'name' => 'unknown']));
        $I->seeResponseCodeIs(404);

        $I->amOnPage(Url::to(['/asset-image/container', 'guid' => 'unknown-guid']));
        $I->seeResponseCodeIs(404);
    }

    private function seeRedirectTo(FunctionalTester $I, string $url): void
    {
        $I->seeResponseCodeIs(302);
        Assert::assertSame($url, Yii::$app->response->headers->get('location'));
        Assert::assertStringContainsString('/assets/', $url);
    }
}
