<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 01.08.2017
 * Time: 20:22
 */

namespace humhub\modules\user\tests\codeception\functional;


use tests\codeception\_pages\LoginPage;
use Yii;
use user\FunctionalTester;

class RegistrationCest
{
    public function testRegister(FunctionalTester $I)
    {
        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', 1);
        Yii::$app->getModule('user')->settings->set('auth.needApproval', false);

        LoginPage::openBy($I);

        $I->see('Sign up');
        $I->fillField('#register-email', 'wronEmail');
        $I->click('.btn-primary', '#invite-form');
        $I->see('Email is not a valid email address.');

        $I->fillField('#register-email', 'mytestmail@test.de');
        $I->click('.btn-primary', '#invite-form');
        $I->see('Registration successful!');

        $I->assertMailSent(1);
        $I->assertEqualsLastEmailSubject('Welcome to HumHub Test');

        $lastEmailText = $I->grapLastEmailText();

        preg_match('/(index-test.php.*)/', $lastEmailText, $matches);

        $I->amOnPage(trim($matches[0]));
        $I->see('Account registration');

        $I->fillField('#user-username', 'RegistrationUser');
        $I->fillField('#password-newpassword', 'MyPassword');
        $I->fillField('#password-newpasswordconfirm', 'MyPassword');

        $I->fillField('#profile-firstname', 'Registration');
        $I->fillField('#profile-lastname', 'User');

        $I->click('.btn-primary', '#create-account-form');
        $I->seeElement('#wallStream');

    }

}