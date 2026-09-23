<?php

namespace dashboard\acceptance;

use dashboard\AcceptanceTester;

/**
 * The core renders its icons with Tabler only; `fa` classes would mean raw Font Awesome markup
 * slipped back in and is only kept alive by the compatibility layer.
 */
class NoLegacyIconMarkupCest
{
    public function testCorePagesRenderNoFontAwesomeMarkup(AcceptanceTester $I)
    {
        $I->wantTo('ensure the core pages render icons with Tabler only');
        $I->amAdmin();

        $I->amOnDashboard();
        $I->seeElement('.ti');
        $I->dontSeeElement('.fa');

        $I->amOnSpace1();
        $I->wait(1);
        $I->seeElement('.ti');
        $I->dontSeeElement('.fa');

        $I->amOnDirectory();
        $I->wait(1);
        $I->seeElement('.ti');
        $I->dontSeeElement('.fa');

        $I->amOnPage('/admin');
        $I->wait(1);
        $I->seeElement('.ti');
        $I->dontSeeElement('.fa');
    }
}
