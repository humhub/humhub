<?php

namespace user\acceptance;

use user\AcceptanceTester;

class ImpersonateCest
{

    public function testUserImpersonation(AcceptanceTester $I)
    {
        $I->wantTo('ensure that impersonation works');
        $I->amAdmin();

        $I->amGoingTo('impersonate to Sara Tester');
        $I->impersonateUser('User2');
        $I->waitForText('Sara Tester');

        $I->amGoingTo('stop impersonation');
        $I->stopImpersonation();
        $I->waitForText('Admin Tester');

        $I->clickAccountDropDown();
        $I->dontSee('Stop impersonation');
    }

}
