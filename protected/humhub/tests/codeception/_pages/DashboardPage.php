<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class DashboardPage extends BasePage
{

    public $route = 'dashboard/dashboard';

}
