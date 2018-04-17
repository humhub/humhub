<?php

use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\models\Space;
use tests\codeception\_pages\LoginPage;
use humhub\modules\user\models\User;

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

    public function amAdmin($logout = false)
    {
        if($logout) {
            $this->logout();
        }

        LoginPage::openBy($this)->login('admin', 'test');
        $this->see('Dashboard');
        $this->see('Administration');
    }

    function setGroupPermission($groupId, $permission, $state = 1)
    {
        $groupPermission = new humhub\modules\user\models\GroupPermission();
        $groupPermission->permission_id = $permission->id;
        $groupPermission->group_id = $groupId;
        $groupPermission->module_id = $permission->moduleId;
        $groupPermission->class = $permission->className();
        $groupPermission->state = $state;
        $groupPermission->save();
        \Yii::$app->user->getPermissionManager()->clear();
    }

    public function amUser($user = null, $password = null, $logout = false)
    {
        if ($logout) {
            $this->logout();
        }

        if ($user == null) {
            $this->amUser1();
        } else {
            LoginPage::openBy($this)->login($user, $password);
            tests\codeception\_pages\DashboardPage::openBy($this);
            $this->see('Dashboard');
        }
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

    public function logout()
    {
        $this->amGoingTo('logout');
        \Yii::$app->user->logout(true);
    }

    public function enableFriendships($enable = true)
    {
        Yii::$app->getModule('friendship')->settings->set('enable', $enable);
    }

    public function switchIdentity($username)
    {
        Yii::$app->user->switchIdentity(User::findOne(['username' => $username]));
    }

    public function amFriendWith($username)
    {
        $user = User::findOne(['username' => $username]);
        Friendship::add($user, Yii::$app->user->identity);
        Friendship::add(Yii::$app->user->identity, $user);
    }

    public function follow($username)
    {
        User::findOne(['username' => $username])->follow();
    }

    public function setProfileField($field, $value)
    {
        $output = new \Codeception\Lib\Console\Output([]);
        $output->writeln("Set attribute $field : $value");

        $user = Yii::$app->user->identity;
        $user->profile->setAttributes([$field => $value]);
        $user->profile->save();
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

    public $spaces = [
        '5396d499-20d6-4233-800b-c6c86e5fa34a',
        '5396d499-20d6-4233-800b-c6c86e5fa34b',
        '5396d499-20d6-4233-800b-c6c86e5fa34c',
        '5396d499-20d6-4233-800b-c6c86e5fa34d',
    ];

    public function amOnSpace($guid, $path = '/space/space', $params = [])
    {
        if(!$path) {
            $path = '/space/space';
        }

        if(is_int($guid)) {
            $guid = $this->spaces[--$guid];
        }

        $params['sguid'] = $guid;

        $this->amOnRoute($path, $params);
    }

    public function enableModule($guid, $moduleId)
    {
        if(is_int($guid)) {
            $guid = $this->spaces[--$guid];
        }

        $space = Space::findOne(['guid' => $guid]);
        $space->enableModule($moduleId);
        Yii::$app->moduleManager->flushCache();
        \humhub\modules\space\models\Module::flushCache();
    }

}
