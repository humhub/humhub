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
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function amAdmin($logout = false)
    {
        $this->amUser('Admin', 'test', $logout);
    }
    
    public function amUser1($logout = false)
    {
        $this->amUser('User1', '123qwe', $logout);
    }
    
    public function amUser2($logout = false)
    {
        $this->amUser('User2', '123qwe', $logout);
    }
    
    public function amUser($user = null, $password = null, $logout = false)
    {
        if($logout) {
            $this->logout();
        }
        
        $user = ($user != null) ? $user : 'User1';
        $password = ($password != null) ? $password : '123qwe';
        $this->login($user, $password);
        $this->wait(10);
        $this->seeElement('#wallStream');
    }
    
    public function login($user, $password)
    {
        LoginPage::openBy($this)->login($user, $password);
    }
    
    public function logout()
    {
        $this->clickAccountDropDown();
        $this->click('Logout');
        $this->wait(10);
    }
    
    public function clickAccountDropDown()
    {
        $this->click('#account-dropdown-link');
        $this->wait(2);
    }
    
    public function amOnProfile()
    {
        $this->clickAccountDropDown();
        $this->click('My profile');
    }
    
    public function jsClick($selector)
    {
        $this->executeJS('$("'.$selector.'").click();');
    }
    
    public function scrollToTop()
    {
        $this->executeJS('window.scrollTo(0,0);');
    }
}
