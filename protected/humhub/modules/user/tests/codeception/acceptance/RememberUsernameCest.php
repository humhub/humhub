<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\acceptance;

use tests\codeception\_pages\LoginPage;
use user\AcceptanceTester;

/**
 * Covers the cookie-based "Remember login name" behaviour:
 *  - Ticking the box on Step 2 stores the username in a long-lived cookie.
 *  - On a fresh visit to /user/auth/login the cookie auto-skips Step 1 and
 *    drops the visitor straight on the password screen.
 *  - The Step-2 back-arrow (`?forget=1`) clears the cookie and brings the
 *    visitor back to a blank Step 1.
 *
 * @since 1.19
 */
class RememberUsernameCest
{
    private const COOKIE_NAME = 'auth.login.rememberUsername';

    public function _before(AcceptanceTester $I)
    {
        $I->resetCookie(self::COOKIE_NAME);
    }

    public function _after(AcceptanceTester $I)
    {
        $I->resetCookie(self::COOKIE_NAME);
    }

    public function testCookieSetWhenCheckboxTicked(AcceptanceTester $I)
    {
        $I->wantTo('store the username in a cookie when "Remember login name" is ticked');

        LoginPage::openBy($I);
        $I->fillField('Login[username]', 'User1');
        $I->click('#continue-button');
        $I->waitForElement('#login_password');

        $I->checkOption('Login[rememberUsername]');
        $I->fillField('Login[password]', 'user^humhub@PASS%worD!');
        $I->click('#login-button');
        $I->waitForText('User 2 Space 2 Post Public');

        $I->seeCookie(self::COOKIE_NAME);
        $I->assertSame('User1', $I->grabCookie(self::COOKIE_NAME));
    }

    public function testStep1AutoSkippedWhenCookiePresent(AcceptanceTester $I)
    {
        $I->wantTo('auto-skip Step 1 when the username cookie is set');

        $I->amOnPage('/');
        $I->setCookie(self::COOKIE_NAME, 'User1');

        LoginPage::openBy($I);

        $I->expectTo('land on Step 2 with the password input visible');
        $I->waitForElement('#login_password');
        $I->dontSeeElement('input[name="Login[username]"]');
        $I->see('User1');
    }

    public function testForgetLinkClearsCookieAndReturnsToStep1(AcceptanceTester $I)
    {
        $I->wantTo('drop the cookie and bounce back to Step 1 via the back-arrow link');

        $I->amOnPage('/');
        $I->setCookie(self::COOKIE_NAME, 'User1');

        LoginPage::openBy($I);
        $I->waitForElement('#login_password');

        $I->click('#login-back');

        $I->expectTo('see a blank Step 1 again');
        $I->waitForElement('input[name="Login[username]"]');
        $I->dontSeeElement('#login_password');
        $I->dontSeeCookie(self::COOKIE_NAME);
    }
}
