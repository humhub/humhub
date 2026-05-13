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
 * One scripted end-to-end run rather than three independent tests — the
 * cookie is signed by Yii (cookieValidationKey), so we can't fabricate one
 * with Selenium's setCookie and expect the server to honour it.
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

    public function testRememberUsernameRoundtrip(AcceptanceTester $I)
    {
        $I->wantTo('store, replay and clear the username via the "Remember login name" cookie');

        $I->amGoingTo('login with the "Remember login name" box ticked');
        LoginPage::openBy($I);
        $I->fillField('Login[username]', 'User1');
        $I->click('#continue-button');
        $I->waitForElement('#login_password');
        $I->checkOption('Login[rememberUsername]');
        $I->fillField('Login[password]', 'user^humhub@PASS%worD!');
        $I->click('#login-button');
        $I->waitForText('User 2 Space 2 Post Public');

        $I->expectTo('see the username cookie set by the server');
        $I->seeCookie(self::COOKIE_NAME);

        $I->amGoingTo('logout and revisit the login page');
        $I->logout();
        LoginPage::openBy($I);

        $I->expectTo('be auto-skipped to Step 2 with the remembered username displayed');
        $I->waitForElement('#login_password');
        $I->dontSeeElement('input[name="Login[username]"]');
        $I->see('User1');

        $I->amGoingTo('use the back-arrow to forget the username');
        $I->click('#login-back');

        $I->expectTo('see a blank Step 1 again with the cookie cleared');
        $I->waitForElement('input[name="Login[username]"]');
        $I->dontSeeElement('#login_password');
        $I->dontSeeCookie(self::COOKIE_NAME);
    }
}
