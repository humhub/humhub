<?php

namespace user\acceptance;

use humhub\modules\user\models\User;
use tests\codeception\_pages\LoginPage;
use user\AcceptanceTester;

class LoginCest
{
    public function testUserLogin(AcceptanceTester $I)
    {
        $I->wantTo('ensure that login works');

        $loginPage = LoginPage::openBy($I);

        $I->amGoingTo('try to continue with an empty username on Step 1');
        $loginPage->login('', '');
        $I->expectTo('see Step 1 validation error');
        $I->waitForText('Username or Email cannot be blank.');

        $I->amGoingTo('try to submit Step 2 with an empty password');
        $loginPage->openPasswordStep('User1');
        $I->click('#login-button');
        $I->waitForText('Password cannot be blank.');

        $I->amGoingTo('try to login with wrong credentials');
        // Reset to Step 1 — the previous assertion left us on Step 2, where
        // LoginPage::login() can't fill the username field.
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('User1', 'wrong');
        $I->expectTo('see validations errors');
        $I->waitForText('User or Password incorrect.');

        $I->amGoingTo('try to login with correct credentials');
        // Wrong-credentials POST left us on Step 2 — start over from Step 1.
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('User1', 'user^humhub@PASS%worD!');
        $I->expectTo('see dashboard');
        $I->waitForText('User 2 Space 2 Post Public');
        $I->dontSee('Administration');
    }

    public function testLoginByEMail(AcceptanceTester $I)
    {
        $I->wantTo('ensure that login with email works');
        $loginPage = LoginPage::openBy($I);
        $I->amGoingTo('try to login with admin credentials');
        $loginPage->login('user1@example.com', 'user^humhub@PASS%worD!');
        $I->expectTo('see dashboard');
        $I->waitForText('User 2 Space 2 Post Public');
    }

    public function testDisabledUser(AcceptanceTester $I)
    {
        $I->wantTo('ensure that disabled user cannot login');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('DisabledUser', 'user^humhub@PASS%worD!');
        $I->expectTo('see validations errors');
        $I->waitForText('Your account is disabled!');
    }

    public function testDisabledUserProfilePage(AcceptanceTester $I)
    {
        $I->wantTo('ensure that disabled user profile page is not viewable');
        $I->amAdmin();
        $I->amOnPage('/u/disableduser');
        $I->waitForText('This profile is disabled!');
    }

    public function testUnApprovedUser(AcceptanceTester $I)
    {
        $user = User::findOne(['id' => 4]);
        $user->status = User::STATUS_NEED_APPROVAL;
        $user->save();

        $I->wantTo('ensure that unapproved user cannot login');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('User3', 'user^humhub@PASS%worD!');
        $I->expectTo('see validations errors');
        $I->waitForText('Your account is not approved yet!');
    }


    public function testChangePassword(AcceptanceTester $I)
    {
        $user = User::findOne(['id' => 4]);
        $user->setMustChangePassword(true);
        $user->save();

        $I->wantTo('ensure that user need to change password');
        $loginPage = LoginPage::openBy($I);
        $loginPage->login('User3', 'user^humhub@PASS%worD!');

        $I->expectTo('see password change dialog');
        $I->waitForElement('//input[@name="Password[currentPassword]"]', 20);
        $I->seeInCurrentUrl('/must-change-password');

        $I->amGoingTo('open the dashboard before changing password');
        $I->amOnRoute(['/dashboard/dashboard']);
        $I->waitForElement('//input[@name="Password[currentPassword]"]', 20);
        $I->seeInCurrentUrl('/must-change-password');
    }

    //Login by email
}
