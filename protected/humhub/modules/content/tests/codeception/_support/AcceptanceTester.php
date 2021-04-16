<?php
namespace content;

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

   public function filterStreamArchived()
   {
       $this->jsClick('.wall-stream-filter-toggle');
       $this->jsClick('[data-filter-id=entry_archived]');
   }

   public function seeArchivedContents($archivedContents = [], $notArchivedContents = [])
   {
       $this->wait(2);

       foreach ($archivedContents as $archivedContent) {
           $this->see($archivedContent);
       }

       foreach ($notArchivedContents as $notArchivedContent) {
           $this->dontSee($notArchivedContent);
       }
   }

    public function dontSeeArchivedContents($archivedContents = [], $notArchivedContents = [])
    {
        $this->wait(2);

        foreach ($archivedContents as $archivedContent) {
            $this->dontSee($archivedContent);
        }

        foreach ($notArchivedContents as $notArchivedContent) {
            $this->see($notArchivedContent);
        }
    }
}
