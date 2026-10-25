<?php

namespace tests\codeception\_pages;

use tests\codeception\_support\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class AccountSettingsPage extends BasePage
{
    public $route = 'user/account/edit';

    public function clickPermissions()
    {
        $this->actor->click('Security & Privacy');
        $this->actor->waitForText('Privacy', 30, '.nav-pills');
        $this->actor->click('Privacy', '.nav-pills');
        $this->actor->waitForText('These settings allow you to determine', 30);
    }

}
