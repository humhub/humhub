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
    
    public function clickSecurity()
    {
        $this->actor->click('Security');
        $this->actor->waitForText('Security settings', 30);
    }

}
