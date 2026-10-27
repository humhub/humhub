<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\tests\codeception\functional;

use FunctionalTester;

/**
 * `/spaces` renders the `SpacesBrowser` island; the cards load from the HTTP API.
 */
class SpacesBrowserPageCest
{
    public function testRendersTheIsland(FunctionalTester $I)
    {
        $I->wantTo('see the spaces browser island with its placeholder');
        $I->amUser1();

        $I->amOnRoute('/space/spaces');
        $I->seeResponseCodeIs(200);
        $I->seeElement('spaces-browser');
        $I->seeElement('spaces-browser .page-header');
        $I->see('Spaces', 'spaces-browser .page-header-title');
        $I->seeElement('spaces-browser .space-card-skeleton');
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
