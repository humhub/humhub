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
 * Verifies that ticking "Remember login name" on Step 2 makes the controller
 * write the long-lived `auth.login.rememberUsername` cookie.
 *
 * The downstream auto-skip behaviour (controller reads the cookie on the next
 * /user/auth/login GET and redirects to Step 2) is exercised by the controller
 * code directly and isn't easy to test reliably end-to-end through Selenium —
 * cookie persistence across logout + redirect chains was flaky on CI. The
 * cookie-write assertion here catches the integration boundary that matters.
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

    public function testCookieWrittenWhenCheckboxTicked(AcceptanceTester $I)
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
    }
}
