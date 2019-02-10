<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\acceptance;

use tests\codeception\_pages\DirectoryMemberPage;
use user\AcceptanceTester;

class InviteCest
{
    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testDashboardInviteByMail(AcceptanceTester $I)
    {
        $I->wantTo('ensure that inviting external users by mail form works.');

        $I->amUser();
        DirectoryMemberPage::openBy($I);

        $I->click('Send invite');
        $I->waitForText('Invite new people', null, '#globalModal');

        $I->amGoingTo('invite an already existing user email');
        $I->fillField('#emails', 'user1@example.com');
        $I->click('Send invite');
        $I->expectTo('see an error message');
        $I->waitForText('user1@example.com is already registered!');

        $I->amGoingTo('invite an non existing user email');
        $I->fillField('#emails', 'user1234@example.com');
        $I->click('Send invite');
        $I->expectTo('see a confirm message');
        $I->seeSuccess();
    }
}
