<?php

namespace admin;

use Codeception\Lib\Friend;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use Yii;

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
 * @method Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \FunctionalTester
{
    use _generated\FunctionalTesterActions;

    /**
     * Define custom actions here
     */

    public function loginUserWithoutGroupManagerPermission(): User
    {
        $user = User::findOne(['username' => 'User2']);
        GroupUser::updateAll(['is_group_manager' => 0], ['user_id' => $user->id]);

        $this->amUser2();

        return $user;
    }
}
