<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\connection\LdapConnectionRegistry;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use humhub\modules\user\source\UserSourceCollection;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Integration tests for the LDAP user-sync feature ({@see LdapUserSource::syncUsers()}).
 *
 * These tests verify that running syncUsers() against the test OpenLDAP container
 * (seeded via .github/ldap/init.ldif) correctly creates, updates, and disables
 * HumHub users.
 *
 * Skipped when LDAP_TEST_HOST is not set.
 */
class LdapSyncTest extends HumHubDbTestCase
{
    private LdapConnectionConfig $ldapConfig;
    private LdapUserSource $ldapUserSource;

    protected function _before(): void
    {
        parent::_before();

        if (empty(getenv('LDAP_TEST_HOST'))) {
            $this->markTestSkipped(
                'LDAP integration tests are skipped. Set LDAP_TEST_HOST to enable them.',
            );
        }

        $this->ldapConfig = $this->createTestConfig(false);
        $this->installConnection();

        try {
            // Verify connectivity
            new LdapService($this->ldapConfig);
        } catch (\Exception $e) {
            $this->markTestSkipped('Cannot connect to LDAP server: ' . $e->getMessage());
        }

        // Register AuthClient (sync needs it for attribute normalisation)
        Yii::$app->authClientCollection->setClient('ldap', [
            'class' => LdapAuth::class,
            'connectionId' => 'ldap',
            'clientId' => 'ldap',
        ]);

        // Register a fresh UserSourceCollection that contains the LDAP source
        $sourceCollection = new UserSourceCollection();
        $sourceCollection->setUserSources([
            'ldap' => [
                'class' => LdapUserSource::class,
                'connectionId' => 'ldap',
                'allowedAuthClientIds' => ['ldap'],
            ],
        ]);
        Yii::$app->set('userSourceCollection', $sourceCollection);

        $this->ldapUserSource = $sourceCollection->getUserSource('ldap');
    }

    private function installConnection(): void
    {
        $registry = new LdapConnectionRegistry();
        $registry->setConfigs(['ldap' => $this->ldapConfig]);
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $module->setConnectionRegistry($registry);
    }

    private function enableAutoRefresh(): void
    {
        $this->ldapConfig->autoRefreshUsers = true;
    }

    // ---------------------------------------------------------------------------
    // Sync creates new users
    // ---------------------------------------------------------------------------

    public function testSyncCreatesNewLdapUsers(): void
    {
        $this->enableAutoRefresh();

        $beforeCount = $this->countLdapUsers();

        $this->ldapUserSource->syncUsers();

        $afterCount = $this->countLdapUsers();

        $this->assertGreaterThan(
            $beforeCount,
            $afterCount,
            'syncUsers() should have created at least one new HumHub user from LDAP.',
        );
    }

    public function testSyncCreatesUserWithCorrectEmail(): void
    {
        $this->enableAutoRefresh();
        $this->ldapUserSource->syncUsers();

        $john = User::findOne(['email' => 'john@example.org']);
        $this->assertNotNull($john, 'john@example.org should exist in HumHub after sync.');
        $this->assertSame('ldap', $john->user_source);
    }

    public function testSyncCreatesUserWithCorrectUsername(): void
    {
        $this->enableAutoRefresh();
        $this->ldapUserSource->syncUsers();

        $jane = User::findOne(['email' => 'jane@example.org']);
        $this->assertNotNull($jane, 'jane@example.org should exist in HumHub after sync.');
        $this->assertSame('jane.doe', $jane->username);
    }

    // ---------------------------------------------------------------------------
    // Sync stores source_id in user_auth
    // ---------------------------------------------------------------------------

    public function testSyncSetsSourceIdInUserAuthFromIdAttribute(): void
    {
        $this->enableAutoRefresh();
        $this->ldapUserSource->syncUsers();

        $john = User::findOne(['email' => 'john@example.org']);
        $this->assertNotNull($john);

        $auth = Auth::findOne(['source' => 'ldap', 'user_id' => $john->id]);
        $this->assertNotNull($auth, 'A user_auth entry should exist for the synced LDAP user.');
        $this->assertSame('john.doe', $auth->source_id);
    }

    // ---------------------------------------------------------------------------
    // Sync disables users no longer present in LDAP
    // ---------------------------------------------------------------------------

    public function testSyncDisablesUserNotFoundInLdap(): void
    {
        $this->enableAutoRefresh();
        $this->ldapUserSource->syncUsers();

        $ghostUser = User::findOne(['email' => 'john@example.org']);
        $this->assertNotNull($ghostUser);

        Auth::updateAll(
            ['source_id' => 'ghost.user.not.in.ldap'],
            ['user_id' => $ghostUser->id, 'source' => 'ldap'],
        );

        $this->ldapUserSource->syncUsers();

        $ghostUser->refresh();
        $this->assertSame(
            User::STATUS_DISABLED,
            $ghostUser->status,
            'User with source_id not found in LDAP should be disabled after sync.',
        );
    }

    // ---------------------------------------------------------------------------
    // Sync re-enables previously disabled users that returned to LDAP
    // ---------------------------------------------------------------------------

    public function testSyncReEnablesDisabledUserFoundAgainInLdap(): void
    {
        $this->enableAutoRefresh();
        $this->ldapUserSource->syncUsers();

        $john = User::findOne(['email' => 'john@example.org']);
        $this->assertNotNull($john);

        $john->status = User::STATUS_DISABLED;
        $john->save(false);
        $this->assertSame(User::STATUS_DISABLED, $john->status);

        $this->ldapUserSource->syncUsers();

        $john->refresh();
        $this->assertSame(
            User::STATUS_ENABLED,
            $john->status,
            'User found again in LDAP should be re-enabled by sync.',
        );
    }

    // ---------------------------------------------------------------------------
    // Sync without autoRefreshUsers is a no-op
    // ---------------------------------------------------------------------------

    public function testSyncDoesNothingWhenAutoRefreshIsDisabled(): void
    {
        $this->ldapConfig->autoRefreshUsers = false;

        $beforeCount = $this->countLdapUsers();
        $this->ldapUserSource->syncUsers();
        $afterCount = $this->countLdapUsers();

        $this->assertSame($beforeCount, $afterCount, 'No users should be synced when autoRefreshUsers is false.');
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function countLdapUsers(): int
    {
        return (int) User::find()->where(['user_source' => 'ldap'])->count();
    }

    private function createTestConfig(bool $autoRefresh): LdapConnectionConfig
    {
        return new LdapConnectionConfig([
            'title' => 'LDAP Test',
            'hostname' => getenv('LDAP_TEST_HOST'),
            'port' => (int)(getenv('LDAP_TEST_PORT') ?: 389),
            'baseDn' => getenv('LDAP_TEST_BASE_DN') ?: 'dc=example,dc=org',
            'bindUsername' => getenv('LDAP_TEST_BIND_DN') ?: 'cn=admin,dc=example,dc=org',
            'bindPassword' => getenv('LDAP_TEST_BIND_PASSWORD') ?: 'adminpassword',
            'userFilter' => getenv('LDAP_TEST_USER_FILTER') ?: '(objectClass=inetOrgPerson)',
            'usernameAttribute' => getenv('LDAP_TEST_USERNAME_ATTRIBUTE') ?: 'uid',
            'emailAttribute' => 'mail',
            'idAttribute' => 'uid',
            'autoRefreshUsers' => $autoRefresh,
        ]);
    }
}
