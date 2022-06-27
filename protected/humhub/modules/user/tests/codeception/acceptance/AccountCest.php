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
        $I->waitForText('User Account');
        $I->click('Account Settings');

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
        $I->waitForElementVisible('.data-saved', 5);

        $I->selectOption('#accountsettings-language', 'English(US)');
        $I->click('Save');
        $I->waitForElementVisible('.data-saved', 5);
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
        $I->waitForText('User Account');
        $I->click('Account Settings');
        $I->waitForText('Basic Settings');

        $I->click('Notifications'); //Notification tab
        $I->waitForText('Notification Settings');

        $I->expectTo('see the notification settings form');
        $I->see('Following');
        $I->see('Mentionings');
        $I->jsClick('[name="NotificationSettings[settings][notification.followed_web]"]');
        $I->jsClick('[name="NotificationSettings[settings][notification.comments_web]"]');

        //$I->wait(20);

        $I->scrollToBottom();
        $I->click('Save', '.panel-body');

        $I->seeSuccess('Saved');

        // Todo proper checkbox tests...
        /*$I->amOnPage('index-test.php?r=notification%2Fuser');
        $I->waitForText('Notification Settings');
        $I->seeInField('[name="NotificationSettings[settings][notification.followed_web]"]', 0);
        $I->seeInField('[name="NotificationSettings[settings][notification.comments_web]"]', 0);*/
    }
}
