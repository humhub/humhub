<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\post\models\Post;
use PHPUnit\Framework\Assert;

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

    public function testSpacePageNamesEachLandmarkOnce(FunctionalTester $I)
    {
        $I->wantTo('ensure the menu column of a space is its named nav, and the sidebar the only aside');

        $I->amUser1();
        $I->amOnSpace1();

        $I->seeElement('.layout-nav-container nav[aria-label]');
        $I->dontSeeElement('aside.layout-nav-container');
        $I->seeNumberOfElements('aside[aria-label]', 1);
    }

    public function testDashboardHasOneHeading(FunctionalTester $I)
    {
        $I->wantTo('ensure the dashboard has one level-one heading, which it does not show');

        $I->amUser1();
        $I->amOnRoute('/dashboard/dashboard');
        $I->seeNumberOfElements('h1', 1);
        $I->see('Dashboard', 'h1.visually-hidden');
    }

    public function testSpaceHomeHasTheSpaceNameAsHeading(FunctionalTester $I)
    {
        $I->wantTo('ensure the name in the space header is the h1 of the space home, the entries are h2');

        $I->amAdmin();
        $I->amOnSpace1();
        $I->seeNumberOfElements('h1', 1);
        $I->seeElement('.img-profile-data h1');
    }

    public function testStreamEntryIsAHeadingBelowThePageHeading(FunctionalTester $I)
    {
        $I->wantTo('ensure a stream entry is named by an h2, its meta line is no heading');

        $I->amAdmin();
        $post = new Post();
        $post->message = 'Heading test';
        $post->content->setContainer($I->getFixtureSpace(0));
        Assert::assertTrue($post->save(), json_encode($post->getErrors()));
        $I->amOnSpace1('/space/space/stream');
        $html = implode('', array_column(json_decode($I->grabPageSource(), true)['content'] ?? [], 'output'));

        Assert::assertStringContainsString('<h2 class="h4 mt-0 wall-entry-title"', $html);
        Assert::assertStringContainsString('<div class="h5 wall-entry-meta">', $html);
        Assert::assertStringNotContainsString('<h4 class="mt-0"', $html);
        Assert::assertStringNotContainsString('<h5>', $html);
    }

    public function testSpaceSettingsPageHasOneHeading(FunctionalTester $I)
    {
        $I->wantTo('ensure a space settings page has its own title as the one h1, the space name is context');

        $I->amAdmin();
        $I->amOnSpace1('/space/manage/default/index');
        $I->seeNumberOfElements('h1', 1);
        $I->seeElement('.page-header h1');
        $I->seeElement('.img-profile-data div.h1');
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

    public function testHeaderSearchControlsAreNamedButtons(FunctionalTester $I)
    {
        $I->wantTo('ensure the header search can be closed with a named button');

        $I->amUser1();
        $I->amOnRoute('/dashboard/dashboard');

        $I->seeElement('#dropdown-search button#dropdown-search-close[type="button"][aria-label="Close"]');
        $I->seeElement('#dropdown-search .dropdown-search-keyword[aria-label="Search"]');
    }
}
