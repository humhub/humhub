<?php

namespace space\acceptance;

use space\AcceptanceTester;

class CreateSpaceCest
{

    /**
     * Create private space
     * 
     * @param AcceptanceTester $I
     */
    public function testCreateSpace(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->wantToTest('the creation of a space');
        $I->amGoingTo('create a new space and invite another user');

        $I->click('#space-menu');
        $I->waitForText('Create new space');
        $I->click('Create new space');

        $I->waitForText('Create new space', 30, '#globalModal');
        $I->fillField('Space[name]', 'Space 1');
        $I->fillField('Space[description]', 'SpaceDescription');

        $I->click('#access-settings-link');
        $I->waitForElementVisible('.field-space-join_policy');

        // Only by invite
        $I->jsClick('#space-join_policy [value="0"]');

        // Private visibility
        $I->jsClick('#space-visibility [value="0"]');

        $I->click('Next', '#globalModal');

        $I->waitForText('Name "Space 1" has already been taken.', 20, '#globalModal');
        $I->fillField('Space[name]', 'MySpace');
        $I->click('Next', '#globalModal');

        // Fresh test environments (travis) won't have any preinstalled modules.
        // Perhaps we should fetch an module manually by default.
        try {
            $I->waitForText('Add Modules', 5, '#globalModal');
            $I->click('Next', '#globalModal');
        } catch (\Exception $e) {
            // Do this if it's not present.
        }

        $I->waitForText('Invite members', 10, '#globalModal');
        $I->selectUserFromPicker('#space-invite-user-picker', 'Peter Tester');
        $I->wait(1);

        $I->click('Done', '#globalModal');
        $I->waitForText('MySpace');
        $I->waitForText('This space is still empty!');

        $I->amUser1(true);
        $I->seeInNotifications('invited you to the space MySpace');

        //TODO: Test private space
        // User Approval
    }

    // User Approval
    // Space settings
}
