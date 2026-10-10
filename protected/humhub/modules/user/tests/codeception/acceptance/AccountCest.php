<?php

namespace user\acceptance;

use user\AcceptanceTester;
use tests\codeception\_pages\DirectoryPage;

class AccountCest
{
    public function testBaseAccountSettings(AcceptanceTester $I)
    {
        $I->wantTo('ensure that the basic account settings work');

        $I->amGoingTo('save access my account settings');
        $I->amUser();
        $I->amOnProfile();

        $I->click('Edit account');
        $I->waitForText('Your Account');
        $I->click('General');

        $I->waitForText('Basic Settings');

        $I->amGoingTo('fill the basic settings form');

        $I->selectFromPicker('#accountsettings-tags', 'Tester');
        $I->selectFromPicker('#accountsettings-tags', 'Actor');
        #$I->selectOption('#accountsettings-language', 'Deutsch');
        $I->click('Save');

        /*
        $I->expectTo('see the german translation');
        $I->see('Sprache');
        $I->see('Speichern');
        $I->click('Save');
        $I->seeSuccess();

        $I->selectOption('#accountsettings-language', 'English(US)');
        $I->click('Save');
        $I->seeSuccess();
        */

        $I->seeSuccess('Saved');

        //$I->amOnProfile();
        $directory = DirectoryPage::openBy($I);
        $directory->clickMembers();
        $I->expectTo('see my user tags');
        $I->see('Tester');
        $I->see('Actor');
    }

    public function testSaveBaseNotifications(AcceptanceTester $I)
    {
        $I->wantTo('ensure that the notification settings can be saved');

        $I->amGoingTo('save access my account settings');
        $I->amUser();
        $I->amOnProfile();

        $I->click('Edit account');
        $I->waitForText('Your Account');
        $I->click('General');
        $I->waitForText('Basic Settings');

        $I->click('Notifications'); //Notification tab
        $I->waitForText('Categories');

        $I->expectTo('see the notification settings');
        $I->waitForText('Directly addressed to you');
        $I->see('Reactions on my content');

        $I->amGoingTo('switch off the e-mails about reactions; the change is saved right away');
        $I->click('[data-category="social"] .c-notification-settings__category-head');
        $I->waitForElementVisible('[name="NotificationSettings[categories.social.email]"]');
        $I->jsClick('[name="NotificationSettings[categories.social.email]"]');

        $I->waitForText('Saved', 10, '.c-notification-settings__status');
        $I->see('Custom', '[role="radiogroup"] [aria-checked="true"]');
    }
}
