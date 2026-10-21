<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace content\functional;

use content\FunctionalTester;
use humhub\modules\user\components\MaintenanceModeGate;
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

    public function testAbsoluteUrlResolvesInMaintenanceMode(FunctionalTester $I)
    {
        $I->wantTo('ensure an absolute image URL still resolves while maintenance mode is active');
        $I->stopFollowingRedirects();

        $url = User::findOne(['id' => 2])->image->getUrl(null, true);
        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        try {
            $I->amOnPage($url);
            $I->seeResponseCodeIs(302);
            Assert::assertStringContainsString('/assets/', (string) Yii::$app->response->headers->get('location'));
        } finally {
            Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 0);
        }
    }

    public function testAbsoluteUrlRedirectsAjaxRequests(FunctionalTester $I)
    {
        $I->wantTo('ensure an absolute image URL redirects a script fetching it via XHR as well');
        $I->stopFollowingRedirects();
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');

        $I->amOnPage(User::findOne(['id' => 2])->image->getUrl(null, true));
        $I->seeResponseCodeIs(302);
        Assert::assertStringContainsString('/assets/', (string) Yii::$app->response->headers->get('location'));
    }

    public function testInvalidTokenIsRejected(FunctionalTester $I)
    {
        $I->wantTo('ensure an invalid image token is rejected');
        $I->stopFollowingRedirects();

        $I->amOnPage(Url::to(['/asset-image', 'i' => 'invalid']));
        $I->seeResponseCodeIs(404);
    }
}
