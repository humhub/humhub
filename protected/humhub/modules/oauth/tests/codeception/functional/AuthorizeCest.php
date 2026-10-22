<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;
use yii\helpers\Url;

class AuthorizeCest
{
    public function _before(FunctionalTester $I)
    {
        Module::instance()->getKeyStore()->generateIfMissing();
        $this->cleanUp();
    }

    public function _after(FunctionalTester $I)
    {
        $this->cleanUp();
    }

    private function cleanUp(): void
    {
        Grant::deleteAll();
        Client::deleteAll(['not', ['client_id' => Client::SYSTEM_CLIENT_ID]]);
        Module::instance()->settings->delete('allowClients');
        Module::instance()->settings->delete('allowUserClients');
    }

    private function params(string $clientId = Client::SYSTEM_CLIENT_ID, string $scope = '', string $redirectUri = OAuthTestHelper::REDIRECT_URI): array
    {
        return [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => 'xyz',
            'scope' => $scope,
            'code_challenge' => OAuthTestHelper::challenge(str_repeat('v', 64)),
            'code_challenge_method' => 'S256',
        ];
    }

    public function testGuestIsSentToTheLogin(FunctionalTester $I)
    {
        $I->amOnRoute('/oauth/authorize/index', $this->params());
        $I->seeInCurrentUrl(ltrim(Url::to(['/user/auth/login']), '/'));
    }

    public function testAppSignInShowsTheConfirmationAndCreatesAGrant(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/authorize/index', $this->params());
        $I->see('Sign in to the HumHub app on this device?');

        $grants = Grant::find()->where(['user_id' => 1])->count();
        $I->stopFollowingRedirects();
        $I->click('Allow');
        $I->seeResponseCodeIsRedirection();
        Assert::assertEquals($grants + 1, Grant::find()->where(['user_id' => 1])->count());
    }

    public function testDenyCreatesNoGrant(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/authorize/index', $this->params());

        $grants = Grant::find()->count();
        $I->stopFollowingRedirects();
        $I->click('Deny');
        $I->seeResponseCodeIsRedirection();
        Assert::assertEquals($grants, Grant::find()->count());
    }

    public function testAdminClientShowsItsScopes(FunctionalTester $I)
    {
        Yii::$app->getModule('oauth')->settings->set('allowClients', true);
        (new Client([
            'client_id' => 'agent', 'type' => Client::TYPE_ADMIN, 'name' => 'Research Agent',
            'redirect_uris' => json_encode(['http://127.0.0.1/callback']),
            'scopes' => json_encode(['content:read', 'spaces:read']),
            'is_active' => 1, 'created_at' => time(),
        ]))->save(false);

        $I->amAdmin();
        $I->amOnRoute('/oauth/authorize/index', $this->params('agent', 'content:read', 'http://127.0.0.1:43123/callback'));
        $I->see('Research Agent');
        $I->see('Read content, comments and likes');
        $I->dontSee('Read spaces and memberships');
    }

    public function testUserClientIsOnlyUsableByItsOwner(FunctionalTester $I)
    {
        Yii::$app->getModule('oauth')->settings->set('allowUserClients', true);
        (new Client([
            'client_id' => 'mine', 'type' => Client::TYPE_USER, 'name' => 'My Script', 'owner_user_id' => 2,
            'redirect_uris' => json_encode(['http://127.0.0.1/callback']),
            'scopes' => json_encode(['content:read']), 'is_active' => 1, 'created_at' => time(),
        ]))->save(false);

        $I->amAdmin();
        $I->amOnRoute('/oauth/authorize/index', $this->params('mine', 'content:read', 'http://127.0.0.1/callback'));
        $I->seeResponseCodeIs(403);
    }

    public function testNoAuthorizationWhileImpersonating(FunctionalTester $I)
    {
        $I->amAdmin();
        Yii::$app->user->getImpersonation()->start(User::findOne(['id' => 2]));

        $I->amOnRoute('/oauth/authorize/index', $this->params());
        $I->seeResponseCodeIs(403);
    }

    private function createAdminClient(array $attributes = []): void
    {
        Module::instance()->settings->set('allowClients', true);
        (new Client($attributes + [
            'client_id' => 'agent', 'type' => Client::TYPE_ADMIN, 'name' => 'Research Agent',
            'redirect_uris' => json_encode(['http://127.0.0.1/callback']),
            'scopes' => json_encode(['content:read']),
            'is_active' => 1, 'created_at' => time(),
        ]))->save(false);
    }

    private function location(): string
    {
        return Yii::$app->response->headers->get('Location') ?? '';
    }

    public function testPlainPkceIsRejected(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->stopFollowingRedirects();
        $I->amOnRoute('/oauth/authorize/index', ['code_challenge_method' => 'plain'] + $this->params());
        $I->seeResponseCodeIsRedirection();
        Assert::assertStringContainsString('error=invalid_request', $this->location());
        Assert::assertStringContainsString('state=xyz', $this->location());
    }

    public function testUnallowedScopeIsRejected(FunctionalTester $I)
    {
        $this->createAdminClient();
        $I->amAdmin();
        $I->stopFollowingRedirects();
        $I->amOnRoute('/oauth/authorize/index', $this->params('agent', 'spaces:read', 'http://127.0.0.1/callback'));
        $I->seeResponseCodeIsRedirection();
        Assert::assertStringContainsString('error=invalid_scope', $this->location());
        Assert::assertStringContainsString('state=xyz', $this->location());
    }

    public function testAllowRedirectsWithCodeAndState(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/authorize/index', $this->params());
        $I->stopFollowingRedirects();
        $I->click('Allow');
        Assert::assertStringStartsWith(OAuthTestHelper::REDIRECT_URI . '?', $this->location());
        Assert::assertStringContainsString('code=', $this->location());
        Assert::assertStringContainsString('state=xyz', $this->location());
    }

    public function testDenyRedirectsWithAccessDenied(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/authorize/index', $this->params());
        $I->stopFollowingRedirects();
        $I->click('Deny');
        Assert::assertStringContainsString('error=access_denied', $this->location());
        Assert::assertStringContainsString('state=xyz', $this->location());
    }

    public function testRequestIdIsSingleUse(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/authorize/index', $this->params());
        $requestId = $I->grabValueFrom('input[name=request]');
        $I->stopFollowingRedirects();
        $I->sendAjaxPostRequest(Url::to(['/oauth/authorize/index']), ['request' => $requestId, 'decision' => 'deny']);
        $I->seeResponseCodeIsRedirection();
        $I->sendAjaxPostRequest(Url::to(['/oauth/authorize/index']), ['request' => $requestId, 'decision' => 'allow']);
        $I->seeResponseCodeIs(400);
    }

    public function testSkipConsentRedirectsImmediately(FunctionalTester $I)
    {
        $this->createAdminClient(['skip_consent' => 1]);
        $I->amAdmin();
        $I->stopFollowingRedirects();
        $I->amOnRoute('/oauth/authorize/index', $this->params('agent', 'content:read', 'http://127.0.0.1/callback'));
        $I->seeResponseCodeIsRedirection();
        Assert::assertStringContainsString('code=', $this->location());
        Assert::assertEquals(1, Grant::find()->where(['user_id' => 1])->count());
    }
}
