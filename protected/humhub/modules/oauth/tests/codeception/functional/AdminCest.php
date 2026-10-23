<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\forms\SettingsForm;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\permissions\CreatePersonalAccessToken;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\GroupPermission;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

class AdminCest
{
    private const SETTINGS = [
        'allowApp', 'allowClients', 'allowPersonalAccessTokens',
        'patScopes', 'patMaxLifetimeDays', 'refreshTokenLifetimeDays', 'grantMaxLifetimeDays', 'revokeOnPasswordChange',
    ];

    public function _before(FunctionalTester $I)
    {
        $this->cleanUp();
    }

    public function _after(FunctionalTester $I)
    {
        $this->cleanUp();
    }

    private const USER1_GROUP = 2;

    private function cleanUp(): void
    {
        Grant::deleteAll();
        Client::deleteAll(['not', ['client_id' => Client::SYSTEM_CLIENT_ID]]);
        // Failed password confirmations are throttled via the cache
        Yii::$app->cache->flush();
        GroupPermission::deleteAll(['group_id' => self::USER1_GROUP, 'class' => [ManageSettings::class, CreatePersonalAccessToken::class]]);
        foreach (self::SETTINGS as $name) {
            Module::instance()->settings->delete($name);
        }
    }

    public function testMenuEntryAndPageForAdmin(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/authentication');
        $I->see('Apps & API Access');

        $I->amOnRoute('/oauth/admin/index');
        $I->seeResponseCodeIs(200);
        $I->see('Personal access tokens');
    }

    public function testEnabledFeatureWithoutGroupPermissionIsFlagged(FunctionalTester $I)
    {
        Module::instance()->settings->set('allowPersonalAccessTokens', true);
        $I->amAdmin();

        $I->amOnRoute('/oauth/admin/index');
        $I->see('No group has the permission "Create personal access tokens" yet');

        $I->setGroupPermission(self::USER1_GROUP, CreatePersonalAccessToken::class);
        $I->amOnRoute('/oauth/admin/index');
        $I->dontSee('No group has the permission "Create personal access tokens" yet');
    }

    public function testTokenSettingsAreHiddenWhileTokensAreOff(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->seeElement('#oauth-pat-settings.d-none');
        $I->seeElement('#oauth-pat-settings select[name="SettingsForm[patScopes][]"]');
        $I->seeElement('#oauth-pat-settings input[name="SettingsForm[patMaxLifetimeDays]"]');

        Module::instance()->settings->set('allowPersonalAccessTokens', true);
        $I->amOnRoute('/oauth/admin/index');
        $I->seeElement('#oauth-pat-settings');
        $I->dontSeeElement('#oauth-pat-settings.d-none');
        // The permission warning is part of it
        $I->see('No group has the permission "Create personal access tokens" yet', '#oauth-pat-settings');
    }

