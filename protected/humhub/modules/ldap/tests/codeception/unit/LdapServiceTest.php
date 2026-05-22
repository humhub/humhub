<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\connection\LdapConnectionRegistry;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\services\LdapService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Integration tests for {@see LdapService}.
 *
 * These tests connect to a real OpenLDAP server. Skipped automatically when
 * LDAP_TEST_HOST is not set so local runs without Docker are safe.
 *
 * In CI the workflow (.github/workflows/ldap-test.yml) starts an OpenLDAP
 * container and seeds it with the test users defined in .github/ldap/init.ldif.
 */
class LdapServiceTest extends HumHubDbTestCase
{
    private LdapService $ldapService;
    private LdapConnectionConfig $ldapConfig;
    private LdapAuth $ldapAuth;

    protected function _before(): void
    {
        parent::_before();

        if (empty(getenv('LDAP_TEST_HOST'))) {
            $this->markTestSkipped(
                'LDAP integration tests are skipped. Set LDAP_TEST_HOST to enable them.',
            );
        }

        $this->ldapConfig = $this->createTestConfig();

        try {
            $this->ldapService = new LdapService($this->ldapConfig);
        } catch (\Exception $e) {
            $this->markTestSkipped('Cannot connect to LDAP server: ' . $e->getMessage());
        }

        $registry = new LdapConnectionRegistry();
        $registry->setConfigs(['ldap' => $this->ldapConfig]);
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $module->setConnectionRegistry($registry);

        $this->ldapAuth = new LdapAuth(['connectionId' => 'ldap', 'clientId' => 'ldap']);
        $this->ldapAuth->init();
    }

    // ---------------------------------------------------------------------------
    // User lookup
    // ---------------------------------------------------------------------------

    public function testCountUsersReturnsAtLeastTwoTestUsers(): void
    {
        $this->assertGreaterThanOrEqual(2, $this->ldapService->countUsers());
    }

    public function testGetUserDnByUsernameReturnsExpectedDn(): void
    {
        $dn = $this->ldapService->getUserDn('john.doe');

        $this->assertNotNull($dn);
        $this->assertStringContainsStringIgnoringCase('john.doe', $dn);
    }

    public function testGetUserDnByEmailReturnsExpectedDn(): void
    {
        $dn = $this->ldapService->getUserDn('john@example.org');

        $this->assertNotNull($dn);
    }

    public function testGetUserDnReturnsNullForNonExistentUser(): void
    {
        $this->assertNull($this->ldapService->getUserDn('nobody.here'));
    }

    public function testGetEntryReturnsAttributesForUser(): void
    {
        $dn = $this->ldapService->getUserDn('john.doe');
        $this->assertNotNull($dn);

        $entry = $this->ldapService->getEntry($dn);

        $this->assertNotNull($entry);
        $this->assertArrayHasKey('uid', $entry);
        $this->assertArrayHasKey('mail', $entry);
        $this->assertSame('john.doe', $entry['uid']);
        $this->assertSame('john@example.org', $entry['mail']);
    }

    public function testGetAllUserEntriesReturnsAllLdapUsers(): void
    {
        $entries = $this->ldapService->getAllUserEntries();

        $this->assertNotEmpty($entries);
        $this->assertGreaterThanOrEqual(2, count($entries));

        foreach ($entries as $entry) {
            $this->assertArrayHasKey('dn', $entry);
        }
    }

    public function testGetAllUserEntriesIncludeExpectedUsers(): void
    {
        $entries = $this->ldapService->getAllUserEntries();

        $uids = array_map(static fn(array $e) => $e['uid'] ?? null, $entries);

        $this->assertContains('john.doe', $uids);
        $this->assertContains('jane.doe', $uids);
    }

    // ---------------------------------------------------------------------------
    // Authentication
    // ---------------------------------------------------------------------------

    public function testAttemptAuthSucceedsWithCorrectCredentials(): void
    {
        $dn = $this->ldapService->attemptAuth('john.doe', 'Password123');

        $this->assertNotNull($dn);
        $this->assertStringContainsStringIgnoringCase('john.doe', $dn);
    }

    public function testAttemptAuthSucceedsWithEmailAsUsername(): void
    {
        $dn = $this->ldapService->attemptAuth('john@example.org', 'Password123');

        $this->assertNotNull($dn);
    }

    public function testAttemptAuthFailsWithWrongPassword(): void
    {
        $this->assertNull($this->ldapService->attemptAuth('john.doe', 'WrongPassword'));
    }

    public function testAttemptAuthFailsForNonExistentUser(): void
    {
        $this->assertNull($this->ldapService->attemptAuth('nobody.here', 'Password123'));
    }

    // ---------------------------------------------------------------------------
    // DN list
    // ---------------------------------------------------------------------------

    public function testGetDnListReturnsMatchingEntries(): void
    {
        $dnList = $this->ldapService->getDnList('(objectClass=inetOrgPerson)');

        $this->assertNotEmpty($dnList);
        $this->assertGreaterThanOrEqual(2, count($dnList));
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function createTestConfig(): LdapConnectionConfig
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
        ]);
    }
}
