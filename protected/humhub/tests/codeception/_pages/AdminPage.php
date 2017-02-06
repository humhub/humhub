<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents admin page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class AdminPage extends BasePage
{

    public $route = 'admin';
}
