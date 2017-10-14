<?php
namespace user\acceptance;

use user\AcceptanceTester;
use tests\codeception\_pages\DirectoryMemberPage;

class InviteCest
{
    public function testUserInvite(AcceptanceTester $I)
    {
        $I->wantTo('ensure that password recovery form works.');
        
        $I->amUser();
        DirectoryMemberPage::openBy($I);
        $I->click('Send invite');

        $I->waitForText('Invite new people', null,'#globalModal');
        
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