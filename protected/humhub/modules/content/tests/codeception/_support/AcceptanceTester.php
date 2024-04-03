<?php

namespace content;

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

    public function metaSearch(string $keyword, bool $checkResult = true): void
    {
        $this->fillField(['name' => 'keyword'], $keyword);
        $this->waitForText('Content', null, '.search-provider-title');
        $this->see('People', '.search-provider-title');
        $this->see('Spaces', '.search-provider-title');
        if ($checkResult) {
            $this->waitForText($keyword, null, '.highlight');
        }
    }
}
