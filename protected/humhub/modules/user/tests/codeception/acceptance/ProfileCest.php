<?php
namespace user\acceptance;

use user\AcceptanceTester;
use tests\codeception\_pages\DirectoryPage;
use tests\codeception\_pages\AccountSettingsPage;

class ProfileCest
{
    public function testSaveProfile(AcceptanceTester $I)
    {
        $I->wantTo('ensure that saving the account works');
        
        $I->amGoingTo('save access my account settings');
        $I->amUser();
        $I->clickAccountDropDown();
        $I->click('Account settings');
        $I->expectTo('see the profile edit form');
        $I->seeElement('#profile-tabs');
        
        $I->amGoingTo('fill only my firstname');
        $I->fillField('#profile-firstname', 'MyFirstName');
        $I->fillField('#profile-lastname', '');
        $I->click('save');
        $I->wait(1);
        $I->expectTo('see an error');
        $I->seeElement('.has-error');
        
        $I->amGoingTo('fill all required fields plus birthday and hide year field');
        $I->fillField('#profile-lastname', 'MyLastName');
        $I->fillField('#profile-birthday', '4/16/87');
        $I->click('/html/body/div[3]/div/div[2]/div/div[3]/form/div/div[1]/div[11]/label/div'); // Hide year in profile
        
        $I->scrollToTop();
        
        $I->click('Communication');
        $I->wait(1);
        $I->fillField('#profile-phone_private', '089733333');
        $I->click('Social bookmarks');
        $I->wait(1);
        $I->fillField('#profile-url', 'http://www.asdf.de');
        $I->click('save');
        $I->wait(1);
        $I->expectTo('see no errors after saving');
        $I->dontSeeElement('.has-error');
        
        $I->amGoingTo('access my profiles about page');
        $I->clickAccountDropDown();
        $I->click('My profile');
        $I->click('About');
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
        DirectoryPage::openBy($I);
        $I->click('Members');
        $I->click('User1');
        
        $I->expectTo('see the profile of User2');
        $I->see('Follow');
        $I->see('Stream');
        $I->see('About');
        
        $I->click('About');
        $I->wait(1);
        $I->see('Peter');
        $I->see('Tester');
        
        AccountSettingsPage::openBy($I);
        $I->click('Security');
        #$I->wait(2);
        $I->selectOption('select[data-attribute0*=ViewAboutPage]', 'Deny');
       
        $I->amUser1(true);
        DirectoryPage::openBy($I);
        $I->click('Members');
        $I->click('User2');
        
    }
}