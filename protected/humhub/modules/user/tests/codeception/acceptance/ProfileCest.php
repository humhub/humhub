<?php
namespace user\acceptance;

use user\AcceptanceTester;
use tests\codeception\_pages\AccountSettingsPage;

class ProfileCest
{
    /**
     * @skip
     */
    public function testSaveProfile(AcceptanceTester $I)
    {
        $I->wantTo('ensure that saving the account works');

        $I->amGoingTo('save access my account settings');
        $I->amUser();
        $I->clickAccountDropDown();
        $I->click('Account settings');
        $I->expectTo('see the profile edit form');

        $I->waitForElementVisible('#profile-tabs', 20);
        //$I->seeElement();

        $I->amGoingTo('fill only my firstname');
        $I->fillField('#profile-firstname', 'MyFirstName');
        $I->fillField('#profile-lastname', '');

        $I->scrollToBottom();
        $I->click('save');
        $I->wait(5);
        $I->expectTo('see an error');
        $I->see('Last name cannot be blank.');

        $I->amGoingTo('fill all required fields plus birthday and hide year field');
        $I->fillField('#profile-lastname', 'MyLastName');
        $I->click('label[for="profile-birthday_hide_year"]'); // Hide year in profile
        $I->fillField('#profile-birthday', 'Apr 16, 1987');

        $I->scrollToTop();

        $I->click('Communication');
        $I->wait(1);
        $I->fillField('#profile-phone_private', '089733333');
        $I->click('Social bookmarks');
        $I->wait(1);
        $I->fillField('#profile-url', 'http://www.asdf.de');
        $I->scrollToBottom();
        $I->click('save');
        $I->wait(1);

        $I->expectTo('see no errors after saving');

        $I->dontSeeElement('.has-error');

        $I->amGoingTo('access my profiles about page');
        $profile = $I->amOnProfile();
        $profile->clickAbout();

        $I->expectTo('see all my saved values and my birthday without year');
        $I->see('MyFirstName');
        $I->see('MyLastName');
        $I->dontSee('April 16, 1987');
        $I->see('16. April');
        $I->seeLink('Communication');
    }

    public function testViewAboutPage(AcceptanceTester $I)
    {
        $I->wantTo('ensure that my profile works as expected.');

        $I->amUser2();
        $I->amOnPage('/u/user1/user/profile/home');

        $I->expectTo('see the profile of User2');
        $I->see('Follow');
        $I->see('Stream');
        $I->see('About');

        $I->click('About');
        $I->wait(1);
        $I->see('Peter');
        $I->see('Tester');

        $accountSettings = AccountSettingsPage::openBy($I);
        $accountSettings->clickPermissions();
        $I->selectOption('select[data-attribute0*=ViewAboutPage]', 'Deny');

        $I->amUser1(true);
        $I->amOnPage('/u/user2/user/profile/home');
        $I->dontSee('About');
    }

    public function testHomePageUrl(AcceptanceTester $I)
    {
        $I->wantTo('ensure that my profile home page URL alias routed as expected.');

        $I->amUser2();
        $I->amOnPage('/u/user2/user/profile/home');

        $I->waitForText('Stream');
        $I->click('Stream');
        $I->waitForText('Profile menu');
        $I->see('Sara Tester');
        $I->see('Stream');

        $I->expectTo('see the alias of profile home page URL');
        $I->seeCurrentUrlEquals('/u/user2/home');
    }

    public function testAboutPageUrl(AcceptanceTester $I)
    {
        $I->wantTo('ensure that my profile about page URL alias routed as expected.');

        $I->amUser2();
        $I->amOnPage('/u/user2/user/profile/home');

        $I->waitForText('About');
        $I->click('About');
        $I->waitForText('Profile menu');
        $I->see('Sara Tester');
        $I->see('About');

        $I->expectTo('see the alias of profile about page URL');
        $I->seeCurrentUrlEquals('/u/user2/about');
    }
}
