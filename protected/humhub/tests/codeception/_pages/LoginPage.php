<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents login page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class LoginPage extends BasePage
{

    public $route = 'user/auth/login';

    /**
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        $this->actor->fillField('input[name="AccountLogin[username]"]', $username);
        $this->actor->fillField('input[name="AccountLogin[password]"]', $password);
        $this->actor->click('login-button');
    }

}
