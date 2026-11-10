<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use user\FunctionalTester;

/**
 * The sudo mode (docs/develop/concept-sudo-mode.md): the confirmation, and the account actions it protects
 */
class SudoCest
{
    private const PASSWORD = 'user^humhub@PASS%worD!';

    public function testAnInteractiveLoginStartsTheSudoMode(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-settings');
        $I->dontSeeElement('form[data-sudo-required]');

        $I->submitForm('#account-settings-form', ['AccountSettings[username]' => 'User1Renamed']);
        $I->seeInSource('Your username has been successfully changed');
    }

    public function testWithoutTheSudoModeTheConfirmationComesFirst(FunctionalTester $I)
    {
        // A programmatic login is no interactive one: the sudo mode is not active
        $I->amLoggedInAs(User::findOne(['id' => 2]));
        $I->amOnRoute('/user/account/edit-settings');
        $I->seeElement('#account-settings-form[data-sudo-required*="sudo%2Fconfirm"]');
        $I->seeElement('input[name="AccountSettings[username]"][data-sudo-field]');
        $I->seeElement('input[name="AccountSettings[email]"][data-sudo-field]');

        $I->submitForm('#account-settings-form', ['AccountSettings[username]' => 'User1Renamed']);
        $I->seeCurrentUrlMatches('~user%2Fsudo%2Fconfirm~');
        $I->see('Confirm your password');
        $I->seeElement('#sudo-confirm-form input[type="password"][autocomplete="current-password"]');
        Assert::assertSame('User1', User::findOne(['id' => 2])->username);

        $I->submitForm('#sudo-confirm-form', ['SudoConfirmForm[password]' => 'wrong']);
        $I->seeCurrentUrlMatches('~user%2Fsudo%2Fconfirm~');
        $I->see('Your password is incorrect!');

        $I->submitForm('#sudo-confirm-form', ['SudoConfirmForm[password]' => self::PASSWORD]);
        // Back on the form, which no longer asks: a form posted without JavaScript is sent again
        $I->seeCurrentUrlMatches('~user%2Faccount%2Fedit-settings~');
        $I->dontSeeElement('form[data-sudo-required]');
        $I->submitForm('#account-settings-form', ['AccountSettings[username]' => 'User1Renamed']);
        $I->seeInSource('Your username has been successfully changed');
    }

    public function testOtherSettingsAreSavedWithoutTheSudoMode(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findOne(['id' => 2]));
        $I->amOnRoute('/user/account/edit-settings');
        $I->submitForm('#account-settings-form', []);
        $I->seeCurrentUrlMatches('~user%2Faccount%2Fedit-settings~');
        $I->dontSee('Confirm your password');
    }

    public function testAnAjaxRequestWithoutTheSudoModeGetsA401WithTheConfirmUrl(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findOne(['id' => 2]));
        $I->sendAjaxPostRequest('/index-test.php?r=user%2Faccount%2Fedit-settings', ['AccountSettings[email]' => 'user1-new@example.com']);
        $I->seeResponseCodeIs(401);
        Assert::assertStringContainsString('user%2Fsudo%2Fconfirm', $I->grabHttpHeader('X-Sudo-Required'));
        Assert::assertStringContainsString('"sudo":true', $I->grabPageSource());
        $I->dontSeeEmailIsSent();
    }

    public function testTheConfirmationDialogIsLoadedByAjax(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findOne(['id' => 2]));
        $I->sendAjaxGetRequest('/index-test.php?r=user%2Fsudo%2Fconfirm&returnUrl=%2Findex-test.php%3Fr%3Ddashboard');
        $I->seeResponseCodeIs(200);
        $source = $I->grabPageSource();
        Assert::assertStringContainsString('id="sudo-confirm-modal"', $source);
        Assert::assertStringContainsString('data-action-click="ui.modal.submit"', $source);
        Assert::assertStringContainsString('data-action-data-type="json"', $source);
        Assert::assertStringNotContainsString('<html', $source);
    }

    public function testRepeatedWrongPasswordsAreThrottled(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findOne(['id' => 2]));
        $I->amOnRoute('/user/sudo/confirm');
        for ($i = 0; $i < 6; $i++) {
            $I->submitForm('#sudo-confirm-form', ['SudoConfirmForm[password]' => $i < 5 ? 'wrong' : self::PASSWORD]);
        }
        $I->see('Too many attempts, try again later.');
        \Yii::$app->cache->delete('user.sudo.failures.2');
    }

    public function testTheReturnUrlStaysOnThisSite(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findOne(['id' => 2]));
        $I->amOnRoute('/user/sudo/confirm', ['returnUrl' => 'https://evil.example.com/']);
        $I->submitForm('#sudo-confirm-form', ['SudoConfirmForm[password]' => self::PASSWORD]);
        $I->seeCurrentUrlMatches('~dashboard~');
    }
}
