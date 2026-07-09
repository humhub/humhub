<?php

namespace admin\acceptance;

use tests\codeception\_pages\LoginPage;
use admin\AcceptanceTester;
use Yii;

class MaintenanceModeCest
{
    public function _after(AcceptanceTester $I)
    {
        Yii::$app->settings->set('maintenanceMode', 0);
    }

    public function testAdminLoginWithMO(AcceptanceTester $I)
    {
        $I->wantTo('ensure that login works for admin during maintenance');

        Yii::$app->settings->set('maintenanceMode', 1);

        $loginPage = LoginPage::openBy($I, ['maintenanceAdmin' => 1]);

        $I->amGoingTo('try to login with admin credentials via the maintenance bypass');
        $loginPage->login('Admin', 'admin&humhub@PASS%worD!');
        $I->expectTo('see dashboard');
        $I->waitForText('DASHBOARD');
    }

    public function testMaintenancePageShownToGuest(AcceptanceTester $I)
    {
        $I->wantTo('ensure that the maintenance view is shown when accessing /user/auth/login');

        Yii::$app->settings->set('maintenanceMode', 1);

        LoginPage::openBy($I);

        $I->expectTo('see maintenance page instead of the login form');
        $I->waitForText('Maintenance mode');
        $I->dontSeeElement('#login_username');
        $I->seeLink('Admin login');
    }

    public function testUserLoginWithMO(AcceptanceTester $I)
    {
        $I->wantTo('ensure that regular user login is rejected during maintenance');

        Yii::$app->settings->set('maintenanceMode', 1);

        $loginPage = LoginPage::openBy($I, ['maintenanceAdmin' => 1]);

        $I->amGoingTo('try to login with regular user credentials');
        $loginPage->login('User1', 'user^humhub@PASS%worD!');

        $I->expectTo('be bounced back to the maintenance page with an error');
        $I->waitForText('Maintenance mode');
        $I->see('Only administrators can sign in during maintenance mode.');
        $I->dontSee('DIRECTORY');
    }
}
