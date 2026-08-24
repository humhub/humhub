<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace space\acceptance;

use Exception;
use space\AcceptanceTester;

class RequestMembershipCest
{
    /**
     * The membership button is a Vue island since 1.20 (`space\widgets\MembershipButton`), so
     * its mount element is the stable handle on it - there is no server-rendered anchor left to
     * address.
     */
    private const JOIN_BUTTON = 'membership-button a';

    /**
     * Its request-membership dialog is a `UiModal` teleported into the body, told apart from
     * the always-present (and closed) legacy `#globalModal` by the `show` class.
     */
    private const REQUEST_MODAL = '.modal.show';

    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testRequestMembershipAccept(AcceptanceTester $I)
    {
        $I->wantTo('ensure that accepting an users space membership works.');

        $I->amUser1();
        $I->amOnSpace1();
        $I->waitForElementVisible(self::JOIN_BUTTON);
        $I->click(self::JOIN_BUTTON);

        $I->waitForText('Request Membership', 10, self::REQUEST_MODAL);
        $I->fillField('#requestmembershipform-message', 'Hi, I want to join this space.');
        $I->click('Send', self::REQUEST_MODAL);
        $I->waitForText('Your request was successfully submitted to the space administrators.');
        $I->click('Close', self::REQUEST_MODAL);

        $I->waitForText('Pending');

        $I->amAdmin(true);
        $I->seeInNotifications('Peter Tester requests membership for the space Space 1', true);

        $I->waitForText('Pending Approvals', 10, '.tab-menu .active');
        $I->see('Hi, I want to join this space.', '.grid-view');
        $I->click('Accept', '.grid-view');

        $I->wait(1);

        $I->amUser1(true);

        $I->seeInNotifications('Admin Tester approved your membership for the space Space 1', true);
        $I->waitForText('User 1 Space 1 Post Private', 10, '#wallStream');
    }

    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testRequestMembershipDecline(AcceptanceTester $I)
    {
        $I->wantTo('ensure that declining an users space membership works.');

        $I->amUser1();
        $I->amOnSpace1();
        $I->waitForElementVisible(self::JOIN_BUTTON);
        $I->click(self::JOIN_BUTTON);

        $I->waitForText('Request Membership', 10, self::REQUEST_MODAL);
        $I->fillField('#requestmembershipform-message', 'Hi, I want to join this space.');
        $I->click('Send', self::REQUEST_MODAL);
        $I->waitForText('Your request was successfully submitted to the space administrators.');
        $I->click('Close', self::REQUEST_MODAL);

        $I->waitForText('Pending');

        $I->amAdmin(true);
        $I->seeInNotifications('Peter Tester requests membership for the space Space 1', true);

        $I->waitForText('Pending Approvals', 10, '.tab-menu .active');

        $I->click('.dropdown', '.controls-header');
        $I->waitForText('Members', 10, '.controls-header');
        $I->click('Members', '.controls-header');

        $I->waitForText('Member since');
        $I->see('Pending Approvals');
        $I->click('Pending Approvals');

        $I->waitForText('Decline');
        $I->click('Decline');

        $I->waitForElementVisible('#wallStream');
        $I->dontSeeInNotifications('Peter Tester requests membership for the space Space 1');

        $I->amUser1(true);

        $I->seeInNotifications('Admin Tester declined your membership request for the space Space 1', true);
        $I->waitForElementVisible(self::JOIN_BUTTON);
    }

    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testRequestMembershipRevoke(AcceptanceTester $I)
    {
        $I->wantTo('ensure that revoking an users space membership works.');

        $I->amUser1();
        $I->amOnSpace1();
        $I->waitForElementVisible(self::JOIN_BUTTON);
        $I->click(self::JOIN_BUTTON);

        $I->waitForText('Request Membership', 10, self::REQUEST_MODAL);
        $I->fillField('#requestmembershipform-message', 'Hi, I want to join this space.');
        $I->click('Send', self::REQUEST_MODAL);
        $I->waitForText('Your request was successfully submitted to the space administrators.');
        $I->click('Close', self::REQUEST_MODAL);

        $I->waitForText('Pending');
        $I->click('Pending');
        $I->waitForText('Confirm');
        $I->click('Confirm');
        $I->waitForText('Join'); // Back to dashboard
        $I->amOnSpace1();
        $I->waitForText('Join', 10, self::JOIN_BUTTON);

        $I->amAdmin(true);
        $I->dontSeeInNotifications('Peter Tester requests membership for the space Space 1');
        $I->amOnSpace1();
        $I->dontSeeElement('.panel-danger');

        $I->click('.dropdown', '.controls-header');
        $I->waitForText('Members', 10, '.controls-header');
        $I->click('Members', '.controls-header');

        $I->waitForText('Manage members');
        $I->dontSee('Pending Approvals');
    }
}