    public function testSettingsAreSaved(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[allowApp]' => '0',
            'SettingsForm[allowClients]' => '1',
            'SettingsForm[allowPersonalAccessTokens]' => '1',
            'SettingsForm[patScopes]' => ['content:read', 'full'],
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '14',
            'SettingsForm[grantMaxLifetimeDays]' => '200',
            'SettingsForm[revokeOnPasswordChange]' => '1',
        ]);

        $module = Module::instance();
        Assert::assertFalse($module->allowsApp());
        Assert::assertTrue($module->allowsClients());
        Assert::assertTrue($module->allowsPersonalAccessTokens());
        Assert::assertSame(['content:read', 'full'], $module->getPatScopes());
        Assert::assertSame(30, $module->getPatMaxLifetimeDays());
        Assert::assertSame(14, $module->getRefreshTokenLifetimeDays());
        Assert::assertSame(200, $module->getGrantMaxLifetimeDays());
        Assert::assertTrue($module->revokesOnPasswordChange());
    }

    public function testRefreshTokenLifetimeOfZeroIsRejected(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[allowClients]' => '1',
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '0',
            'SettingsForm[grantMaxLifetimeDays]' => '0',
        ]);

        Assert::assertFalse(Module::instance()->allowsClients());
        Assert::assertSame(90, Module::instance()->getRefreshTokenLifetimeDays());
    }

    public function testDefaultScopeSelectionStaysUnset(FunctionalTester $I)
    {
        $default = ScopeRegistry::getDefaultRestrictedScopeIds();
        Module::instance()->settings->set('patScopes', json_encode(['content:read']));

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[patScopes]' => array_reverse($default),
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '14',
            'SettingsForm[grantMaxLifetimeDays]' => '0',
        ]);

        Assert::assertNull(Module::instance()->settings->get('patScopes'));
    }

    public function testUnregisteredStoredScopeDoesNotBlockSaving(FunctionalTester $I)
    {
        Module::instance()->settings->set('patScopes', json_encode(['content:read', 'gone:read']));
        Assert::assertSame(['content:read'], (new SettingsForm())->patScopes);

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[allowClients]' => '1',
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '14',
            'SettingsForm[grantMaxLifetimeDays]' => '0',
        ]);

        Assert::assertTrue(Module::instance()->allowsClients());
    }

    public function testNormalUserIsForbidden(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/oauth/admin/index');
        $I->seeResponseCodeIs(403);
    }

    private function adminClient(array $attributes = []): Client
    {
        $client = new Client($attributes + [
            'client_id' => 'agent', 'type' => Client::TYPE_ADMIN, 'name' => 'Research Agent',
            'secret_hash' => hash('sha256', 'old-secret'),
            'redirect_uris' => json_encode(['https://example.org/cb']),
            'scopes' => json_encode(['content:read', 'spaces:read']),
            'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);

        return $client;
    }

    private function grant(int $userId, Client $client, array $scopes = ['content:read']): Grant
    {
        Module::instance()->settings->set('allowClients', true);

        return (new GrantService())->createOAuthGrant(User::findOne(['id' => $userId]), $client, $scopes);
    }

    private function post(FunctionalTester $I, string $route, array $params = [], array $data = []): void
    {
        $I->sendAjaxPostRequest('/index-test.php?' . http_build_query(['r' => $route] + $params), $data);
    }

    private function grabSecret(FunctionalTester $I): string
    {
        Assert::assertSame(1, preg_match('/id="oauth-client-secret"[^>]*value="([^"]+)"/', $I->grabPageSource(), $match));

        return $match[1];
    }

    public function testClientsAreListedAndSystemClientIsProtected(FunctionalTester $I)
    {
        $this->adminClient();
        $system = Client::findByClientId(Client::SYSTEM_CLIENT_ID);

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/clients');
        $I->seeResponseCodeIs(200);
        $I->see('Research Agent');
        $I->see(Client::SYSTEM_CLIENT_ID);

        $I->amOnRoute('/oauth/admin/client', ['id' => $system->id]);
        $I->seeResponseCodeIs(403);
        $this->post($I, 'oauth/admin/delete-client', ['id' => $system->id]);
        $I->seeResponseCodeIs(403);
        $this->post($I, 'oauth/admin/regenerate-secret', ['id' => $system->id]);
        $I->seeResponseCodeIs(403);
        Assert::assertNotNull(Client::findByClientId(Client::SYSTEM_CLIENT_ID));
    }

    public function testClientIsCreatedAndSecretShownOnce(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/client');
        // The checkbox list gets the script that locks the other scopes while `full` is checked
        Assert::assertStringContainsString('"#clientform-scopes"', $I->grabPageSource());
        $I->see('Full access includes all other scopes.', '#clientform-scopes-full-hint');
        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'Server app',
            'ClientForm[redirectUris]' => 'https://example.org/callback',
            'ClientForm[confidential]' => '1',
            'ClientForm[scopes]' => ['content:read', 'full'],
            'ClientForm[skipConsent]' => '1',
            'ClientForm[isActive]' => '1',
        ]);
        $I->see('Copy the secret now.');
        Assert::assertSame('no-store', $I->grabHttpHeader('Cache-Control'));
        $secret = $this->grabSecret($I);

        $client = Client::findOne(['name' => 'Server app']);
        Assert::assertSame(Client::TYPE_ADMIN, $client->type);
        Assert::assertTrue($client->validateSecret($secret));
        Assert::assertSame(1, (int)$client->skip_consent);
        // `full` covers the others and is stored alone
        Assert::assertSame(['full'], $client->getAllowedScopes());

        $I->amOnRoute('/oauth/admin/clients');
        $I->see('Server app');
        $I->dontSee($secret);
        $I->amOnRoute('/oauth/admin/client', ['id' => $client->id]);
        $I->dontSee($secret);
    }

    public function testClientIsEditedAndDeactivated(FunctionalTester $I)
    {
        $client = $this->adminClient();
        $grant = $this->grant(2, $client);
        Assert::assertTrue((new GrantService())->isUsable($grant));

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/client', ['id' => $client->id]);
        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'Research Agent',
            'ClientForm[redirectUris]' => 'https://example.org/cb',
            'ClientForm[confidential]' => '1',
            'ClientForm[scopes]' => ['spaces:read'],
            'ClientForm[isActive]' => '1',
        ]);
        $client->refresh();
        Assert::assertSame(['spaces:read'], $client->getAllowedScopes());
        Assert::assertTrue($client->validateSecret('old-secret'));

        $I->amOnRoute('/oauth/admin/client', ['id' => $client->id]);
        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'Research Agent',
            'ClientForm[redirectUris]' => 'https://example.org/cb',
            'ClientForm[confidential]' => '1',
            'ClientForm[scopes]' => ['spaces:read'],
            'ClientForm[isActive]' => '0',
        ]);
        $client->refresh();
        Assert::assertSame(0, (int)$client->is_active);
        Assert::assertFalse((new GrantService())->isUsable(Grant::findOne(['id' => $grant->id])));
    }

    public function testSecretIsRegenerated(FunctionalTester $I)
    {
        $client = $this->adminClient();

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/client', ['id' => $client->id]);
        $I->see('Regenerate secret');
        $I->dontSee('Your password');

        $this->post($I, 'oauth/admin/regenerate-secret', ['id' => $client->id]);
        $I->see('Copy the secret now.');
        Assert::assertSame('no-store', $I->grabHttpHeader('Cache-Control'));
        $secret = $this->grabSecret($I);

        $client->refresh();
        Assert::assertFalse($client->validateSecret('old-secret'));
        Assert::assertTrue($client->validateSecret($secret));
    }

    public function testClientIsDeletedWithItsGrants(FunctionalTester $I)
    {
        $client = $this->adminClient();
        $grant = $this->grant(2, $client);

        $I->amAdmin();
        // A GET must not delete
        $I->amOnRoute('/oauth/admin/delete-client', ['id' => $client->id]);
        Assert::assertNotNull(Client::findOne(['id' => $client->id]));

        $this->post($I, 'oauth/admin/delete-client', ['id' => $client->id]);
        Assert::assertNull(Client::findOne(['id' => $client->id]));
        Assert::assertFalse(Grant::find()->where(['id' => $grant->id])->exists());
    }

    public function testGrantsAreFilteredAndRevoked(FunctionalTester $I)
    {
        $agent = $this->adminClient();
        $other = $this->adminClient(['client_id' => 'other', 'name' => 'Other Tool']);
        $user1 = $this->grant(2, $agent);
        $user2 = $this->grant(3, $agent);
        $otherGrant = $this->grant(2, $other);

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/grants');
        $I->seeResponseCodeIs(200);
        $I->see('Research Agent', 'table');
        $I->see('Other Tool', 'table');

        $I->amOnRoute('/oauth/admin/grants', ['GrantSearch' => ['user' => User::findOne(['id' => 3])->username]]);
        $I->see('Research Agent', 'table');
        $I->dontSee('Other Tool', 'table');
        Assert::assertSame(1, substr_count($I->grabPageSource(), 'data-action-confirm-header="Revoke this grant?"'));

        $I->amOnRoute('/oauth/admin/grants', ['GrantSearch' => ['client_id' => $other->id]]);
        $I->see('Other Tool', 'table');
        $I->dontSee('Research Agent', 'table');

        $I->amOnRoute('/oauth/admin/grants', ['GrantSearch' => ['type' => Grant::TYPE_PAT]]);
        $I->dontSee('Research Agent', 'table');
        $I->dontSee('Other Tool', 'table');

        $this->post($I, 'oauth/admin/revoke-grant', ['id' => $user1->id]);
        Assert::assertFalse(Grant::find()->where(['id' => $user1->id])->exists());
        Assert::assertTrue(Grant::find()->where(['id' => $user2->id])->exists());
    }

    public function testRevokeAllUsesTheCurrentFilter(FunctionalTester $I)
    {
        $agent = $this->adminClient();
        $other = $this->adminClient(['client_id' => 'other', 'name' => 'Other Tool']);
        $this->grant(2, $agent);
        $this->grant(3, $agent);
        $keep = $this->grant(2, $other);

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/grants', ['GrantSearch' => ['client_id' => $agent->id]]);
        // The button posts exactly the filter of the list
        $source = $I->grabPageSource();
        Assert::assertStringContainsString('GrantSearch%5Bclient_id%5D=' . $agent->id, html_entity_decode($source));

        // A GET does nothing
        $I->amOnRoute('/oauth/admin/revoke-grants', ['GrantSearch' => ['client_id' => $agent->id]]);
        Assert::assertSame(3, (int)Grant::find()->count());

        $this->post($I, 'oauth/admin/revoke-grants', ['GrantSearch' => ['client_id' => $agent->id]]);
        Assert::assertSame([$keep->id], array_map('intval', Grant::find()->select('id')->column()));
    }

    public function testInvalidFilterRevokesNothing(FunctionalTester $I)
    {
        $this->grant(2, $this->adminClient());

        $I->amAdmin();
        $this->post($I, 'oauth/admin/revoke-grants', ['GrantSearch' => ['type' => 'bogus']]);
        Assert::assertSame(1, (int)Grant::find()->count());
    }

    public function testClientAndGrantPagesAreForbiddenForUsers(FunctionalTester $I)
    {
        $I->amUser1();
        foreach (['clients', 'client', 'grants'] as $action) {
            $I->amOnRoute('/oauth/admin/' . $action);
            $I->seeResponseCodeIs(403);
        }
    }

    public function testRevokeAllNeedsAFilter(FunctionalTester $I)
    {
        $this->grant(2, $this->adminClient());

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/grants');
        $I->dontSee('Revoke all');

        $this->post($I, 'oauth/admin/revoke-grants');
        $I->seeResponseCodeIs(400);
        Assert::assertSame(1, (int)Grant::find()->count());
    }

    public function testRevokeAllByTypeKeepsOtherTypes(FunctionalTester $I)
    {
        $oauth = $this->grant(2, $this->adminClient());
        [, $pat] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 2]), 'Script', ['content:read'], 30);

        $I->amAdmin();
        $this->post($I, 'oauth/admin/revoke-grants', ['GrantSearch' => ['type' => Grant::TYPE_PAT]]);
        Assert::assertTrue(Grant::find()->where(['id' => $oauth->id])->exists());
        Assert::assertFalse(Grant::find()->where(['id' => $pat->id])->exists());
    }

    public function testSingleRevokeKeepsTheFilter(FunctionalTester $I)
    {
        $grant = $this->grant(2, $this->adminClient());

        $I->amAdmin();
        $this->post($I, 'oauth/admin/revoke-grant', ['id' => $grant->id, 'GrantSearch' => ['type' => 'oauth']]);
        $I->seeResponseCodeIs(302);
        Assert::assertStringContainsString('GrantSearch%5Btype%5D=oauth', (string)$I->grabHttpHeader('X-Redirect'));
    }

    public function testPostOnlyActionsDoNotMutateOnGet(FunctionalTester $I)
    {
        $client = $this->adminClient();
        $grant = $this->grant(2, $client);

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/revoke-grant', ['id' => $grant->id]);
        $I->amOnRoute('/oauth/admin/regenerate-secret', ['id' => $client->id]);
        Assert::assertTrue(Grant::find()->where(['id' => $grant->id])->exists());
        Assert::assertTrue(Client::findOne(['id' => $client->id])->validateSecret('old-secret'));
    }

    public function testOnlySystemAdminsSetFullAndSkipConsent(FunctionalTester $I)
    {
        $I->setGroupPermission(self::USER1_GROUP, ManageSettings::class);
        $privileged = $this->adminClient(['scopes' => json_encode(['full']), 'skip_consent' => 1]);

        $I->amUser1();
        $I->amOnRoute('/oauth/admin/client');
        $I->seeResponseCodeIs(200);
        $I->dontSee('Trusted: skip the consent page');
        $I->dontSee('Full access to your account');
        $I->dontSeeElement('#clientform-scopes-full-hint');

        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'Sneaky app',
            'ClientForm[redirectUris]' => 'https://example.org/cb',
            'ClientForm[scopes]' => ['content:read', 'full'],
            'ClientForm[isActive]' => '1',
        ]);
        Assert::assertFalse(Client::find()->where(['name' => 'Sneaky app'])->exists());

        $this->post($I, 'oauth/admin/client', [], ['ClientForm' => [
            'name' => 'Sneaky app', 'redirectUris' => 'https://example.org/cb', 'scopes' => ['content:read'],
            'skipConsent' => '1', 'isActive' => '1',
        ]]);
        Assert::assertFalse(Client::find()->where(['name' => 'Sneaky app'])->exists());

        // What a system administrator set stays
        $I->amOnRoute('/oauth/admin/client', ['id' => $privileged->id]);
        $I->see('Full access to your account');
        $I->see('Set by a system administrator.');
        $I->dontSeeElement('input[name="ClientForm[scopes][]"]');
        $I->submitForm('#oauth-client-form', [
            'ClientForm[name]' => 'Renamed agent',
            'ClientForm[redirectUris]' => 'https://example.org/cb',
            'ClientForm[confidential]' => '1',
            'ClientForm[isActive]' => '1',
        ]);
        $privileged->refresh();
        Assert::assertSame('Renamed agent', $privileged->name);
        Assert::assertSame(['full'], $privileged->getAllowedScopes());
        Assert::assertSame(1, (int)$privileged->skip_consent);
    }

    public function testNoSecretIsCreatedWhileImpersonating(FunctionalTester $I)
    {
        $I->setGroupPermission(self::USER1_GROUP, ManageSettings::class);
        $client = $this->adminClient();

        $I->amAdmin();
        Yii::$app->user->getImpersonation()->start(User::findOne(['id' => 2]));

        $I->amOnRoute('/oauth/admin/client', ['id' => $client->id]);
        $I->seeResponseCodeIs(200);
        $this->post($I, 'oauth/admin/regenerate-secret', ['id' => $client->id]);
        $I->seeResponseCodeIs(403);
        $this->post($I, 'oauth/admin/client', [], ['ClientForm' => [
            'name' => 'Backdoor', 'redirectUris' => 'https://example.org/cb', 'scopes' => ['content:read'],
            'confidential' => '1', 'isActive' => '1',
        ]]);
        $I->seeResponseCodeIs(403);

        Assert::assertTrue(Client::findOne(['id' => $client->id])->validateSecret('old-secret'));
        Assert::assertFalse(Client::find()->where(['name' => 'Backdoor'])->exists());
    }

    public function testArrayFilterValuesAreIgnored(FunctionalTester $I)
    {
        $this->grant(2, $this->adminClient());

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/grants', ['GrantSearch' => ['user' => ['x'], 'type' => ['oauth']]]);
        $I->seeResponseCodeIs(200);
        $I->dontSee('Revoke all');

        $this->post($I, 'oauth/admin/revoke-grants', ['GrantSearch' => ['user' => ['x'], 'client_id' => ['1']]]);
        $I->seeResponseCodeIs(400);
        Assert::assertSame(1, (int)Grant::find()->count());
    }

    public function testGrantsShowScopeLabels(FunctionalTester $I)
    {
        $this->grant(2, $this->adminClient());

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/grants');
        $I->see('Read content, comments and likes', 'table');
        $I->dontSee('content:read', 'table');
    }
}
