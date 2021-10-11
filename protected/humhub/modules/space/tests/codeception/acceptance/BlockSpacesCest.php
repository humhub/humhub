<?php
namespace space\acceptance;

use space\AcceptanceTester;

class BlockSpacesCest
{
    public function testBlockUsers(AcceptanceTester $I)
    {
        $I->wantTo('test users blocking for space');

        $I->amGoingTo('check the users blocking is enabled in system by default');
        $I->amAdmin();
        $this->isUsersBlockingEnabled($I);

        $I->amGoingTo('block some users for the space "Sara Tester"');
        $I->selectUserFromPicker('#space-blockedusersfield', 'Peter Tester');
        $I->click('Save');
        $I->seeSuccess('Saved');
        $I->see('Peter Tester', '.field-space-blockedusersfield');

        $I->amGoingTo('check the current user "Peter Tester" is blocked for the space "Space 1"');
        $I->amUser1(true);
        $I->amOnSpace1();
        $I->see('You are blocked for this page!');

        $I->amGoingTo('disable the users blocking');
        $I->amAdmin(true);
        $this->disableUsersBlocking($I);

        $I->amUser1(true);
        $this->isUsersBlockingDisabled($I);
        $I->amGoingTo('be sure the Space 1 is viewable for the user "Peter Tester"');
        $I->amOnSpace1();
        $I->see('Space 1');
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
        $I->amOnSpace1('/space/manage/default/index');
        $I->see('Blocked users');
    }

}
