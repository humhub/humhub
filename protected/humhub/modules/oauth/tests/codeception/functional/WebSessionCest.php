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

    public function _after()
    {
        Grant::deleteAll();
    }

    public function testCodeLogsInAndBindsTheSession(FunctionalTester $I)
    {
        $grant = $this->appGrant(2);
        $code = (new WebSessionService())->createCode($grant);

        $I->amOnRoute('/oauth/web-session/index', ['code' => $code, 'returnUrl' => Url::to(['/user/account/edit'])]);

        Assert::assertSame(2, (int)Yii::$app->user->id);
        Assert::assertSame($grant->id, (int)Yii::$app->session->get(WebSessionService::SESSION_KEY_GRANT));
        Assert::assertTrue((bool)Yii::$app->session->get(GateManager::SESSION_KEY_ISSUED_CREDENTIAL));
        $I->seeInCurrentUrl('account');
    }

    public function testCodeIsSingleUse(FunctionalTester $I)
    {
        $code = (new WebSessionService())->createCode($this->appGrant(2));

        $I->amOnRoute('/oauth/web-session/index', ['code' => $code]);
        $I->amOnRoute('/oauth/web-session/index', ['code' => $code]);
        $I->seeResponseCodeIs(400);
    }

    public function testAbsoluteReturnUrlsAreIgnored(FunctionalTester $I)
    {
        $code = (new WebSessionService())->createCode($this->appGrant(2));

        $I->amOnRoute('/oauth/web-session/index', ['code' => $code, 'returnUrl' => 'https://evil.example/']);
        $I->dontSeeInCurrentUrl('evil');
    }

    public function testADifferentLoggedInUserIsReplaced(FunctionalTester $I)
    {
        $I->amAdmin();
        $code = (new WebSessionService())->createCode($this->appGrant(2));

        $I->amOnRoute('/oauth/web-session/index', ['code' => $code]);
        Assert::assertSame(2, (int)Yii::$app->user->id);
    }

    public function testRevokingTheGrantEndsTheSession(FunctionalTester $I)
    {
        $grant = $this->appGrant(2);
        $I->amOnRoute('/oauth/web-session/index', ['code' => (new WebSessionService())->createCode($grant)]);

        (new GrantService())->revoke($grant);

        $I->amOnRoute('/user/account/edit');
        $I->seeInCurrentUrl('login');
    }

    public function testLogoutInTheWebViewRevokesTheGrant(FunctionalTester $I)
    {
        $grant = $this->appGrant(2);
        $I->amOnRoute('/oauth/web-session/index', ['code' => (new WebSessionService())->createCode($grant)]);

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
