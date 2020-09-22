<?php

namespace tests\codeception\_pages;

use tests\codeception\_support\BasePage;

/**
 * Represents admin page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class AdminPage extends BasePage
{

    public $route = 'admin';
}
