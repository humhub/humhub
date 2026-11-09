<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace admin\functional;

use admin\FunctionalTester;
use humhub\modules\marketplace\Module as MarketplaceModule;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The administration's section menu: a card with the title and one entry per section
 */
class AdminMenuCest
{
    public function testSectionMenu(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/user');

        $I->seeElement('nav#admin-menu.card.section-menu[aria-labelledby="admin-menu-title"]');
        $I->see('Administration', '#admin-menu-title.section-menu-title');
        $I->dontSeeElement('#admin-menu .section-menu-title strong');
        $I->dontSee('Administration menu', '#admin-menu');
        $I->seeElement('#admin-menu .nav.flex-lg-column.nav-section > .nav-item > .nav-link.active[data-menu-id="user"][aria-current="page"]');
        $I->dontSeeElement('#admin-menu .list-group-item');

        $labels = array_map('trim', $I->grabMultiple('#admin-menu .nav-section .nav-link'));
        Assert::assertSame('Users', $labels[0]);
        Assert::assertContains('Modules', $labels);
    }

    public function testPendingModuleUpdatesShowTheWaitingDot(FunctionalTester $I)
    {
        /* @var MarketplaceModule $marketplace */
        $marketplace = Yii::$app->getModule('marketplace');
        $marketplace->settings->set('pendingModuleUpdateCount', 3);

        $I->amAdmin();
        $I->amOnRoute('/admin/user');
        $I->see('3 waiting', '#admin-menu .nav-link.modules .badge-dot-waiting');
        $I->dontSeeElement('#admin-menu .nav-link.modules .badge');

        $marketplace->settings->set('pendingModuleUpdateCount', 0);
        $I->amOnRoute('/admin/user');
        $I->dontSeeElement('#admin-menu .nav-link.modules .badge-dot');
    }
}
