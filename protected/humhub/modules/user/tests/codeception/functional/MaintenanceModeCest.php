<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\modules\user\components\MaintenanceModeGate;
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
