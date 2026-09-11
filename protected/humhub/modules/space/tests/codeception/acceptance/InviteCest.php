<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace space\acceptance;

use Exception;
use space\AcceptanceTester;

class InviteCest
{
    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testSpaceUserInviteAccept(AcceptanceTester $I)
    {
        $I->wantTo('ensure that accepting an users invitation to a space works.');

        $I->amUser1();
        $I->amOnSpace2();
        $I->click('Invite', '.panel-profile');
        $I->waitForText('Invite members', 10, '#globalModal');
        $I->selectUserFromPicker('#space-invite-user-picker', 'Sara Tester');
        $I->click('Send', '#globalModal');

        $I->amUser2(true);
        $I->seeInNotifications('Peter Tester invited you to the space Space 2', true);
        $I->waitForText('Accept Invite', 10, '.controls-header');
        $I->dontSee('Admin Space 2 Post Private', '#wallStream');
        $I->click('Accept Invite', '.controls-header');
        // Accepting is an API call of the `MembershipButton` island followed by the reload
        // its `reloadOnJoin` prop asks for (the space header renders it that way), so the
        // button is only gone once both have happened - the member state itself stays
        // hidden in the header (`show-member-state="false"`).
        $I->waitForElementNotVisible('.controls-header membership-button a', 10);
        $I->dontSee('Accept Invite');

        $I->amUser1(true);
        $I->seeInNotifications('Sara Tester accepted your invite for the space Space 2', true);
        $I->waitForText('Sara Tester joined this Space.');
    }

    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testSpaceUserInviteDecline(AcceptanceTester $I)
    {
        $I->wantTo('ensure that declining an user invitation to a space works.');

        $I->amUser1();
        $I->amOnSpace2();
        $I->click('Invite', '.panel-profile');
        $I->waitForText('Invite members', 10, '#globalModal');
        $I->selectUserFromPicker('#space-invite-user-picker', 'Sara Tester');
        $I->click('Send', '#globalModal');

        $I->amUser2(true);
        $I->seeInNotifications('Peter Tester invited you to the space Space 2', true);
        $I->waitForText('Accept Invite', 10, '.controls-header');

        $I->click('.dropdown-toggle', '.controls-header');
        $I->waitForText('Decline Invite', 10, '.controls-header');
        $I->click('Decline Invite');
        $I->waitForText('Join');

        $I->amUser1(true);
        $I->seeInNotifications('Sara Tester declined your invite for the space Space 2');
    }

    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testSpaceUserInviteRevoke(AcceptanceTester $I)
    {
        $I->wantTo('ensure that revoking an user invitation to a space works.');

        $I->amUser1();
        $I->amOnSpace2();
        $I->click('Invite', '.panel-profile');
        $I->waitForText('Invite members', 10, '#globalModal');
        $I->selectUserFromPicker('#space-invite-user-picker', 'Sara Tester');
        $I->click('Send', '#globalModal');

        $I->waitForElementNotVisible('#globalModal');

        $I->click('.dropdown', '.controls-header');
        $I->waitForText('Members', 10, '.controls-header');
        $I->click('Members', '.controls-header');

        $I->waitForText('Pending Invites');
        $I->click('Pending Invites');

        $I->waitForText('Cancel', 10, '.layout-content-container');
        $I->click('Cancel', '.layout-content-container');
        $I->acceptPopup();
        $I->waitForText('Member since', 10, '.layout-content-container');

        $I->amUser2(true);
        $I->seeInNotifications('Peter Tester revoked your invitation for the space Space 2');

    }
}
