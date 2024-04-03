<?php

namespace user\acceptance;

use humhub\modules\user\models\User;
use user\AcceptanceTester;

class InvisibleUserCest
{
    public function testUserInvisible(AcceptanceTester $I)
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

        $I->amGoingTo('be sure Sara Tester is visible in administration');
        // Administration users list
        $I->amOnRoute(['/admin/user']);
        $I->waitForText($userName);

        $I->amGoingTo('be sure Sara Tester is visible for owner');
        $I->amUser2(true);
        // People
        $I->amOnRoute(['/people']);
        $I->waitForText('People');
        $I->see($userName);
        // Space members
        $I->amOnSpace1();
        $I->waitForText('Space members');
        $I->click('Members', '.statistics');
        $I->waitForText('Members', null, '#globalModal');
        $I->see($userName, '#globalModal');

        $I->amGoingTo('be sure Sara Tester is invisible for other users without permissions');
        $I->amUser1(true);
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
    }

    public function testUserVisibilityOnGuestMode(AcceptanceTester $I)
    {
        $I->wantTo('test profile visibilities on guest mode');
        $I->amOnUser1Profile();
        $I->waitForText('Please sign in');
        $I->see('If you\'re already a member, please login with your username/email and password.');

        $I->amGoingTo('enable guest mode');
        $I->amAdmin();
        $I->allowGuestAccess();

        $I->amGoingTo('make user public');
        $I->amUser1(true);
        $I->amOnPage('/user/account/edit-settings');
        $I->waitForText('Profile visibility');
        $I->selectOption('#accountsettings-visibility', User::VISIBILITY_ALL);
        $I->click('Save');
        $I->seeSuccess();

        $I->amGoingTo('view public user by guest');
        $I->logout();
        $I->amOnUser1Profile();
        $I->waitForText('Peter Tester');

        $I->amGoingTo('make user visible only for registered users');
        $I->amUser1();
        $I->amOnPage('/user/account/edit-settings');
        $I->waitForText('Profile visibility');
        $I->selectOption('#accountsettings-visibility', User::VISIBILITY_REGISTERED_ONLY);
        $I->click('Save');
        $I->seeSuccess();

        $I->amGoingTo('view user available only for registered users by guest');
        $I->logout();
        $I->amOnUser1Profile();
        $I->waitForText('Login required');
        $I->see('You need to login to view this user profile!');

        $I->amGoingTo('make user visible only for registered users');
        $I->amAdmin();
        $I->amOnRoute(['/admin/user/edit', 'id' => 2]);
        $I->waitForText('Visibility');
        $I->selectOption('#usereditform-visibility', User::VISIBILITY_HIDDEN);
        $I->click('Save');
        $I->seeSuccess();

        $I->amGoingTo('view private/inivisile user by guest');
        $I->logout();
        $I->amOnUser1Profile();
        $I->waitForText('Login required');
        $I->see('You need to login to view this user profile!');
    }

}
