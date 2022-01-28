<?php
namespace user\acceptance;

use user\AcceptanceTester;

class BlockUsersCest
{
    public function testBlockUsers(AcceptanceTester $I)
    {
        $I->wantTo('test users blocking');

        $I->amGoingTo('check the users blocking is enabled in system by default');
        $I->amUser2();
        $this->isUsersBlockingEnabled($I);

        $I->amGoingTo('block some users for the current user "Sara Tester"');
        $I->selectUserFromPicker('#accountsettings-blockedusers', 'Peter Tester');
        $I->selectUserFromPicker('#accountsettings-blockedusers', 'Sara Tester');
        $I->selectUserFromPicker('#accountsettings-blockedusers', 'Andreas Tester');
        $I->click('Save');
        $I->seeSuccess('Saved');
        $I->see('Peter Tester', '.field-accountsettings-blockedusers');
        $I->dontSee('Sara Tester', '.field-accountsettings-blockedusers');
        $I->see('Andreas Tester', '.field-accountsettings-blockedusers');

        $I->amGoingTo('check the current user "Peter Tester" is blocked for the user "Sara Tester"');
        $I->amUser1(true);
        $I->amOnUser2Profile();
        $I->see('You are blocked for this page!');

        $I->amGoingTo('enable the users blocking');
        $I->amAdmin(true);
        $this->disableUsersBlocking($I);

        $I->amUser1(true);
        $this->isUsersBlockingDisabled($I);
        $I->amGoingTo('be sure the user "Sara Tester" is viewable for the user "Peter Tester"');
        $I->amOnUser2Profile();
        $I->see('Sara Tester');
    }

    private function disableUsersBlocking(AcceptanceTester $I)
    {
        $I->amOnPage('/admin/authentication');
        $I->click('[for="authenticationsettingsform-blockusers"]');
        $I->wait(1);
        $I->click('Save');
    }

    private function isUsersBlockingDisabled(AcceptanceTester $I)
    {
        $I->amOnPage('/user/account/edit-settings');
        $I->dontSee('Blocked users');
    }

    private function isUsersBlockingEnabled(AcceptanceTester $I)
    {
        $I->amOnPage('/user/account/edit-settings');
        $I->see('Blocked users');
    }

}
