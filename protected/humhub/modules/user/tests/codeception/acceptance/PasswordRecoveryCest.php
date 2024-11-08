<?php

namespace user\acceptance;

use user\AcceptanceTester;
use tests\codeception\_pages\LoginPage;

class PasswordRecoveryCest
{
    public function testPasswordRecovery(AcceptanceTester $I)
    {
        $I->wantTo('ensure that password recovery works');

        $I->amGoingTo('request a recovery mail for an invalid user email and wrong captcha');
        LoginPage::openBy($I);
        $I->wait(3);
        $I->waitForText('Forgot your password?');
        $I->jsClick('#password-recovery-link');
        $I->waitForText('Password recovery');
        $I->fillField('#email_txt', 'wrong@mail.de');
        $I->fillField('#accountrecoverpassword-verifycode', 'wrong');
        $I->click('Reset password');
        $I->wait(3);
        $I->expectTo('see error messages');
        $I->see('The verification code is incorrect.');

        $I->amGoingTo('request a recovery mail for an invalid user email');
        $I->fillField('#accountrecoverpassword-verifycode', 'testme');
        $I->click('Reset password');
        $I->wait(3);
        $I->expectTo('see confirm messages even with wrong email for safe reason');
        $I->see('Password recovery!');
        $I->see('If a user account associated with this email address exists, further instructions will be sent to you by email shortly.');

        $I->amGoingTo('request a recovery mail with valid data');
        LoginPage::openBy($I);
        $I->wait(3);
        $I->waitForText('Forgot your password?');
        $I->jsClick('#password-recovery-link');
        $I->waitForText('Password recovery');
        $I->fillField('#email_txt', 'user1@example.com');
        $I->fillField('#accountrecoverpassword-verifycode', 'testme');
        $I->click('Reset password');
        $I->wait(3);
        $I->expectTo('see confirm messages');
        $I->see('Password recovery!');
        $I->see('If a user account associated with this email address exists, further instructions will be sent to you by email shortly.');
    }
}
