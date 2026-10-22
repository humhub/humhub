<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\notification\models\Notification;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\notifications\GrantCreatedNotification;
use humhub\modules\oauth\permissions\CreatePersonalAccessToken;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\GroupPermission;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

class AccountCest
{
    private const USER1_GROUP = 2;
    private const PASSWORD = 'user^humhub@PASS%worD!';
    private const SAFARI_IOS = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

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
        GroupPermission::deleteAll(['class' => CreatePersonalAccessToken::class]);
        Notification::deleteAll(['class' => GrantCreatedNotification::class]);
        Module::instance()->settings->delete('allowPersonalAccessTokens');
        Module::instance()->settings->delete('allowClients');
        // Failed password confirmations are throttled via the cache
        Yii::$app->cache->flush();
    }

    private function usedGrant(int $userId, string $clientId, array $scopes, array $attributes = []): Grant
    {
        $grant = (new GrantService())->createOAuthGrant(User::findOne(['id' => $userId]), Client::findByClientId($clientId), $scopes);
        $grant->updateAttributes($attributes + ['last_used_at' => time()]);

        return $grant;
    }

    private function revoke(FunctionalTester $I, Grant $grant): void
    {
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Frevoke&id=' . $grant->id);
    }

    private function allowTokens(FunctionalTester $I): void
    {
        Module::instance()->settings->set('allowPersonalAccessTokens', true);
        $I->setGroupPermission(self::USER1_GROUP, CreatePersonalAccessToken::class);
    }

    public function testDevicesAreListedAndSignedOut(FunctionalTester $I)
    {
        $grant = $this->usedGrant(2, Client::SYSTEM_CLIENT_ID, ['full'], ['user_agent' => self::SAFARI_IOS]);

        $I->amUser1();
        $I->amOnRoute('/user/security/login');
        $I->see('Apps & Tokens');

        $I->amOnRoute('/oauth/account/index');
        $I->see('Devices');
        $I->see('Safari · iOS');
        $I->see('Sign out');

        $this->revoke($I, $grant);
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testConnectedAppsAreListedAndDisconnected(FunctionalTester $I)
    {
        Module::instance()->settings->set('allowClients', true);
        (new Client([
            'client_id' => 'agent', 'type' => Client::TYPE_ADMIN, 'name' => 'Research Agent',
            'redirect_uris' => json_encode(['http://127.0.0.1/callback']),
            'scopes' => json_encode(['content:read', 'spaces:read']),
            'is_active' => 1, 'created_at' => time(),
        ]))->save(false);
        $grant = $this->usedGrant(2, 'agent', ['content:read', 'unknown:scope']);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->see('Connected apps');
        $I->see('Research Agent');
        $I->see('Read content, comments and likes');
        $I->dontSee('Read spaces and memberships');
        $I->dontSee('unknown:scope');
        $I->dontSee('Devices');

        $this->revoke($I, $grant);
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testUnusedGrantsAreNotListed(FunctionalTester $I)
    {
        (new GrantService())->createOAuthGrant(User::findOne(['id' => 2]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->dontSee('Devices');
        $I->see('No apps or tokens have access to your account.');
    }

    public function testTokenIsCreatedShownOnceAndRevoked(FunctionalTester $I)
    {
        $this->allowTokens($I);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->see('Personal access tokens');
        $I->see('Create token');

        $I->amOnRoute('/oauth/account/create-token');
        $I->submitForm('form[action*="create-token"]', [
            'PersonalAccessTokenForm[name]' => 'Backup script',
            'PersonalAccessTokenForm[scopes]' => ['content:read'],
            'PersonalAccessTokenForm[lifetime]' => '30',
            'PersonalAccessTokenForm[currentPassword]' => 'wrong',
        ]);
        $I->see('Your password is incorrect.');
        Assert::assertFalse(Grant::find()->where(['type' => Grant::TYPE_PAT])->exists());

        $I->submitForm('form[action*="create-token"]', [
            'PersonalAccessTokenForm[name]' => 'Backup script',
            'PersonalAccessTokenForm[scopes]' => ['content:read'],
            'PersonalAccessTokenForm[lifetime]' => '30',
            'PersonalAccessTokenForm[currentPassword]' => self::PASSWORD,
        ]);
        Assert::assertSame(1, preg_match('/hhpat_[A-Za-z0-9]{38}/', $I->grabPageSource(), $match));
        $I->see('Copy the token now.');

        $grant = (new PersonalAccessTokenService())->find($match[0]);
        Assert::assertNotNull($grant);
        Assert::assertSame(2, (int)$grant->user_id);
        Assert::assertSame('Backup script', $grant->name);
        Assert::assertSame(['content:read'], $grant->getScopeIds());
        Assert::assertEqualsWithDelta(time() + 30 * 86400, $grant->expires_at, 60);

        $I->amOnRoute('/oauth/account/index');
        $I->see('Backup script');
        $I->see('hhpat_…' . $grant->token_hint);
        $I->dontSee($match[0]);

        $this->revoke($I, $grant);
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testRepeatedWrongPasswordsAreThrottled(FunctionalTester $I)
    {
        $this->allowTokens($I);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/create-token');
        for ($i = 0; $i < 6; $i++) {
            $I->submitForm('form[action*="create-token"]', [
                'PersonalAccessTokenForm[name]' => 'Backup script',
                'PersonalAccessTokenForm[scopes]' => ['content:read'],
                'PersonalAccessTokenForm[currentPassword]' => $i < 5 ? 'wrong' : self::PASSWORD,
            ]);
        }

        $I->see('Too many attempts, try again later.');
        Assert::assertFalse(Grant::find()->where(['type' => Grant::TYPE_PAT])->exists());
    }

    public function testTokensCannotBeCreatedWithoutPermission(FunctionalTester $I)
    {
        Module::instance()->settings->set('allowPersonalAccessTokens', true);
        $this->usedGrant(2, Client::SYSTEM_CLIENT_ID, ['full']);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->dontSee('Create token');

        $I->amOnRoute('/oauth/account/create-token');
        $I->seeResponseCodeIs(403);
    }

    public function testForeignGrantCannotBeRevoked(FunctionalTester $I)
    {
        $this->usedGrant(2, Client::SYSTEM_CLIENT_ID, ['full']);
        $foreign = $this->usedGrant(3, Client::SYSTEM_CLIENT_ID, ['full']);

        $I->amUser1();
        $this->revoke($I, $foreign);
        $I->seeResponseCodeIs(404);
        Assert::assertTrue(Grant::find()->where(['id' => $foreign->id])->exists());
    }

    public function testTabIsHiddenWithoutGrantsOrPermissions(FunctionalTester $I)
    {
        Module::instance()->settings->set('allowPersonalAccessTokens', true);

        $I->amUser1();
        $I->amOnRoute('/user/security/login');
        $I->dontSee('Apps & Tokens');

        $I->amOnRoute('/oauth/account/index');
        $I->seeResponseCodeIs(404);
    }
}
