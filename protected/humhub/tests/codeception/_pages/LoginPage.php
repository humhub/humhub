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
        $this->actor->submitForm('#account-login-form', array('Login' => array(
            'username' => $username,
            'password' => $password
       )));
        
        /*$this->actor->fillField('input[name="Login[username]"]', $username);
        $this->actor->fillField('input[name="Login[password]"]', $password);
        $this->actor->click('login-button');*/
    }

}
