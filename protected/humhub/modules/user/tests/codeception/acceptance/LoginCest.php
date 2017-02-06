<?php

namespace user\acceptance;

use tests\codeception\_pages\LoginPage;
use user\AcceptanceTester;

class LoginCest
{

    public function testUserLogin(AcceptanceTester $I)
    {
        $I->wantTo('ensure that login works');

        $loginPage = LoginPage::openBy($I);

        $I->amGoingTo('try to login with empty credentials');
        $loginPage->login('', '');
        $I->expectTo('see validations errors');
        $I->waitForText('username or email cannot be blank.');
        $I->see('Password cannot be blank.');

        $I->amGoingTo('try to login with wrong credentials');
        $loginPage->login('User1', 'wrong');
        $I->expectTo('see validations errors');
        $I->waitForText('User or Password incorrect.');

        $I->amGoingTo('try to login with correct credentials');
        $loginPage->login('User1', '123qwe');
        $I->expectTo('see dashboard');
        $I->waitForText('User 2 Space 2 Post Public');
        $I->dontSee('Administration');
    }

    public function testLoginByEMail(AcceptanceTester $I)
    {
        $I->wantTo('ensure that login with email works');
        $loginPage = LoginPage::openBy($I);
        $I->amGoingTo('try to login with admin credentials');
        $loginPage->login('user1@example.com', '123qwe');
        $I->expectTo('see dashboard');
        $I->waitForText('User 2 Space 2 Post Public');
    }

    public function testDisabledUser(AcceptanceTester $I)
    {
        $I->wantTo('ensure that disabled user cannot login');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('DisabledUser', '123qwe');
        $I->expectTo('see validations errors');
        $I->waitForText('Your account is disabled!');
    }

    public function testUnApprovedUser(AcceptanceTester $I)
    {
        $user = \humhub\modules\user\models\User::findOne(['id' => 4]);
        $user->status = \humhub\modules\user\models\User::STATUS_NEED_APPROVAL;
        $user->save();

        $I->wantTo('ensure that unapproved user cannot login');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('User3', '123qwe');
        $I->expectTo('see validations errors');
        $I->waitForText('Your account is not approved yet!');
    }

    //Login by email
}
