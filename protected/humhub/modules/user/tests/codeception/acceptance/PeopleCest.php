<?php
namespace user\acceptance;

use user\AcceptanceTester;

class PeopleCest
{
    public function testPeoplePageAccessibility(AcceptanceTester $I)
    {
        $I->wantToTest('people page visibility for guests');

        $I->amGoingTo('disable guest access to people page');

        $I->amAdmin();
        $I->allowGuestAccess();
        $I->logout();

        $I->amOnRoute('/dashboard');
        $I->dontSeeElement('[data-menu-id="people"]');

        $I->amGoingTo('enable guest access to people page');

        $I->amAdmin();
        $I->allowGuestAccessPeoplePage();
        $I->logout();

        $I->seeElement('[data-menu-id="people"]');
        $I->jsClick('[data-menu-id="people"]');
        $I->amOnRoute('/people');
        $I->see('People');
    }
}
