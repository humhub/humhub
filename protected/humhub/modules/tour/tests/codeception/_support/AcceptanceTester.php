<?php

namespace tour;

use Codeception\Lib\Friend;

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
class AcceptanceTester extends \AcceptanceTester
{
    use _generated\AcceptanceTesterActions;

    public function checkOptionShowTour()
    {
        $this->wait(1);
        $this->amOnRoute(['/admin/setting/basic']);

        $this->seeElement('.form-collapsible-fields');
        $this->click('.form-collapsible-fields label');
        $this->see('Show introduction tour for new users');
        $this->click('.field-basicsettingsform-tour label');

        $this->click('Save');
        $this->seeSuccess();
    }
}
