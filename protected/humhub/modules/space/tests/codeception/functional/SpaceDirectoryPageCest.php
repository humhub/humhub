<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\tests\codeception\functional;

use FunctionalTester;

/**
 * `/spaces` renders the `SpaceDirectory` island; the cards load from the HTTP API.
 */
class SpaceDirectoryPageCest
{
    public function testRendersTheIsland(FunctionalTester $I)
    {
        $I->wantTo('see the spaces directory island with its placeholder');
        $I->amUser1();

        $I->amOnRoute('/space/spaces');
        $I->seeResponseCodeIs(200);
        $I->seeElement('space-directory');
        $I->seeElement('space-directory .c-page-toolbar');
        $I->see('Spaces', 'space-directory .c-page-toolbar__title');
        $I->seeElement('space-directory .c-space-card-skeleton');
        $I->dontSeeElement('.card-panel');
        $I->dontSeeElement('.cards-end');
    }

    public function testTheLoadMoreRouteIsGone(FunctionalTester $I)
    {
        $I->wantTo('see that the cards are no longer rendered by the server');
        $I->amUser1();

        $I->amOnRoute('/space/spaces/load-more');
        $I->seeResponseCodeIs(404);
    }
}
