<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\functional;

use humhub\modules\user\models\User;
use tests\codeception\_pages\LoginPage;
use user\FunctionalTester;
use Yii;

class RegistrationCest
{
    /**
     * @param \FunctionalTester $I
     */
    public function _before(\FunctionalTester $I)
    {
        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', 1);
        Yii::$app->getModule('user')->settings->set('auth.needApproval', false);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRegisterInvalidEmail(FunctionalTester $I)
    {
        LoginPage::openBy($I);

        $I->see('Sign up');
        $I->fillField('#register-email', 'wrongEmail');
        $I->click('.btn-primary', '#invite-form');
        $I->see('Email is not a valid email address.');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRegister(FunctionalTester $I)
    {
        LoginPage::openBy($I);

        $I->see('Sign up');
        $I->fillField('#register-email', 'mytestmail@test.de');
        $I->click('.btn-primary', '#invite-form');
        $I->see('Registration successful!');

        $I->assertMailSent(1);
        $I->assertEqualsLastEmailSubject('Welcome to HumHub Test');

        $matches = [];
        preg_match('/(index-test.php.*)/', $I->grapLastEmailText(), $matches);

        $I->amOnPage(trim($matches[0]));
        $I->see('Account registration');

        $I->fillField('#user-username', 'RegistrationUser');
        $I->fillField('#password-newpassword', 'MyPassword');
        $I->fillField('#password-newpasswordconfirm', 'MyPassword');

        $I->fillField('#profile-firstname', 'Registration');
        $I->fillField('#profile-lastname', 'User');

        $I->click('.btn-primary', '#create-account-form');
        $I->seeRecord(User::class, [
            'email' => 'mytestmail@test.de',
            'username' => 'RegistrationUser',
        ]);
    }
}
