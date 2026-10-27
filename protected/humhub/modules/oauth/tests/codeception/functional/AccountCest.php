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
        GroupPermission::deleteAll(['class' => CreatePersonalAccessToken::class]);
        Notification::deleteAll(['class' => GrantCreatedNotification::class]);
        Module::instance()->settings->delete('allowPersonalAccessTokens');
        Module::instance()->settings->delete('allowClients');
        Module::instance()->maxPersonalAccessTokensPerUser = null;
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

    private function adminClient(array $attributes = []): Client
    {
        Module::instance()->settings->set('allowClients', true);
        $client = new Client($attributes + [
            'client_id' => 'agent', 'type' => Client::TYPE_ADMIN, 'name' => 'Research Agent',
            'redirect_uris' => json_encode(['http://127.0.0.1/callback']),
            'scopes' => json_encode(['content:read', 'spaces:read']),
            'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);

        return $client;
    }

    public function testDevicesAreListedAndSignedOut(FunctionalTester $I)
    {
        $grant = $this->usedGrant(2, Client::SYSTEM_CLIENT_ID, ['full'], ['user_agent' => self::SAFARI_IOS]);

        $I->amUser1();
        $I->amOnRoute('/user/security/login');
        $I->see('Devices & Access');
        $I->dontSee('Apps & Tokens');

        $I->amOnRoute('/user/security/sessions');
        $I->see('Browser sessions');
        $I->see('HumHub app', '.security-section h3');
        $I->see('Devices signed in with the HumHub app.');
        $I->see('Safari · iOS');
        $I->seeElement('[aria-label="Sign out: Safari · iOS"]');

        $this->revoke($I, $grant);
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testConnectedAppsAreListedAndDisconnected(FunctionalTester $I)
    {
        $this->adminClient();
        $grant = $this->usedGrant(2, 'agent', ['content:read', 'unknown:scope']);

        $I->amUser1();
        $I->amOnRoute('/user/security/sessions');
        $I->see('Connected apps');
        $I->see('Research Agent');
        $I->see('Read content, comments and likes');
        $I->dontSee('Read spaces and memberships');
        $I->dontSee('unknown:scope');
        $I->dontSee('Devices signed in with the HumHub app.');

        $this->revoke($I, $grant);
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testUnusedGrantsAreNotListed(FunctionalTester $I)
    {
        (new GrantService())->createOAuthGrant(User::findOne(['id' => 2]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);

        // Nothing to list, so no section
        $I->amUser1();
        $I->amOnRoute('/user/security/sessions');
        $I->see('Browser sessions');
        $I->dontSee('Devices signed in with the HumHub app.');
        $I->dontSee('Connected apps');
        $I->dontSee('Personal access tokens');
    }

    public function testExpiredTokensKeepTheTab(FunctionalTester $I)
    {
        [, $grant] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), 'Old script', ['content:read'], 30);
        $grant->updateAttributes(['expires_at' => time() - 60]);

        $I->amUser1();
        $I->amOnRoute('/user/security/sessions');
        $I->see('Old script');
        $I->see('Expired');
    }

    public function testNamesAreEncoded(FunctionalTester $I)
    {
        $client = $this->adminClient(['name' => '<script>alert(1)</script>']);
        $this->usedGrant(2, $client->client_id, ['content:read']);
        (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), '<script>alert(2)</script>', ['content:read'], 30);

        $I->amUser1();
        $I->amOnRoute('/user/security/sessions');
        $I->see('Connected apps');
        $I->see('Personal access tokens');
        $source = $I->grabPageSource();
        Assert::assertStringNotContainsString('<script>alert(', $source);
        // As the name and in the label of its row's button
        Assert::assertSame(2, substr_count($source, '&lt;script&gt;alert(1)&lt;/script&gt;'));
        Assert::assertSame(2, substr_count($source, '&lt;script&gt;alert(2)&lt;/script&gt;'));
    }

    public function testTokenIsCreatedShownOnceAndRevoked(FunctionalTester $I)
    {
        $this->allowTokens($I);

        $I->amUser1();
        $I->amOnRoute('/user/security/sessions');
        $I->see('Personal access tokens');
        $I->see('Create token');

        $I->amOnRoute('/oauth/account/create-token');
        // The page belongs to the tab of its section
        $I->see('Devices & Access', '.nav-pills .nav-link.active');
        // Without `full` among the scopes, no lock and no hint
        $I->dontSeeElement('#personalaccesstokenform-scopes-full-hint');
        Assert::assertStringNotContainsString('"#personalaccesstokenform-scopes"', $I->grabPageSource());
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

        $I->amOnRoute('/user/security/sessions');
        $I->see('Backup script');
        $I->see('hhpat_…' . $grant->token_hint);
        $I->dontSee($match[0]);

        $this->revoke($I, $grant);
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testTheTokenLimitReplacesTheCreateButtonAndRefusesPosts(FunctionalTester $I)
    {
        $this->allowTokens($I);
        Module::instance()->maxPersonalAccessTokensPerUser = 2;
        $user = User::findOne(['id' => 2]);
        $service = new PersonalAccessTokenService();
        [, $first] = $service->create($user, 'One', ['content:read'], 30);
        $service->create($user, 'Two', ['content:read'], 30);

        $I->amUser1();
        $I->amOnRoute('/user/security/sessions');
        $I->see('You have reached the maximum of 2 personal access tokens. Revoke one to create a new token.');
        $I->dontSee('Create token');

        $post = [
            'PersonalAccessTokenForm[name]' => 'Three',
            'PersonalAccessTokenForm[scopes]' => ['content:read'],
            'PersonalAccessTokenForm[lifetime]' => '30',
            'PersonalAccessTokenForm[currentPassword]' => self::PASSWORD,
        ];
        // The form is not offered; a direct POST creates nothing either
        $I->amOnRoute('/oauth/account/create-token');
        $I->dontSeeElement('form[action*="create-token"]');
        $I->see('You have reached the maximum of 2 personal access tokens.');
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fcreate-token', $post);
        Assert::assertSame(2, (int)Grant::find()->where(['type' => Grant::TYPE_PAT])->count());

        // Revoking frees a slot
        $this->revoke($I, $first);
        $I->amOnRoute('/user/security/sessions');
        $I->see('Create token');
        $I->dontSee('You have reached the maximum');

        // An expired token does not count
        $service->create($user, 'Three', ['content:read'], 30)[1]->updateAttributes(['expires_at' => time() - 10]);
        $I->amOnRoute('/oauth/account/create-token');
        $I->submitForm('form[action*="create-token"]', $post);
        $I->see('Copy the token now.');
        Assert::assertSame(3, (int)Grant::find()->where(['type' => Grant::TYPE_PAT])->count());
    }

    public function testFullTokenScopeIsStoredAlone(FunctionalTester $I)
    {
        $this->allowTokens($I);
        Module::instance()->settings->set('patScopes', json_encode(['full', 'content:read', 'spaces:read']));

        try {
            $I->amUser1();
            $I->amOnRoute('/oauth/account/create-token');
            $I->seeElement('#personalaccesstokenform-scopes input[value="full"]');
            $I->see('Full access includes all other scopes.', '#personalaccesstokenform-scopes-full-hint');
            // The checkbox list gets the script that locks the other scopes while `full` is checked
            Assert::assertStringContainsString('"#personalaccesstokenform-scopes"', $I->grabPageSource());

            $I->submitForm('form[action*="create-token"]', [
                'PersonalAccessTokenForm[name]' => 'Agent',
                'PersonalAccessTokenForm[scopes]' => ['content:read', 'full'],
                'PersonalAccessTokenForm[lifetime]' => '30',
                'PersonalAccessTokenForm[currentPassword]' => self::PASSWORD,
            ]);
            $I->see('Copy the token now.');

            $grant = Grant::findOne(['type' => Grant::TYPE_PAT, 'user_id' => 2]);
            Assert::assertSame(['full'], $grant->getScopeIds());
        } finally {
            Module::instance()->settings->delete('patScopes');
        }
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
        $I->amOnRoute('/user/security/sessions');
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

    public function testTokenSectionIsHiddenWithoutTokensOrPermission(FunctionalTester $I)
    {
        Module::instance()->settings->set('allowPersonalAccessTokens', true);

        $I->amUser1();
        $I->amOnRoute('/user/security/sessions');
        $I->see('Browser sessions');
        $I->dontSee('Personal access tokens');
        $I->dontSee('Create token');
    }

    public function testFormerAppsAndTokensPageIsGone(FunctionalTester $I)
    {
        $this->allowTokens($I);

        // The former "Apps & Tokens" page was merged into "Devices & Access"
        $I->amUser1();
        $I->amOnRoute('/oauth/account/index');
        $I->seeResponseCodeIs(404);
    }

    public function testNoTokensAreCreatedWhileImpersonating(FunctionalTester $I)
    {
        $this->allowTokens($I);

        $I->amAdmin();
        Yii::$app->user->getImpersonation()->start(User::findOne(['id' => 2]));

        $I->amOnRoute('/user/security/sessions');
        $I->seeResponseCodeIs(200);

        $I->amOnRoute('/oauth/account/create-token');
        $I->seeResponseCodeIs(403);
        $I->sendAjaxPostRequest('/index-test.php?r=oauth%2Faccount%2Fcreate-token', [
            'PersonalAccessTokenForm' => ['name' => 'Backdoor', 'scopes' => ['content:read'], 'lifetime' => '30'],
        ]);
        $I->seeResponseCodeIs(403);

        Assert::assertFalse(Grant::find()->where(['type' => Grant::TYPE_PAT])->exists());
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
