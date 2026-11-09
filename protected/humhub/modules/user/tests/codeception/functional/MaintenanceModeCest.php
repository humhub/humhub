<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\libs\UUID;
use humhub\modules\user\components\MaintenanceModeGate;
use humhub\modules\user\models\User;
use humhub\modules\user\services\PasswordRecoveryService;
use PHPUnit\Framework\Assert;
use user\FunctionalTester;
use Yii;

/**
 * Verifies the maintenance mode user gate (see docs/develop/user-gates.md).
 */
class MaintenanceModeCest
{
    public function _after(FunctionalTester $I)
    {
        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 0);
    }

    public function testNonAdminIsInterceptedAndLoggedOut(FunctionalTester $I)
    {
        $I->wantTo('ensure that a non-admin user is intercepted and logged out during maintenance');

        $I->amUser1();
        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        $I->amOnRoute('/dashboard/dashboard');

        $I->see('Maintenance mode is active');

        // The user was logged out: the admin login bypass now shows the login form
        $I->amOnPage('/index-test.php?r=user%2Fauth%2Flogin&maintenanceAdmin=1');
        $I->seeElement('input[name="Login[username]"]');
    }

    public function testAdminIsNotIntercepted(FunctionalTester $I)
    {
        $I->wantTo('ensure that admins can use the platform during maintenance');

        $I->amAdmin();
        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        $I->amOnRoute('/dashboard/dashboard');

        $I->see('Dashboard');
    }

    public function testPasswordRecoveryIsReachableForGuests(FunctionalTester $I)
    {
        $I->wantTo('ensure that the password recovery can be requested during maintenance');

        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        $I->amOnRoute('/user/password-recovery');
        $I->seeElement('#password-recovery-form');

        $I->fillField('#email_txt', 'admin@example.com');
        $I->fillField('#accountrecoverpassword-captcha', 'testme');
        $I->click('Reset password');
        $I->see('further instructions will be sent to you by email');

        // "Back" must lead to the admin login form instead of the maintenance notice
        $I->click('Back');
        $I->seeElement('input[name="Login[username]"]');
    }

    public function testPasswordRecoveryCaptchaIsNotRedirected(FunctionalTester $I)
    {
        $I->wantTo('ensure that the captcha of the password recovery form stays reachable during maintenance');

        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        // A blocked route would redirect to the maintenance notice, which also answers with 200
        $I->stopFollowingRedirects();
        $I->amOnRoute('/captcha/yii', ['refresh' => 1]);

        $I->seeResponseCodeIs(200);
    }

    public function testPasswordResetLinkIsReachableForGuests(FunctionalTester $I)
    {
        $I->wantTo('ensure that an admin can reset the password via the email link during maintenance');

        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        $admin = User::findOne(['username' => 'Admin']);
        $token = UUID::v4();
        $admin->getSettings()->set(PasswordRecoveryService::SETTING_TOKEN, $token . '.' . time());

        $I->amOnRoute('/user/password-recovery/reset', ['guid' => $admin->guid, 'token' => $token]);
        $I->fillField('#new_password', 'NewAdmin&humhub@PASS%worD!');
        $I->fillField('Password[newPasswordConfirm]', 'NewAdmin&humhub@PASS%worD!');
        $I->click('Change password');
        $I->see('Your password has been successfully changed!');

        // "Sign In" must lead to the admin login form instead of the maintenance notice
        $I->click('Sign In');
        $I->seeElement('input[name="Login[username]"]');
    }

    public function testCustomPasswordRecoveryRouteIsAllowed(FunctionalTester $I)
    {
        $I->wantTo('ensure that a custom internal password recovery route stays reachable during maintenance');

        Yii::$app->getModule('user')->passwordRecoveryRoute = ['/custom/recovery'];
        Assert::assertContains('/custom/recovery', (new MaintenanceModeGate())->getAllowedRoutes());

        // External URLs are no routes and must not end up in the allowed routes
        Yii::$app->getModule('user')->passwordRecoveryRoute = 'https://some.external.link';
        Assert::assertNotContains('https://some.external.link', (new MaintenanceModeGate())->getAllowedRoutes());
    }

    public function testServiceWorkerRequestIsNotRedirected(FunctionalTester $I)
    {
        $I->wantTo('ensure that non-HTML requests are answered with 403 instead of a redirect during maintenance');

        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        // A service worker fetch negotiates `Accept: */*` — it must never be redirected,
        // and especially must not poison the returnUrl for the next login (the browser
        // would be sent to the service worker source instead of the dashboard).
        $I->haveHttpHeader('Accept', '*/*');
        $I->amOnPage('/index-test.php?r=web%2Fpwa-service-worker%2Findex');

        $I->seeResponseCodeIs(403);
    }

    public function testAjaxRequestReceivesGateResponse(FunctionalTester $I)
    {
        $I->wantTo('ensure that AJAX requests receive a machine-readable gate response during maintenance');

        $I->amUser1();
        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, 1);

        $I->sendAjaxGetRequest('/index-test.php?r=dashboard%2Fdashboard');

        $I->seeResponseCodeIs(401);
        $I->see('maintenance-mode');
    }
}
