<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\acceptance;

use user\AcceptanceTester;
use Yii;

/**
 * The confirmation dialog of the sudo mode in the browser (docs/develop/concept-sudo-mode.md): the protected action
 * waits for the password and runs once it is confirmed.
 */
class SudoCest
{
    public function _after(AcceptanceTester $I)
    {
        Yii::$app->getModule('user')->settings->delete('auth.sudoAdmin');
    }

    /**
     * Signs in with "Remember me" and drops the session: the next request signs in again by the cookie, which is no
     * interactive login, so the sudo mode is not active.
     */
    private function amSignedInByCookie(AcceptanceTester $I, string $username, string $password): void
    {
        $I->amOnRoute('/user/auth/login');
        $I->executeJS("try { sessionStorage.removeItem('humhub.user.login.email'); } catch (e) {}");
        $I->fillField('Login[username]', $username);
        $I->click('#continue-button');
        $I->waitForElement('#login_password');
        $I->fillField('Login[password]', $password);
        $I->executeJS("document.querySelector('input[type=checkbox][name=\"Login[rememberMe]\"]').checked = true;");
        $I->click('#login-button');
        $I->waitForElementVisible('#account-dropdown-link', 30);

        $I->resetCookie('PHPSESSID');
    }

    public function testTheDialogConfirmsAndTheActionRuns(AcceptanceTester $I)
    {
        $I->wantTo('confirm my password in the dialog before my username changes');
        $this->amSignedInByCookie($I, 'User1', 'user^humhub@PASS%worD!');

        $I->amOnRoute('/user/account/edit-settings');
        $I->waitForElementVisible('form[action*="change-email"]');

        $I->click('Username', '.accordion');
        $I->waitForElementVisible('input[name="AccountChangeUsername[newUsername]"]');
        $I->seeElement('form[action*="change-username"][data-sudo-required]');
        $I->dontSeeElementInDOM('input[name="AccountChangeUsername[currentPassword]"]');
        $I->fillField('AccountChangeUsername[newUsername]', 'User1Confirmed');
        $I->click('Save', 'form[action*="change-username"]');

        $I->expectTo('see the dialog instead of the change');
        $I->waitForText('Confirm your password', 10, '.modal.show#globalModal');
        $I->fillField('SudoConfirmForm[password]', 'wrong');
        $I->click('Confirm', '.modal.show#globalModal');
        $I->waitForText('Your password is incorrect!', 10, '.modal.show#globalModal');

        $I->fillField('SudoConfirmForm[password]', 'user^humhub@PASS%worD!');
        $I->click('Confirm', '.modal.show#globalModal');

        $I->expectTo('see the change go through');
        $I->waitForText('Your username has been successfully changed', 20);
        $I->dontSeeElement('form[data-sudo-required]');
    }

    public function testCancellingLeavesTheFormUnsent(AcceptanceTester $I)
    {
        $I->wantTo('cancel the dialog and stay where I am');
        $this->amSignedInByCookie($I, 'User1', 'user^humhub@PASS%worD!');

        $I->amOnRoute('/user/account/edit-settings');
        $I->waitForElementVisible('form[action*="change-email"]');
        $I->fillField('AccountChangeEmail[newEmail]', 'user1-cancelled@example.com');
        $I->click('Save', 'form[action*="change-email"]');

        $I->waitForText('Confirm your password', 10, '.modal.show#globalModal');
        $I->click('Cancel', '.modal.show#globalModal');
        $I->waitForElementNotVisible('#globalModal');

        $I->seeInCurrentUrl('edit-settings');
        $I->seeElement('form[action*="change-email"][data-sudo-required]');
        $I->dontSee('confirmation e-mail');
    }

    public function testTheAdministrationAsksWhenEnabled(AcceptanceTester $I)
    {
        $I->wantTo('confirm my password before the administration opens');
        Yii::$app->getModule('user')->settings->set('auth.sudoAdmin', 1);
        $this->amSignedInByCookie($I, 'Admin', 'admin&humhub@PASS%worD!');

        $I->amOnDashboard();
        $I->clickAccountDropDown();
        $I->click('Administration');

        $I->waitForText('Confirm your password', 10, '.modal.show#globalModal');
        $I->fillField('SudoConfirmForm[password]', 'admin&humhub@PASS%worD!');
        $I->click('Confirm', '.modal.show#globalModal');

        $I->waitForElementVisible('#admin-menu', 20);
        $I->see('Users', '.page-header-title');
    }
}
