<?php
namespace space\acceptance;

use space\AcceptanceTester;

class SpacesCest
{
    public function testSpacesPageAccessibility(AcceptanceTester $I)
    {
        $I->wantToTest('spaces page visibility for guests');

        $I->amGoingTo('disable guest access to spaces page');

        $I->amAdmin();
        $I->allowGuestAccess();
        $I->logout();

        $I->amOnRoute('/dashboard');
        $I->dontSeeElement('[data-menu-id="spaces"]');

        $I->amGoingTo('enable guest access to spaces page');

        $I->amAdmin();
        $I->allowGuestAccessSpacesPage();
        $I->logout();

        $I->seeElement('[data-menu-id="spaces"]');
        $I->jsClick('[data-menu-id="spaces"]');
        $I->amOnRoute('/spaces');
        $I->see('Spaces');
    }
}
