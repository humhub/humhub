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
        \Yii::$app->user->logout(true);
    }

}
