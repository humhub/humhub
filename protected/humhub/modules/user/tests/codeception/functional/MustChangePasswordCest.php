<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\modules\user\models\User;
use user\FunctionalTester;

/**
 * Verifies the must-change-password user gate (see docs/develop/user-gates.md).
 */
class MustChangePasswordCest
{
    public function testFullPageRequestIsRedirectedToPasswordChange(FunctionalTester $I)
    {
        $I->wantTo('ensure that a user who must change the password is redirected to the change form');

        $I->amUser1();
        User::findOne(2)->setMustChangePassword(true);

        $I->amOnRoute('/dashboard/dashboard');

        $I->see('Due to security reasons you are required to change your password');
    }

    public function testAjaxRequestIsAnsweredWithGateResponse(FunctionalTester $I)
    {
        $I->wantTo('ensure that AJAX requests receive a machine-readable gate response instead of a 302');

        $I->amUser1();
        User::findOne(2)->setMustChangePassword(true);

        $I->sendAjaxGetRequest('/index-test.php?r=dashboard%2Fdashboard');

        $I->seeResponseCodeIs(401);
        $I->see('must-change-password');
    }

    public function testTranslationsStayReachable(FunctionalTester $I)
    {
        $I->wantTo('ensure the translations endpoint stays reachable while the password gate is open');

        $I->amUser1();
        User::findOne(2)->setMustChangePassword(true);

        // The gate's own page loads its JS translations via this AJAX endpoint — if the
        // gate intercepts it, yii.js navigates back to the gate page in an endless
        // reload loop (X-Redirect on every translations request)
        $I->sendAjaxGetRequest('/index-test.php?r=i18n%2Ftranslations&category=base');

        $I->seeResponseCodeIs(200);
    }

    public function testLogoutStaysReachable(FunctionalTester $I)
    {
        $I->wantTo('ensure that logout stays reachable while the password gate is open');

        $I->amUser1();
        User::findOne(2)->setMustChangePassword(true);

        $I->sendAjaxPostRequest('/index-test.php?r=user%2Fauth%2Flogout');

        $I->amOnRoute('/dashboard/dashboard');
        $I->see('Sign in');
    }
}
