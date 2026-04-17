<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Integration tests for the LDAP user-sync feature ({@see LdapAuth::syncUsers()}).
 *
 * These tests verify that running syncUsers() against the test OpenLDAP container
 * (seeded via .github/ldap/init.ldif) correctly creates, updates, and disables
 * HumHub users.
 *
 * The tests are skipped when LDAP_TEST_HOST is not set.
 * A database with the default HumHub fixtures is required (loaded by HumHubDbTestCase).
 */
class LdapSyncTest extends HumHubDbTestCase
{
    private LdapAuth $ldapAuth;

    protected function _before(): void
    {
        parent::_before();

        if (empty(getenv('LDAP_TEST_HOST'))) {
            $this->markTestSkipped(
                'LDAP integration tests are skipped. Set LDAP_TEST_HOST to enable them.',
            );
        }

        $this->ldapAuth = $this->createTestLdapAuth();

        try {
            // Verify connectivity; LdapService constructor calls connect()
            new LdapService($this->ldapAuth);
        } catch (\Exception $e) {
            $this->markTestSkipped('Cannot connect to LDAP server: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------------
    // Sync creates new users
    // ---------------------------------------------------------------------------

    public function testSyncCreatesNewLdapUsers(): void
    {
        $this->ldapAuth->autoRefreshUsers = true;

        $beforeCount = $this->countLdapUsers();

        $this->ldapAuth->syncUsers();

        $afterCount = $this->countLdapUsers();

        $this->assertGreaterThan(
            $beforeCount,
            $afterCount,
            'syncUsers() should have created at least one new HumHub user from LDAP.',
        );
    }

    public function testSyncCreatesUserWithCorrectEmail(): void
    {
        $this->ldapAuth->autoRefreshUsers = true;
        $this->ldapAuth->syncUsers();

        $john = User::findOne(['email' => 'john@example.org']);
        $this->assertNotNull($john, 'john@example.org should exist in HumHub after sync.');
        $this->assertSame('ldap', $john->auth_mode);
    }

    public function testSyncCreatesUserWithCorrectUsername(): void
    {
        $this->ldapAuth->autoRefreshUsers = true;
        $this->ldapAuth->syncUsers();

        $jane = User::findOne(['email' => 'jane@example.org']);
        $this->assertNotNull($jane, 'jane@example.org should exist in HumHub after sync.');
        $this->assertSame('jane.doe', $jane->username);
    }

    // ---------------------------------------------------------------------------
    // Sync stores authclient_id
    // ---------------------------------------------------------------------------

    public function testSyncSetsAuthclientIdFromIdAttribute(): void
    {
        $this->ldapAuth->autoRefreshUsers = true;
        $this->ldapAuth->syncUsers();

        $john = User::findOne(['email' => 'john@example.org']);
        $this->assertNotNull($john);
        // idAttribute is 'uid', so authclient_id should equal the LDAP uid
        $this->assertSame('john.doe', $john->authclient_id);
    }

    // ---------------------------------------------------------------------------
    // Sync disables users no longer present in LDAP
    // ---------------------------------------------------------------------------

    public function testSyncDisablesUserNotFoundInLdap(): void
    {
        // Run an initial sync so that any existing LDAP users are properly created
        $this->ldapAuth->autoRefreshUsers = true;
        $this->ldapAuth->syncUsers();

        // Mark an existing fixture user as an LDAP user with a non-existent LDAP uid
        $ghostUser = User::findOne(['username' => 'User1']);
        $this->assertNotNull($ghostUser);

        $ghostUser->auth_mode     = 'ldap';
        $ghostUser->authclient_id = 'ghost.user.not.in.ldap';
        $ghostUser->status        = User::STATUS_ENABLED;
        $ghostUser->save(false);

        // Re-run sync – the ghost user has an ID that doesn't exist in LDAP
        $this->ldapAuth->syncUsers();

        $ghostUser->refresh();
        $this->assertSame(
            User::STATUS_DISABLED,
            $ghostUser->status,
            'User with authclient_id not found in LDAP should be disabled after sync.',
        );
    }

    // ---------------------------------------------------------------------------
    // Sync re-enables previously disabled users that returned to LDAP
    // ---------------------------------------------------------------------------

    public function testSyncReEnablesDisabledUserFoundAgainInLdap(): void
    {
        // First sync to create john.doe in HumHub
        $this->ldapAuth->autoRefreshUsers = true;
        $this->ldapAuth->syncUsers();

        $john = User::findOne(['email' => 'john@example.org']);
        $this->assertNotNull($john);

        // Manually disable the user (simulating a previous removal)
        $john->status = User::STATUS_DISABLED;
        $john->save(false);
        $this->assertSame(User::STATUS_DISABLED, $john->status);

        // Sync again – john.doe still exists in LDAP, so the user must be re-enabled
        $this->ldapAuth->syncUsers();

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
        $this->ldapAuth->autoRefreshUsers = false;

        $beforeCount = $this->countLdapUsers();
        $this->ldapAuth->syncUsers();
        $afterCount = $this->countLdapUsers();

        $this->assertSame($beforeCount, $afterCount, 'No users should be synced when autoRefreshUsers is false.');
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function countLdapUsers(): int
    {
        return (int) User::find()->where(['auth_mode' => 'ldap'])->count();
    }

    private function createTestLdapAuth(): LdapAuth
    {
        return new LdapAuth([
            'hostname'          => getenv('LDAP_TEST_HOST'),
            'port'              => (int)(getenv('LDAP_TEST_PORT') ?: 389),
            'baseDn'            => getenv('LDAP_TEST_BASE_DN') ?: 'dc=example,dc=org',
            'bindUsername'      => getenv('LDAP_TEST_BIND_DN') ?: 'cn=admin,dc=example,dc=org',
            'bindPassword'      => getenv('LDAP_TEST_BIND_PASSWORD') ?: 'adminpassword',
            'userFilter'        => getenv('LDAP_TEST_USER_FILTER') ?: '(objectClass=inetOrgPerson)',
            'usernameAttribute' => getenv('LDAP_TEST_USERNAME_ATTRIBUTE') ?: 'uid',
            'emailAttribute'    => 'mail',
            'idAttribute'       => 'uid',
            'autoRefreshUsers'  => false,
        ]);
    }
}
