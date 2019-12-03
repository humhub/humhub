<?php
namespace user\acceptance;

use user\AcceptanceTester;
use tests\codeception\_pages\AccountSettingsPage;

class ChangeEmailCest
{
    public function testChangeEmail(AcceptanceTester $I)
    {
        $I->wantTo('ensure that changing email works');

        $I->amGoingTo('change email address');
        $I->amUser();
        $I->clickAccountDropDown();
        $I->click('Account settings');
        $I->waitForElementVisible('#profile-tabs', 20);
        $I->expectTo('see the profile edit form');

        $I->waitForElementVisible('.nav-tabs', 20);
        $I->click('Change Email');
        $I->waitForText('Your current E-mail address is');

        $I->amGoingTo('fill only new email');
        $I->fillField('#accountchangeemail-newemail', 'new@email.com');
        $I->fillField('#accountchangeemail-currentpassword', '');

        $I->scrollToBottom();
        $I->click('save');
        $I->wait(1);
        $I->expectTo('see an error');
        $I->see('Current password cannot be blank.');

        $I->amGoingTo('fill password and not valid new email');
        $I->fillField('#accountchangeemail-newemail', 'newemail.com');
        $I->fillField('#accountchangeemail-currentpassword', '123qwe');

        $I->scrollToBottom();
        $I->click('save');
        $I->wait(1);
        $I->expectTo('see an error');
        $I->see('New E-Mail address is not a valid email address.');

        $I->amGoingTo('fill all required fields with valid data');
        $I->fillField('#accountchangeemail-newemail', 'new@email.com');
        $I->fillField('#accountchangeemail-currentpassword', '123qwe');

        $I->scrollToBottom();
        $I->click('save');
        $I->wait(1);
        $I->expectTo('see no errors after saving');
        $I->dontSee('Current password cannot be blank.');
        $I->dontSee('New E-Mail address is not a valid email address.');
        $I->wait(5);
        $I->see('We´ve just sent an confirmation e-mail to your new address.');
    }

}
