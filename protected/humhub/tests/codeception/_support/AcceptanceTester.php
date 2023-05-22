<?php

use tests\codeception\_pages\LoginPage;
use yii\helpers\Url;
use \Facebook\WebDriver\WebDriverKeys;

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
class AcceptanceTester extends BaseTester
{
    use _generated\AcceptanceTesterActions;

    public $guestAccessAllowed = false;

    public function amAdmin($logout = false)
    {
        $this->amUser('Admin', 'test', $logout);
    }

    public function amSpaceAdmin($logout = false, $spaceId = 2)
    {
        switch($spaceId) {
            case 1:
            case 3:
                $this->amAdmin($logout);
                break;
            case 2:
            case 4:
                $this->amUser1($logout);
                break;
        }

        $this->amOnSpace($spaceId);
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
            $guid = $this->getFixtureSpaceGuid(--$guid);
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
        $this->jsClick('#authenticationsettingsform-allowguestaccess');
        $this->click('button.btn-primary', '#authentication-settings-form');
        $this->wait(1);
        $this->guestAccessAllowed = true;
    }

    public function amOnRoute($route) {
        $this->amOnPage(Url::to($route));
    }

    public function createTopics($guid, $topics = [])
    {
        $this->amOnSpace($guid, '/topic/manage');
        $this->waitForText('Topic Overview');

        if(is_string($topics)) {
            $topics = [$topics];
        }

        foreach ($topics as $topic) {
            $this->fillField('#topic-name', $topic);
            $this->click('.input-group-btn .btn-default');
            $this->waitForText($topic, null,'.layout-content-container .table-hover');
        }
    }

    public function createPost($text, $topics = null)
    {
        $this->jsClick('#contentForm_message');
        $this->wait(1);
        $this->fillField('#contentForm_message .humhub-ui-richtext', $text);
        $this->executeJS("$('#contentForm_message').trigger('focusout');");
        $this->wait(1);

        if($topics) {
            $this->click('.dropdown-toggle', '.contentForm_options');
            $this->wait(1);
            $this->click('Topics', '.contentForm_options');
            $this->waitForElementVisible('#postTopicContainer');
            $this->selectFromPicker('#postTopicInput', $topics);
        }

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
        $this->wait(1);
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
        $this->wait(1);
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
        $this->wait(1);
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
        $this->wait(2);
        $this->jsClick('[data-menu-id="account-logout"]');
        if(!$this->guestAccessAllowed) {
            $this->waitForElementVisible('#login-form');
        } else {
            $this->waitForText('Sign in / up');
            $this->wait(1);
        }
    }

    public function enableModule($guid, $moduleId)
    {
        $this->amOnSpace($guid, '/space/manage/module');
        $this->seeElement('.enable-module-'.$moduleId);
        $this->jsClick('.enable-module-'.$moduleId);
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
        $this->amOnPage('/u/user1/user/profile/home');
    }

    public function amOnUser2Profile()
    {
        $this->amOnPage('/u/user2/user/profile/home');
    }

    public function amOnUser3Profile()
    {
        $this->amOnPage('/u/user3/user/profile/home');
    }

    public function seeInNotifications($text, $click = false)
    {
        $this->click('.notifications .fa-bell');
        $this->waitForText('Notifications', 5, '.notifications');
        $this->waitForText($text, 5, '.notifications');

        if($click) {
            $this->click($text,'.notifications');
            $this->wait(2);
        } else {
            $this->click('.notifications .fa-bell');
        }

    }

    /**
     * Selects $userName for a given userPicker. Note this implementation will
     * just take the first result found for the given username.
     *
     * @param $selector
     * @param string $userName
     * @throws Exception
     */
    public function selectUserFromPicker($selector, $userName)
    {
        $this->selectFromPicker($selector, $userName);
    }

    public function selectFromPicker($selector, $search)
    {
        if(is_array($search)) {
            foreach ($search as $searchItem) {
                $this->selectFromPicker($selector, $searchItem);
                $this->wait(1);
            }
        } else {
            $select2Input = $selector . ' ~ span input';
            $this->fillField($select2Input, $search);
            $this->waitForElementVisible('.select2-container--open');
            $this->waitForElementVisible('.select2-results__option.select2-results__option--highlighted');
            $this->see($search, '.select2-container--open');
            $this->wait(1);
            $this->pressKey($select2Input, WebDriverKeys::ENTER);
        }


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
        $this->wait(1);
    }

    public function jsAttr($selector, $attr, $val)
    {
        $this->executeJS('$("' . $selector . '").attr("' . $attr . '", "' . $val . '");');
    }

    public function scrollToTop()
    {
        $this->executeJS('window.scrollTo(0,0);');
    }

    public function scrollToBottom()
    {
        $this->executeJS('window.scrollTo(0,document.body.scrollHeight);');
    }
}
