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
        if(method_exists($this->actor, 'wait')) {
            $this->actor->wait(1);
        }
        $this->actor->fillField('Login[username]', $username);
        $this->actor->fillField('Login[password]', $password);
        $this->actor->click('#login-button');
    }
    
    public function selfInvite($email)
    {
        $this->actor->submitForm('#invite-form', array('Invite' => array(
            'email' => $email
       )));
    }

}
