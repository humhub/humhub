<?php
namespace user;

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
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \AcceptanceTester
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */

    public function impersonateUser($userName)
    {
        $this->clickAccountDropDown();
        $this->click('Administration');
        $this->expectTo('see the users list');

        $user = User::findOne(['username' => $userName]);
        $this->waitForText('User administration');
        $this->jsClick('tr[data-key=' . $user->id . '] div.dropdown-navigation button');
        $this->waitForText('Impersonate');
        $this->click('Impersonate', '.dropdown-navigation.open');
        $this->acceptPopup();
    }

    public function stopImpersonation()
    {
        $this->clickAccountDropDown();
        $this->click('Stop impersonation');
    }
}
