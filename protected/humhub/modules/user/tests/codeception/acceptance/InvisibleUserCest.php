<?php

namespace user\acceptance;

use user\AcceptanceTester;

class InvisibleUserCest
{

    public function testUserImpersonation(AcceptanceTester $I)
    {
        $userName = 'Sara Tester';
        $I->wantTo('ensure that user invisibility works');
        $I->amAdmin();

        $I->amGoingTo('be sure Sara Tester is visible');
        // People
        $I->amOnRoute(['/people']);
        $I->waitForText($userName);
        // Space members
        $I->amOnSpace1();
        $I->waitForText('Space members');
        $I->click('Members', '.statistics');
        $I->waitForText($userName, null, '#globalModal');

        $I->amGoingTo('make Sara Tester invisible');
        $I->amOnRoute(['/admin/user/edit', 'id' => 3]);
        $I->waitForText($userName);
        $I->selectOption('select#usereditform-visibility', 'Invisible');
        $I->click('Save');
        $I->seeSuccess();

        $I->amGoingTo('be sure Sara Tester is visible');
        // People
        $I->amOnRoute(['/people']);
        $I->waitForText('People');
        $I->dontSee($userName);
        // Space members
        $I->amOnSpace1();
        $I->waitForText('Space members');
        $I->click('Members', '.statistics');
        $I->waitForText('Members', null, '#globalModal');
        $I->dontSee($userName, '#globalModal');
        // Administration users list
        $I->amOnRoute(['/admin/user']);
        $I->waitForText($userName);
    }

}
