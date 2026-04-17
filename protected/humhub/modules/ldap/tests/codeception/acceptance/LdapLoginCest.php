<?php

namespace ldap\acceptance;

use ldap\AcceptanceTester;
use tests\codeception\_pages\LoginPage;

/**
 * Acceptance tests for LDAP login flows.
 *
 * Scenarios covered:
 *
 * 1. Auto-sync OFF, all required profile fields are provided by LDAP
 *    → user logs in directly and lands on the dashboard.
 *
 * 2. Auto-sync OFF, a required profile field has no LDAP mapping
 *    → user is redirected to the registration form to fill in the missing
 *      field before the account is created.
 *
 * Both tests skip when LDAP_TEST_HOST is not set in the environment so that
 * local runs without a Docker LDAP container are unaffected.
 */
class LdapLoginCest
{
    // LDAP test user defined in _data/init.ldif
    private const TEST_USERNAME = 'john.doe';
    private const TEST_PASSWORD = 'Password123';

    // ---------------------------------------------------------------------------
    // Suite-level setup / teardown
    // ---------------------------------------------------------------------------

    public function _before(AcceptanceTester $I): void
    {
        if (empty(getenv('LDAP_TEST_HOST'))) {
            throw new \Codeception\Exception\Skip(
                'LDAP acceptance tests are skipped. Set LDAP_TEST_HOST to enable them.',
            );
        }

        // The DynamicFixtureHelper reloads all configured fixtures automatically
        // before each test, so no explicit loadFixtures() call is needed here.
    }

    public function _after(AcceptanceTester $I): void
    {
        // Disable LDAP again so other acceptance tests are not affected.
        $this->disableLdap($I);
    }

    // ---------------------------------------------------------------------------
    // Test 1 – all required fields mapped: direct login to dashboard
    // ---------------------------------------------------------------------------

    /**
     * When all required profile fields are mapped from LDAP attributes, an
     * unknown LDAP user logging in for the first time should be auto-registered
     * and land directly on the dashboard — no manual registration step needed.
     *
     * john.doe in init.ldif has both givenName (→ firstname) and sn (→ lastname),
     * which are the only required profile fields in the default fixtures.
     */
    public function testDirectLoginWithAllFieldsMapped(AcceptanceTester $I): void
    {
        $I->wantTo('log in with LDAP credentials when all required fields are mapped and land on the dashboard');

        $this->configureLdap($I, refreshUsers: false);

        $loginPage = LoginPage::openBy($I);
        $loginPage->login(self::TEST_USERNAME, self::TEST_PASSWORD);

        $I->expectTo('be on the dashboard after successful LDAP auto-registration');
        $I->waitForText('Latest activities', 10);
        $I->dontSeeInCurrentUrl('/user/registration');
    }

    // ---------------------------------------------------------------------------
    // Test 2 – required field not mapped: registration form shown
    // ---------------------------------------------------------------------------

    /**
     * When a required profile field has no LDAP mapping, the user cannot be
     * auto-registered. On their first LDAP login they must be redirected to the
     * registration form so they can provide the missing value manually.
     *
     * Setup: remove the LDAP attribute mapping for the "firstname" profile field
     * (which is required by default). This means john.doe's givenName value is
     * ignored and the field stays blank → auto-registration fails → registration
     * form is shown.
     */
    public function testLoginWithMissingRequiredFieldShowsRegistrationForm(AcceptanceTester $I): void
    {
        $I->wantTo('see the registration form when a required profile field has no LDAP mapping');

        $this->configureLdap($I, refreshUsers: false);

        // Remove the ldap_attribute mapping for "firstname" (profile field id=1).
        $I->amAdmin();
        $I->amOnPage('/admin/user-profile/edit-field?id=1');
        $I->waitForElementVisible('#profilefield-ldap_attribute');
        $I->clearField('#profilefield-ldap_attribute');
        $I->jsClick('#edit-profile-field-root [type=submit]');
        $I->seeSuccess();

        $I->logout();

        // john.doe now has no firstname mapped → auto-registration will fail
        $loginPage = LoginPage::openBy($I);
        $loginPage->login(self::TEST_USERNAME, self::TEST_PASSWORD);

        $I->expectTo('be redirected to the account registration form');
        $I->waitForText('Account registration', 10);
        $I->seeElement('#registration-form');

        // Fill in the missing firstname field and submit the form
        $I->waitForElementVisible('#profile-firstname');
        $I->fillField('#profile-firstname', 'John');
        $I->scrollToBottom();
        $I->click('.btn-primary', '#create-account-form');

        $I->expectTo('land on the dashboard after completing registration');
        $I->waitForText('Latest activities', 10);
        $I->dontSeeInCurrentUrl('/user/registration');
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    /**
     * Log in as admin, configure LDAP via the admin settings form, then log out.
     */
    private function configureLdap(AcceptanceTester $I, bool $refreshUsers): void
    {
        $I->amAdmin();
        $I->amOnPage('/ldap/admin');
        $I->waitForElementVisible('#authentication-settings-form');

        $I->checkOption('#ldapsettings-enabled');
        $I->clearField('#ldapsettings-hostname');
        $I->fillField('#ldapsettings-hostname', getenv('LDAP_TEST_HOST') ?: '127.0.0.1');
        $I->clearField('#ldapsettings-port');
        $I->fillField('#ldapsettings-port', getenv('LDAP_TEST_PORT') ?: '389');
        $I->clearField('#ldapsettings-username');
        $I->fillField('#ldapsettings-username', getenv('LDAP_TEST_BIND_DN') ?: 'cn=admin,dc=example,dc=org');
        $I->clearField('#ldapsettings-passwordfield');
        $I->fillField('#ldapsettings-passwordfield', getenv('LDAP_TEST_BIND_PASSWORD') ?: 'adminpassword');
        $I->clearField('#ldapsettings-basedn');
        $I->fillField('#ldapsettings-basedn', getenv('LDAP_TEST_BASE_DN') ?: 'dc=example,dc=org');
        $I->clearField('#ldapsettings-userfilter');
        $I->fillField('#ldapsettings-userfilter', getenv('LDAP_TEST_USER_FILTER') ?: '(objectClass=inetOrgPerson)');
        $I->clearField('#ldapsettings-usernameattribute');
        $I->fillField('#ldapsettings-usernameattribute', getenv('LDAP_TEST_USERNAME_ATTRIBUTE') ?: 'uid');
        $I->clearField('#ldapsettings-idattribute');
        $I->fillField('#ldapsettings-idattribute', 'uid');

        if ($refreshUsers) {
            $I->checkOption('#ldapsettings-refreshusers');
        } else {
            $I->uncheckOption('#ldapsettings-refreshusers');
        }

        $I->jsClick('#authentication-settings-form [type=submit]');
        $I->seeSuccess();

        $I->logout();
    }

    /**
     * Disable LDAP via the admin settings form.
     */
    private function disableLdap(AcceptanceTester $I): void
    {
        try {
            $I->amAdmin();
            $I->amOnPage('/ldap/admin');
            $I->waitForElementVisible('#authentication-settings-form', 5);
            $I->uncheckOption('#ldapsettings-enabled');
            $I->jsClick('#authentication-settings-form [type=submit]');
            $I->seeSuccess();
            $I->logout();
        } catch (\Throwable) {
            // Best-effort cleanup – don't fail the test if this step errors
        }
    }
}
