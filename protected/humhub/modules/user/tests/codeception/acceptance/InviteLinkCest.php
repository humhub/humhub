<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\acceptance;

use Exception;
use user\AcceptanceTester;

class InviteLinkCest
{
    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testLinkInvite(AcceptanceTester $I)
    {
        $I->wantTo('ensure that test link invite is working.');

        // Enable Link Invite
        $I->amAdmin();
        $I->amOnPage('/admin/authentication');
        $I->checkOption('#authenticationsettingsform-internaluserscaninvitebyemail');
        $I->checkOption('#authenticationsettingsform-internaluserscaninvitebylink');
        $I->click('Save');


        // See Invite On People Page
        $I->amUser2(true);
        $I->amOnPage('/people');
        $I->click('Invite');
        $I->waitForText('Invite by link');

        $I->click('Invite by link');

        $link = $I->grabValueFrom('secureLink');

        $I->logout();

        $I->amOnUrl($link);
        $I->waitForText('registration');


        // See Invite on Space Page
        // Dont See on Spaces
        $I->amUser1();
        $I->amOnSpace2();
        $I->click('Invite');
        $I->waitForText('Invite by link');
    }


    /**
     * @param AcceptanceTester $I
     * @throws Exception
     */
    public function testDisable(AcceptanceTester $I)
    {
        $I->wantTo('ensure that link invite is not shown when disabled.');

        // Disable Link Invite
        $I->amAdmin();
        $I->amOnPage('/admin/authentication');
        $I->checkOption('#authenticationsettingsform-internaluserscaninvitebyemail');
        $I->uncheckOption('#authenticationsettingsform-internaluserscaninvitebylink');
        $I->click('Save');


        $I->amUser2(true);
        $I->amOnPage('/people');
        $I->click('Invite');
        $I->waitForText('Send invite');
        $I->dontSee('Invite by email');

        // Dont See on Spaces
        $I->amUser1(true);
        $I->amOnSpace2();
        $I->click('Invite');
        $I->waitForText('Pick users');
        $I->dontSee('Invite by link');

    }

}
