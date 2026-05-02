<?php

namespace ldap\acceptance;

use ldap\AcceptanceTester;
use tests\codeception\_pages\LoginPage;

/**
 * Acceptance tests for LDAP login flows.
 *
 * Two scenarios are covered:
 *   1. All required profile fields are mapped from LDAP → the user is auto-registered
 *      and redirected to the dashboard without seeing a registration form.
 *   2. A required profile field (firstname, mapped from givenName) is absent in the LDAP
 *      entry → HumHub cannot auto-register and shows the registration form instead.
 *
 * Prerequisites (set via env vars):
 *   LDAP_TEST_HOST, LDAP_TEST_PORT, LDAP_TEST_BASE_DN, LDAP_TEST_BIND_DN,
 *   LDAP_TEST_BIND_PASSWORD, LDAP_TEST_USER_FILTER, LDAP_TEST_USERNAME_ATTRIBUTE
 *
 * The LDAP client is configured in dynamic.php when LDAP_TEST_HOST is set, so no
 * database mutation is needed to enable LDAP for these tests.
 */
class LdapLoginCest
{
    /**
     * A user whose LDAP entry contains all required profile fields (john.doe has
     * givenName which maps to the required firstname field) is auto-registered and
     * redirected to the dashboard – no registration form is shown.
     */
    public function testDirectLoginWithAllFieldsMapped(AcceptanceTester $I): void
    {
        $I->wantTo('log in as an LDAP user with all required profile fields mapped');

        $loginPage = LoginPage::openBy($I);
        $loginPage->login('john.doe', 'Password123');

        $I->expectTo('be redirected to the dashboard without a registration form');
        $I->waitForText('John Doe', 10);
        $I->seeCurrentUrlEquals('/dashboard');
        $I->dontSee('Registration');
    }

    /**
     * A user whose LDAP entry is missing givenName (the source attribute for the
     * required firstname profile field) cannot be auto-registered.  HumHub shows
     * the registration form so the user can fill in the missing field manually.
     */
    public function testLoginWithMissingRequiredFieldShowsRegistrationForm(AcceptanceTester $I): void
    {
        $I->wantTo('see the registration form when a required LDAP field is absent');

        $loginPage = LoginPage::openBy($I);
        $loginPage->login('no.name', 'Password789');

        $I->expectTo('see the registration form asking for the missing firstname');
        $I->waitForText('Create account', 10);
        $I->seeInField('#profile-firstname', '');
    }
}
