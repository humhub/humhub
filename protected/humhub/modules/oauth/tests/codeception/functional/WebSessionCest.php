<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\functional;

use FunctionalTester;
use humhub\components\gates\GateManager;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\WebSessionService;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;
use yii\helpers\Url;

class WebSessionCest
{
    private function appGrant(int $userId): Grant
    {
        return (new GrantService())->createOAuthGrant(User::findOne(['id' => $userId]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);
    }

    /**
     * Loads the code's URL the way the app does: with the secret header.
     */
    private function redeem(FunctionalTester $I, Grant $grant, array $params = [], ?string $secret = null): array
    {
        $credentials = (new WebSessionService())->createCode($grant);
        $this->open($I, $credentials['code'], $secret ?? $credentials['secret'], $params);

        return $credentials;
    }

    private function open(FunctionalTester $I, string $code, ?string $secret, array $params = []): void
    {
        if ($secret !== null) {
            $I->haveHttpHeader(WebSessionService::SECRET_HEADER, $secret);
        }
        $I->amOnRoute('/oauth/web-session/index', ['code' => $code] + $params);
        $I->deleteHeader(WebSessionService::SECRET_HEADER);
    }

    public function _after()
    {
        Grant::deleteAll();
    }

    public function testCodeLogsInAndBindsTheSession(FunctionalTester $I)
    {
        $grant = $this->appGrant(2);
        $this->redeem($I, $grant, ['returnUrl' => Url::to(['/user/account/edit'])]);

        Assert::assertSame(2, (int)Yii::$app->user->id);
        Assert::assertSame($grant->id, (int)Yii::$app->session->get(WebSessionService::SESSION_KEY_GRANT));
        Assert::assertTrue((bool)Yii::$app->session->get(GateManager::SESSION_KEY_ISSUED_CREDENTIAL));
        $I->seeInCurrentUrl('account');
    }

    public function testCodeIsSingleUse(FunctionalTester $I)
    {
        $credentials = $this->redeem($I, $this->appGrant(2));

        $this->open($I, $credentials['code'], $credentials['secret']);
        $I->seeResponseCodeIs(400);
    }

    public function testCodeWithoutSecretHeaderIsRejected(FunctionalTester $I)
    {
        $credentials = (new WebSessionService())->createCode($this->appGrant(2));

        $this->open($I, $credentials['code'], null);
        $I->seeResponseCodeIs(400);
        Assert::assertTrue(Yii::$app->user->isGuest);
    }

    public function testWrongSecretIsRejectedAndDoesNotConsumeTheCode(FunctionalTester $I)
    {
        $credentials = (new WebSessionService())->createCode($this->appGrant(2));

        $this->open($I, $credentials['code'], 'wrong');
        $I->seeResponseCodeIs(400);
        Assert::assertTrue(Yii::$app->user->isGuest);

        $this->open($I, $credentials['code'], $credentials['secret']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame(2, (int)Yii::$app->user->id);
    }

    public function testAbsoluteReturnUrlsAreIgnored(FunctionalTester $I)
    {
        $this->redeem($I, $this->appGrant(2), ['returnUrl' => 'https://evil.example/']);
        $I->dontSeeInCurrentUrl('evil');
    }

    public function testUnsafeReturnUrlsLandOnTheHomePage(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();
        foreach (['//evil.example', '/\\evil', "/\t/evil", 'https://evil.example/'] as $returnUrl) {
            $this->redeem($I, $this->appGrant(2), ['returnUrl' => $returnUrl]);
            $location = $I->grabHttpHeader('Location');
            Assert::assertStringNotContainsString('evil', $location, $returnUrl);
            Assert::assertStringEndsWith(Url::home(), $location, $returnUrl);
        }
    }

    public function testRedirectIsNotCached(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();
        $this->redeem($I, $this->appGrant(2));

        $I->seeResponseCodeIsRedirection();
        Assert::assertSame('no-store', $I->grabHttpHeader('Cache-Control'));
        Assert::assertSame('no-referrer', $I->grabHttpHeader('Referrer-Policy'));
    }

    public function testSameLoggedInUserGetsAFreshSession(FunctionalTester $I)
    {
        $I->amUser1();
        Assert::assertSame(2, (int)Yii::$app->user->id);
        $sessionId = Yii::$app->session->getId();
        Assert::assertNull(Yii::$app->session->get(WebSessionService::SESSION_KEY_GRANT));

        $grant = $this->appGrant(2);
        $this->redeem($I, $grant);

        Assert::assertSame(2, (int)Yii::$app->user->id);
        Assert::assertNotSame($sessionId, Yii::$app->session->getId());
        Assert::assertSame($grant->id, (int)Yii::$app->session->get(WebSessionService::SESSION_KEY_GRANT));
    }

    public function testRehandoffForTheSameGrantKeepsTheGrant(FunctionalTester $I)
    {
        $grant = $this->appGrant(2);
        $this->redeem($I, $grant);
        $this->redeem($I, $grant);

        Assert::assertNotNull(Grant::findOne(['id' => $grant->id]));
        Assert::assertSame(2, (int)Yii::$app->user->id);
        Assert::assertSame($grant->id, (int)Yii::$app->session->get(WebSessionService::SESSION_KEY_GRANT));
    }

    public function testADifferentLoggedInUserIsReplaced(FunctionalTester $I)
    {
        $I->amAdmin();
        $this->redeem($I, $this->appGrant(2));
        Assert::assertSame(2, (int)Yii::$app->user->id);
    }

    public function testRevokingTheGrantEndsTheSession(FunctionalTester $I)
    {
        $grant = $this->appGrant(2);
        $this->redeem($I, $grant);

        (new GrantService())->revoke($grant);

        $I->amOnRoute('/user/account/edit');
        $I->seeInCurrentUrl('login');
    }

    public function testLogoutInTheWebViewRevokesTheGrant(FunctionalTester $I)
    {
        $grant = $this->appGrant(2);
        $this->redeem($I, $grant);

        $I->sendAjaxPostRequest(Url::to(['/user/auth/logout']));

        Assert::assertNull(Grant::findOne(['id' => $grant->id]));
    }

    public function testRegularLogoutLeavesGrantsAlone(FunctionalTester $I)
    {
        $grant = $this->appGrant(1);
        $I->amAdmin();
        $I->sendAjaxPostRequest(Url::to(['/user/auth/logout']));

        Assert::assertNotNull(Grant::findOne(['id' => $grant->id]));
    }
}
