<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\functional;

use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
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
        $I->amOnRoute('user/auth/register');

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
        $I->amOnRoute('user/auth/register');

        $I->see('Sign up');
        $I->fillField('#register-email', 'mytestmail@test.de');
        $I->click('.btn-primary', '#invite-form');
        $I->see('Almost there!');

        $I->assertMailSent(1);
        $I->assertEqualsLastEmailSubject('Welcome to HumHub Test');

        $matches = [];
        preg_match('/(index-test.php.*)/', (string) $I->grapLastEmailText(), $matches);

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

    /**
     * A validated invite token is an authorization independent of the global
     * "New users can register" (auth.anonymousRegistration) setting — the
     * registration form fields must still be rendered for it.
     *
     * Regression test for https://github.com/humhub/humhub/pull/8283:
     * disabling anonymousRegistration used to also hide the form on an
     * otherwise valid invite-by-e-mail link.
     *
     * @param FunctionalTester $I
     */
    public function testRegisterWithInviteTokenWhenSelfRegistrationDisabled(FunctionalTester $I)
    {
        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', 0);

        $invite = new Invite([
            'email' => 'invited-user@test.de',
            'source' => Invite::SOURCE_INVITE,
        ]);
        $invite->save(false);

        $I->amOnRoute('/user/registration', ['token' => $invite->token]);

        $I->see('Account registration');
        $I->seeElement('#user-username');
        $I->seeElement('#password-newpassword');

        $I->fillField('#user-username', 'InvitedUser');
        $I->fillField('#password-newpassword', 'MyPassword');
        $I->fillField('#password-newpasswordconfirm', 'MyPassword');
        $I->fillField('#profile-firstname', 'Invited');
        $I->fillField('#profile-lastname', 'User');

        $I->click('.btn-primary', '#create-account-form');
        $I->seeRecord(User::class, [
            'email' => 'invited-user@test.de',
            'username' => 'InvitedUser',
        ]);
    }
}
