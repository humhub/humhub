<?php

namespace user\acceptance;

use humhub\modules\user\models\forms\AccountRecoverPassword;
use tests\codeception\_pages\LoginPage;
use user\AcceptanceTester;

class PasswordRecoveryCest
{
    public function testPasswordRecovery(AcceptanceTester $I)
    {
        $I->wantTo('ensure that password recovery works');

        $I->amGoingTo('request a recovery mail for an invalid user email and captcha not checked');
        LoginPage::openBy($I);
        $I->wait(3);
        $I->waitForText('Forgot your password?');
        $I->jsClick('#password-recovery-link');
        $I->waitForText('Password recovery');
        $I->fillField('#email_txt', AccountRecoverPassword::TESTING_BYPASS_CAPTCHA_EMAIL);
        $I->executeJS('$(\'[class*="captcha"]\').remove();'); // Remove any existing captcha to simulate not checking it or hacking
        $I->click('Reset password');
        $I->wait(3);
        $I->expectTo('see error messages');
        $I->see('We couldn\'t verify that you\'re human. Please check the box again.', '#accountrecoverpassword-captcha + .invalid-feedback');

        $I->amGoingTo('request a recovery mail for an invalid user email');
        $I->fillField('#email_txt', 'wrong@mail.de');
        $I->executeJS('$(\'[class*="captcha"]\').remove();');
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
        $I->executeJS('$(\'[class*="captcha"]\').remove();');
        $I->click('Reset password');
        $I->wait(3);
        $I->expectTo('see confirm messages');
        $I->see('Password recovery!');
        $I->see('If a user account associated with this email address exists, further instructions will be sent to you by email shortly.');
    }
}
