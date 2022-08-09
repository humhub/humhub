<?php
namespace stream;

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

    public function fillDateFilter($field, $value) {
        $this->fillField('[data-filter-id=' . $field . ']', $value);
        // Focus out the filled date input in order to run the filtering:
        $this->click('.filter-block strong');
    }
}
