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

    public function amUser3($logout = false)
    {
        $this->amUser('User3', '123qwe', $logout);
    }

    public function amOnSpace1()
    {
        $this->amOnPage('index-test.php?r=space/space&sguid=5396d499-20d6-4233-800b-c6c86e5fa34a');
    }

    public function amOnSpace2()
    {
        $this->amOnPage('index-test.php?r=space/space&sguid=5396d499-20d6-4233-800b-c6c86e5fa34b');
    }

    public function amOnSpace3()
    {
        $this->amOnPage('index-test.php?r=space/space&sguid=5396d499-20d6-4233-800b-c6c86e5fa34c');
    }

    public function amOnSpace4()
    {
        $this->amOnPage('index-test.php?r=space/space&sguid=5396d499-20d6-4233-800b-c6c86e5fa34d');
    }

    public function createPost($text)
    {
        $this->jsClick('#contentForm_message');
        $this->wait(1);
        $this->fillField('#contentForm_message', $text);
        $this->executeJS("$('#contentForm_message').trigger('focusout');");
        $this->wait(1);
        $this->jsClick('#post_submit_button');
        $this->waitForText($text, 30, '.wall-entry');
    }

    public function amOnDashboard()
    {
        tests\codeception\_pages\DashboardPage::openBy($this);
    }

    public function seeSuccess($text = null)
    {
        $this->waitForElementVisible('#status-bar .success', 30);
        $this->waitForElementVisible('#status-bar .status-bar-close');

        if ($text) {
            $this->see($text, '#status-bar');
        }

        $this->click('#status-bar .status-bar-close');
        $this->waitForElementNotVisible('#status-bar');
    }

    public function seeWarning($text = null)
    {
        $this->waitForElementVisible('#status-bar .warning', 20);
        $this->waitForElementVisible('#status-bar .status-bar-close');

        if ($text) {
            $this->see($text, '#status-bar');
        }

        $this->waitForElementVisible('#status-bar .status-bar-close');
        $this->click('#status-bar .status-bar-close');
        $this->waitForElementNotVisible('#status-bar');
    }

    public function seeError($text = null)
    {
        $this->waitForElementVisible('#status-bar .error', 20);
        $this->waitForElementVisible('#status-bar .status-bar-close');

        if ($text) {
            $this->see($text, '#status-bar');
        }
        $this->waitForElementVisible('#status-bar .status-bar-close');
        $this->click('#status-bar .status-bar-close');
        $this->waitForElementNotVisible('#status-bar');
    }

    public function seeInfo($text = null)
    {
        $this->waitForElementVisible('#status-bar .info', 20);
        $this->waitForElementVisible('#status-bar .status-bar-close');

        if ($text) {
            $this->see($text, '#status-bar');
        }

        $this->waitForElementVisible('#status-bar .status-bar-close');
        $this->click('#status-bar .status-bar-close');
        $this->waitForElementNotVisible('#status-bar');
    }

    public function amUser($user = null, $password = null, $logout = false)
    {
        if ($logout) {
            $this->logout();
        }

        $user = ($user != null) ? $user : 'User1';
        $password = ($password != null) ? $password : '123qwe';
        $this->login($user, $password);
        $this->waitForElementVisible('#wallStream', 30);
    }

    public function login($user, $password)
    {
        LoginPage::openBy($this)->login($user, $password);
    }

    public function logout()
    {
        $this->clickAccountDropDown();
        $this->click('Logout');
        $this->wait(5);
    }

    public function clickAccountDropDown()
    {
        $this->jsClick('#account-dropdown-link');
        $this->wait(2);
    }

    public function amOnDirectory()
    {
        return tests\codeception\_pages\DirectoryPage::openBy($this);
    }

    public function amOnProfile()
    {
        return tests\codeception\_pages\ProfilePage::openBy($this);
    }

    public function amOnUser1Profile()
    {
        $this->amOnPage('index-test.php?r=user/profile&uguid=01e50e0d-82cd-41fc-8b0c-552392f5839d');
    }

    public function amOnUser2Profile()
    {
        $this->amOnPage('index-test.php?r=user/profile&uguid=01e50e0d-82cd-41fc-8b0c-552392f5839e');
    }

    public function amOnUser3Profile()
    {
        $this->amOnPage('index-test.php?r=user/profile&uguid=01e50e0d-82cd-41fc-8b0c-552392f5839a');
    }

    public function seeInNotifications($text)
    {
        $this->click('.notifications .fa-bell');
        $this->waitForText('Notifications', 5, '.notifications');
        $this->waitForText($text, 5, '.notifications');
        $this->click('.notifications');
    }

    /**
     * Selects $userName for a given userPicker. Note this implementation will
     * just take the first result found for the given username.
     * 
     * @param type $id
     * @param type $userName
     */
    public function selectUserFromPicker($selector, $userName)
    {
        $select2Input = $selector . ' ~ span input';
        $this->fillField($select2Input, $userName);
        $this->waitForElementVisible('.select2-container--open');
        $this->wait(5);
        $this->see($userName, '.select2-container--open');
        $this->pressKey($select2Input, WebDriverKeys::ENTER);
    }

    public function dontSeeInNotifications($text)
    {
        $this->click('.notifications');
        $this->wait(1);
        $this->dontSee($text);
        $this->click('.notifications');
    }

    public function scrollTop()
    {
        $this->executeJS('window.scrollTo(0,0);');
        $this->wait(1);
    }

    public function jsClick($selector)
    {
        $this->executeJS("$('" . $selector . "')[0].click();");
    }

    public function jsFillField($selector, $value)
    {
        $this->executeJS('$("' . $selector . '").val("' . $value . '");');
    }

    public function jsShow($selector)
    {
        $this->executeJS('$("' . $selector . '").show();');
    }

    public function jsAttr($selector, $attr, $val)
    {
        $this->executeJS('$("' . $selector . '").attr("' . $attr . '", "' . $val . '");');
    }

    public function scrollToTop()
    {
        $this->executeJS('window.scrollTo(0,0);');
    }

}
