<?php

namespace tests\codeception\_pages;

use tests\codeception\_support\BasePage;

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
        if(method_exists($this->actor, 'waitForText')) {
            $this->actor->waitForText('Please sign in');
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
