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
use humhub\modules\oauth\permissions\ManageOwnOAuthClients;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\GroupPermission;
use humhub\modules\user\models\Password;
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
        GroupPermission::deleteAll(['class' => [CreatePersonalAccessToken::class, ManageOwnOAuthClients::class]]);
        Notification::deleteAll(['class' => GrantCreatedNotification::class]);
        Module::instance()->settings->delete('allowPersonalAccessTokens');
        Module::instance()->settings->delete('allowClients');
        Module::instance()->settings->delete('allowUserClients');
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

    private function allowUserClients(FunctionalTester $I): void
    {
        Module::instance()->settings->set('allowUserClients', true);
        $I->setGroupPermission(self::USER1_GROUP, ManageOwnOAuthClients::class);
    }

    private function userClient(int $ownerId, array $attributes = []): Client
    {
        $client = new Client($attributes + [
            'client_id' => 'own-' . $ownerId, 'type' => Client::TYPE_USER, 'name' => 'Own app',
            'owner_user_id' => $ownerId, 'secret_hash' => hash('sha256', 'old-secret'),
            'redirect_uris' => json_encode(['https://example.org/cb']),
            'scopes' => json_encode(['content:read']),
            'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);

        return $client;
    }

    private function grabSecret(FunctionalTester $I): string
    {
        Assert::assertSame(1, preg_match('/id="oauth-client-secret"[^>]*value="([^"]+)"/', $I->grabPageSource(), $match));

        return $match[1];
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

        // Nothing to list, so no tab
        $I->amUser1();
        $I->amOnRoute('/user/security/login');
        $I->dontSee('Apps & Tokens');
        $I->amOnRoute('/oauth/account/index');
        $I->seeResponseCodeIs(404);
    }

    public function testExpiredTokensKeepTheTab(FunctionalTester $I)
    {
        [, $grant] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), 'Old script', ['content:read'], 30);
        $grant->updateAttributes(['expires_at' => time() - 60]);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->see('Old script');
        $I->see('Expired');
    }

    public function testNamesAreEncoded(FunctionalTester $I)
    {
        $this->allowUserClients($I);
        $client = $this->userClient(2, ['name' => '<script>alert(1)</script>']);
        $this->usedGrant(2, $client->client_id, ['content:read']);
        (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), '<script>alert(2)</script>', ['content:read'], 30);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->see('Connected apps');
        $I->see('My OAuth apps');
        $I->see('Personal access tokens');
        $source = $I->grabPageSource();
        Assert::assertStringNotContainsString('<script>alert(', $source);
        Assert::assertSame(2, substr_count($source, '&lt;script&gt;alert(1)&lt;/script&gt;'));
        Assert::assertSame(1, substr_count($source, '&lt;script&gt;alert(2)&lt;/script&gt;'));

        $I->amOnRoute('/oauth/account/client', ['id' => $client->id]);
        Assert::assertStringNotContainsString('<script>alert(', $I->grabPageSource());
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
        Assert::assertSame('no-store', $I->grabHttpHeader('Cache-Control'));

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

    public function testUserClientIsRegisteredListedAndEdited(FunctionalTester $I)
    {
        $this->allowUserClients($I);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->see('My OAuth apps');
        $I->see('Register app');

        $I->amOnRoute('/oauth/account/client');
        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'CLI agent',
            'ClientForm[redirectUris]' => 'http://example.org/cb',
            'ClientForm[scopes]' => ['content:read'],
        ]);
        $I->see('Not allowed: http://example.org/cb');
        Assert::assertFalse(Client::find()->where(['type' => Client::TYPE_USER])->exists());

        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'CLI agent',
            'ClientForm[redirectUris]' => "http://127.0.0.1/cb\ncom.example.cli:/cb",
            'ClientForm[scopes]' => ['content:read', 'spaces:read'],
        ]);
        $client = Client::findOne(['type' => Client::TYPE_USER, 'name' => 'CLI agent']);
        Assert::assertNotNull($client);
        Assert::assertSame(2, (int)$client->owner_user_id);
        Assert::assertFalse($client->isConfidential());
        Assert::assertSame(['http://127.0.0.1/cb', 'com.example.cli:/cb'], $client->getRedirectUris());
        Assert::assertSame(['content:read', 'spaces:read'], $client->getAllowedScopes());

        $I->amOnRoute('/oauth/account/index');
        $I->see('CLI agent');
        $I->see($client->client_id);
        $I->see('Public');

        $I->amOnRoute('/oauth/account/client', ['id' => $client->id]);
        $I->seeInField('ClientForm[name]', 'CLI agent');
        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'Renamed agent',
            'ClientForm[redirectUris]' => 'http://127.0.0.1/cb',
            'ClientForm[scopes]' => ['content:read'],
        ]);
        $client->refresh();
        Assert::assertSame('Renamed agent', $client->name);
        Assert::assertSame(['http://127.0.0.1/cb'], $client->getRedirectUris());
        Assert::assertSame(['content:read'], $client->getAllowedScopes());
    }

    public function testConfidentialClientShowsItsSecretOnce(FunctionalTester $I)
    {
        $this->allowUserClients($I);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/client');
        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'Server app',
            'ClientForm[redirectUris]' => 'https://example.org/cb',
            'ClientForm[scopes]' => ['content:read'],
            'ClientForm[confidential]' => '1',
        ]);
        $I->see('Copy the secret now.');
        Assert::assertSame('no-store', $I->grabHttpHeader('Cache-Control'));
        $secret = $this->grabSecret($I);

        $client = Client::findOne(['type' => Client::TYPE_USER, 'name' => 'Server app']);
        $I->seeInField('#oauth-client-id', $client->client_id);
        Assert::assertTrue($client->validateSecret($secret));
        Assert::assertNotSame($secret, $client->secret_hash);

        $I->amOnRoute('/oauth/account/index');
        $I->see('Server app');
        $I->see('Confidential');
        $I->dontSee($secret);
        $I->amOnRoute('/oauth/account/client', ['id' => $client->id]);
        $I->dontSee($secret);
    }

    public function testSecretIsRegenerated(FunctionalTester $I)
    {
        $this->allowUserClients($I);
        $client = $this->userClient(2);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/client', ['id' => $client->id]);
        $I->see('Regenerate secret');
        $I->dontSee('Your password');

        // A GET does nothing
        $I->amOnRoute('/oauth/account/regenerate-secret', ['id' => $client->id]);
        Assert::assertTrue(Client::findOne(['id' => $client->id])->validateSecret('old-secret'));

        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fregenerate-secret&id=' . $client->id);
        $I->see('Copy the secret now.');
        Assert::assertSame('no-store', $I->grabHttpHeader('Cache-Control'));
        $secret = $this->grabSecret($I);

        $client->refresh();
        Assert::assertFalse($client->validateSecret('old-secret'));
        Assert::assertTrue($client->validateSecret($secret));
    }

    public function testOwnClientIsDeleted(FunctionalTester $I)
    {
        $this->allowUserClients($I);
        $client = $this->userClient(2);
        $grant = $this->usedGrant(2, $client->client_id, ['content:read']);

        $I->amUser1();
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fdelete-client&id=' . $client->id);
        Assert::assertFalse(Client::find()->where(['id' => $client->id])->exists());
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testForeignClientCannotBeManaged(FunctionalTester $I)
    {
        $this->allowUserClients($I);
        $foreign = $this->userClient(3);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->dontSee('Own app');

        $I->amOnRoute('/oauth/account/client', ['id' => $foreign->id]);
        $I->seeResponseCodeIs(404);

        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fdelete-client&id=' . $foreign->id);
        $I->seeResponseCodeIs(404);
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fregenerate-secret&id=' . $foreign->id);
        $I->seeResponseCodeIs(404);
        Assert::assertTrue(Client::findOne(['id' => $foreign->id])->validateSecret('old-secret'));
    }

    public function testClientsCannotBeManagedWithoutPermission(FunctionalTester $I)
    {
        Module::instance()->settings->set('allowUserClients', true);
        $this->usedGrant(2, Client::SYSTEM_CLIENT_ID, ['full']);
        $client = $this->userClient(2);

        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->dontSee('My OAuth apps');

        $I->amOnRoute('/oauth/account/client');
        $I->seeResponseCodeIs(403);
        $I->amOnRoute('/oauth/account/client', ['id' => $client->id]);
        $I->seeResponseCodeIs(403);
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fdelete-client&id=' . $client->id);
        $I->seeResponseCodeIs(403);
        Assert::assertTrue(Client::find()->where(['id' => $client->id])->exists());
    }

    public function testNoCredentialsAreCreatedWhileImpersonating(FunctionalTester $I)
    {
        $this->allowTokens($I);
        $this->allowUserClients($I);
        $client = $this->userClient(2);

        $I->amAdmin();
        Yii::$app->user->getImpersonation()->start(User::findOne(['id' => 2]));

        $I->amOnRoute('/oauth/account/index');
        $I->seeResponseCodeIs(200);
        $I->amOnRoute('/oauth/account/client', ['id' => $client->id]);
        $I->seeResponseCodeIs(200);

        $I->amOnRoute('/oauth/account/create-token');
        $I->seeResponseCodeIs(403);
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fcreate-token', [
            'PersonalAccessTokenForm' => ['name' => 'Backdoor', 'scopes' => ['content:read'], 'lifetime' => '30'],
        ]);
        $I->seeResponseCodeIs(403);
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fregenerate-secret&id=' . $client->id);
        $I->seeResponseCodeIs(403);
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fclient', [
            'ClientForm' => ['name' => 'Backdoor', 'redirectUris' => 'https://example.org/cb', 'scopes' => ['content:read'], 'confidential' => '1'],
        ]);
        $I->seeResponseCodeIs(403);

        Assert::assertFalse(Grant::find()->where(['type' => Grant::TYPE_PAT])->exists());
        Assert::assertFalse(Client::find()->where(['name' => 'Backdoor'])->exists());
        Assert::assertTrue(Client::findOne(['id' => $client->id])->validateSecret('old-secret'));
    }

    public function testAnInteractiveLoginConfirmsUsersWithoutAPassword(FunctionalTester $I)
    {
        $this->allowTokens($I);

        // The login form records the interactive login; the password is removed afterwards
        $I->amUser1();
        $password = User::findOne(['id' => 2])->currentPassword;
        $password->delete();

        try {
            $I->amOnRoute('/oauth/account/create-token');
            $I->dontSee('Your password');
            $I->submitForm('form[action*="create-token"]', [
                'PersonalAccessTokenForm[name]' => 'Backup script',
                'PersonalAccessTokenForm[scopes]' => ['content:read'],
                'PersonalAccessTokenForm[lifetime]' => '30',
            ]);
            $I->see('Copy the token now.');
            Assert::assertTrue(Grant::find()->where(['type' => Grant::TYPE_PAT, 'user_id' => 2])->exists());
        } finally {
            Yii::$app->db->createCommand()->insert(Password::tableName(), $password->getAttributes())->execute();
        }
    }
}
