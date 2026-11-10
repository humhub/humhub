<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\functional;

use FunctionalTester;

/**
 * The landmarks of the page frame (docs/ui/keyboard.md, "Landmarks and names").
 *
 * @since 1.20
 */
class LandmarksCest
{
    public function testPageFrameLandmarks(FunctionalTester $I)
    {
        $I->wantTo('ensure the page frame exposes its landmarks');

        $I->amUser1();
        $I->amOnRoute('/dashboard/dashboard');

        $I->seeElement('header#topbar-first');
        $I->seeElement('nav#topbar-second[aria-label]');
        $I->seeElement('main#main-content');
        $I->seeElement('aside.layout-sidebar-container[aria-label]');
    }

    public function testSignInPageLandmarksAndHeading(FunctionalTester $I)
    {
        $I->wantTo('ensure the sign-in page has a main landmark, one heading and a labelled field');

        $I->amOnRoute('/user/auth/login');

        $I->seeElement('main#main-content #login-form');
        $I->seeElement('footer .footer-nav-login, footer .powered');
        $I->seeNumberOfElements('h1', 1);
        $I->see('Sign In', '#login-form .panel-heading h1');
        $I->seeElement('label[for="login_username"]');
    }

    public function testActiveMenuLinkIsTheCurrentPage(FunctionalTester $I)
    {
        $I->wantTo('ensure the active navigation link carries aria-current');

        $I->amUser1();
        $I->amOnRoute('/dashboard/dashboard');

        $I->seeElement('#topbar-second a.nav-link.active[aria-current="page"]');
        $I->dontSeeElement('#topbar-second a.nav-link[aria-current]:not(.active)');
    }
}
