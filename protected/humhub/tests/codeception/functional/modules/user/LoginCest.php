<?php
use tests\codeception\_pages\LoginPage;

class LoginCest
{
    public function testUserLogin(FunctionalTester $I)
    {
        $I->wantTo('ensure that login works');
        
        $loginPage = LoginPage::openBy($I);
        
        $I->amGoingTo('try to login with empty credentials');
        $loginPage->login('', '');
        $I->expectTo('see validations errors');
        $I->see('username or email cannot be blank.');
        $I->see('Password cannot be blank.');

        $I->amGoingTo('try to login with wrong credentials');
        $loginPage->login('User2', 'wrong');
        $I->expectTo('see validations errors');
        $I->see('User or Password incorrect.');

        $I->amGoingTo('try to login with correct credentials');
        $loginPage->login('User2', '123qwe');
        $I->expectTo('see dashboard');
        $I->see('Dashboard');
        $I->dontSee('Administration');
    }
    
    public function testAdminLogin(FunctionalTester $I)
    {
        $I->wantTo('ensure that login as admin works');
        $loginPage = LoginPage::openBy($I);
        $I->amGoingTo('try to login with admin credentials');
        $loginPage->login('admin', 'test');
        $I->expectTo('see dashboard');
        $I->see('Dashboard');
        $I->see('Administration');
    }
    
    public function testLogout(FunctionalTester $I)
    {
        $I->wantTo('ensure that logout works');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('admin', 'test');
        $I->expectTo('see logout link');
        $I->seeLink('Logout');
        $I->click('Logout');
        $I->expectTo('see login screen');
        $I->seeInCurrentUrl('login');
    }
    
    public function testDisabledUser(FunctionalTester $I)
    {
        $I->wantTo('ensure that disabled user cannot login');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('DisabledUser', '123qwe');
        $I->expectTo('see validations errors');
        $I->see('Your account is disabled!');
    }
    
    public function testUnApprovedUser(FunctionalTester $I)
    {
        $I->wantTo('ensure that unapproved user cannot login');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('UnApprovedUser', '123qwe');
        $I->expectTo('see validations errors');
        $I->see('Your account is not approved yet!');
    }
    
    //Login by email
}