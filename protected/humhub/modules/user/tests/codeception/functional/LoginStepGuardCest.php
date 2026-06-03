<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use user\FunctionalTester;

/**
 * Verifies the session-coupling between the Step 1 (identity) and Step 2
 * (password) login views — Step 2 must not be reachable without a username
 * stashed in the session by Step 1, otherwise the session-trust assumption
 * (controller forces the username from the session, never from the form) would
 * be bypassable by hitting /user/auth/password directly.
 *
 * @since 1.19
 */
class LoginStepGuardCest
{
    public function testPasswordStepRedirectsToStep1WithoutSession(FunctionalTester $I)
    {
        $I->wantTo('ensure /user/auth/password bounces to Step 1 without a Step-1 session');

        $I->amOnRoute('/user/auth/password');

        // Pretty URLs are off in test mode — the route shows up encoded as
        // `?r=user%2Fauth%2Flogin`. Check the rendered Step 1 markup instead,
        // which is unambiguous either way.
        $I->seeElement('input[name="Login[username]"]');
        $I->dontSeeElement('input[name="Login[password]"]');
    }
}
