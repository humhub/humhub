<?php

namespace user\acceptance;

use user\AcceptanceTester;

class ChangeEmailCest
{
    public function testChangeEmail(AcceptanceTester $I)
    {
        $I->wantTo('ensure that changing email works');

        // A login by the form starts the sudo mode, so the change asks for no password (see SudoCest for the dialog)
        $I->amGoingTo('change email address');
        $I->amUser();
        $I->clickAccountDropDown();
        $I->click('My Settings');
        $I->waitForText('My Settings', 30, '.section-menu');
        $I->click('Account', '.section-menu');
        $I->waitForText('Your current e-mail address is');
        $I->dontSeeElementInDOM('#accountchangeemail-currentpassword');

        $I->amGoingTo('fill a not valid new email');
        $I->fillField('#accountchangeemail-newemail', 'newemail.com');
        $I->expectTo('see an error from browser side, because email is not valid and form cannot be submitted');
        $isValidForm = $I->executeJS('return document.querySelector("#accountchangeemail-newemail").checkValidity();');
        \PHPUnit\Framework\Assert::assertFalse($isValidForm);

        $I->amGoingTo('fill a valid new email');
        $I->fillField('#accountchangeemail-newemail', 'new@email.com');
        $I->click('Save', 'form[action*="change-email"]');
        $I->expectTo('see no errors after saving');
        $I->waitForText('We´ve just sent an confirmation e-mail to your new address.', 20);
        $I->dontSee('New E-Mail address is not a valid email address.');
    }
}
