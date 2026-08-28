<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace space\acceptance;

use space\AcceptanceTester;

/**
 * The top menu sits outside the pjax container and is not re-rendered by a pjax navigation, so
 * the server has to tell the client which entry is active on the page just loaded - otherwise
 * the entry of the page left behind keeps the highlight.
 */
class TopMenuActiveCest
{
    public function testMovingIntoASpaceTakesTheHighlightOffTheDashboard(AcceptanceTester $I)
    {
        $I->wantTo('ensure the top menu highlight follows a navigation out of the dashboard');

        $I->amUser1();
        $I->amOnDashboard();

        $I->waitForElementVisible('#top-menu-nav a.nav-link.active');
        $I->seeElement('#top-menu-nav a.nav-link.active[href*="dashboard"]');

        // Into a space through the space menu, which is not a top menu entry: nothing on the
        // client marks an entry here, so this only works if the server says so.
        $I->click('#space-menu');
        $I->waitForElementVisible('#space-menu-dropdown [data-space-chooser-item]', 10);
        $I->click('#space-menu-dropdown [data-space-chooser-item]');

        $I->waitForElementNotVisible('#top-menu-nav a.nav-link.active', 15);
        $I->seeInCurrentUrl('/s/');
        $I->dontSeeElement('#top-menu-nav a.nav-link.active');

        $I->logout();
    }
}
