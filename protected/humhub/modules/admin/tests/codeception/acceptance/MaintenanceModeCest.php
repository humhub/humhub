<?php

namespace admin\acceptance;

use tests\codeception\_pages\LoginPage;
use admin\AcceptanceTester;
use Yii;

class MaintenanceModeCest
{

    public function testAdminLoginWithMO(AcceptanceTester $I)
    {
        $I->wantTo('ensure that login works for admin');

        Yii::$app->settings->set('maintenanceMode', 1);

        $loginPage = LoginPage::openBy($I);

        $I->amGoingTo('try to login with correct credentials');
        $loginPage->login('Admin', 'test');
        $I->expectTo('see dashboard');
        $I->waitForText('DASHBOARD');
        $I->dontSee('Administration');
    }

    public function testUserLoginWithMO(AcceptanceTester $I)
    {
        $I->wantTo('ensure that regular user login not works');

        Yii::$app->settings->set('maintenanceMode', 1);

        $loginPage = LoginPage::openBy($I);

        $I->amGoingTo('try to login with correct credentials');
        $loginPage->login('User1', '123qwe');
        $I->expectTo('see login');
        $I->waitForText('Maintenance mode');
        $I->dontSee('DIRECTORY');

        Yii::$app->settings->set('maintenanceMode', 0);
    }

}
