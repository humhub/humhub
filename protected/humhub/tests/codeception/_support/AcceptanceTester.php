<?php

use tests\codeception\_pages\LoginPage;
use yii\helpers\Url;

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

    public $guestAccessAllowed = false;

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

    public $spaces = [
        '5396d499-20d6-4233-800b-c6c86e5fa34a',
        '5396d499-20d6-4233-800b-c6c86e5fa34b',
        '5396d499-20d6-4233-800b-c6c86e5fa34c',
        '5396d499-20d6-4233-800b-c6c86e5fa34d',
    ];

    public function amOnSpace1($path = '/space/space', $params = [])
    {
        $this->amOnSpace(1, $path, $params);
    }

    public function amOnSpace2($path = '/space/space', $params = [])
    {
        $this->amOnSpace(2, $path, $params);
    }

    public function amOnSpace3($path = '/space/space', $params = [])
    {
        $this->amOnSpace(3, $path, $params);
    }

    public function amOnSpace4($path = '/space/space', $params = [])
    {
        $this->amOnSpace(4, $path, $params);
    }

    public function amOnSpace($guid, $path = '/space/space', $params = [])
    {
        if(!$path) {
            $path = '/space/space';
        }

        if(is_int($guid)) {
            $guid = $this->spaces[--$guid];
        }

        $params['sguid'] = $guid;
        $params[0] = $path;

        $this->amOnRoute($params);
    }

    public function dontSeeInDropDown($selector, $text) {
        $this->click($selector);
        $this->wait(1);
        $this->dontSee($text, $selector);
        $this->click($selector);
    }

    public function seeInDropDown($selector, $text) {
        $this->click($selector);
        $this->wait(1);
        $this->see($text, $selector);
        $this->click($selector);
    }

    public function allowGuestAccess() {
        $this->amOnRoute(['/admin/authentication']);
        $this->waitForElementVisible('.field-authenticationsettingsform-allowguestaccess');
        $this->click('.field-authenticationsettingsform-allowguestaccess label');

        $this->click('[type="submit"]');
        $this->seeSuccess('Saved');
        $this->guestAccessAllowed = true;
    }

    public function amOnRoute($route) {
        $this->amOnPage(Url::to($route));
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

        $this->jsClick('.status-bar-close');
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
        if(!$this->guestAccessAllowed) {
            $this->waitForElementVisible('#login-form');
        } else {
            $this->waitForElementVisible('.btn-enter');
        }
    }

    public function enableModule($guid, $moduleId)
    {
        $this->amOnSpace($guid, '/space/manage/module');
        $this->seeElement('.enable-module-'.$moduleId);
        $this->click('.enable-module-'.$moduleId);
        $this->waitForElement('.disable-module-'.$moduleId);
        $this->amOnSpace($guid);
    }

    public function clickAccountDropDown()
    {
        $this->jsClick('#account-dropdown-link');
        $this->waitForElementVisible('.account.open');
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
        $this->wait(3);
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

    /**
     * @return \Codeception\Scenario
     */
    /*protected function getScenario()
    {
        // TODO: Implement getScenario() method.
    }*/
}
