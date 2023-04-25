<?php
namespace tour;

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

    public function checkOptionShowTour()
    {
        $this->wait(1);
        $this->amOnRoute(['/admin/setting/basic']);

        $this->see('Show introduction tour for new users');
        $this->click('.field-basicsettingsform-tour label');
        // $this->checkOption('#basicsettingsform-tour');

        $this->click('Save');
        $this->seeSuccess();
    }
}
