<?php

use tests\codeception\_pages\LoginPage;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    public function amAdmin()
    {
        LoginPage::openBy($this)->login('admin', 'test');
        $this->see('Dashboard');
        $this->see('Administration');
    }
    
    public function amUser($user = null, $password = null)
    {
        $user = ($user != null) ? $user : 'User1';
        $password = ($password != null) ? $password : '123qwe';
        LoginPage::openBy($this)->login($user, $password);
        $this->see('Dashboard');
    }
    
    public function logout($user = null, $password = null)
    {
        $this->getModule('Yii2')->sendAjaxPostRequest('index-test.php?r=user%2Fauth%2Flogout');
        $this->wait(1);
        LoginPage::openBy($this);
    }
}
