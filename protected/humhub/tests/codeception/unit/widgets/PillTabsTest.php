<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use humhub\widgets\PageHeader;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Link tabs as Bootstrap nav pills.
 *
 * @since 1.20
 */
class PillTabsTest extends HumHubDbTestCase
{
    private function tabs(array $config = []): PillTabs
    {
        $tabs = new PillTabsTestTabs($config);
        $tabs->addEntry(new MenuLink(['id' => 'users', 'label' => 'Users', 'url' => '/users', 'isActive' => true, 'sortOrder' => 10]));
        $tabs->addEntry(new MenuLink(['id' => 'groups', 'label' => 'Groups', 'url' => '/groups', 'sortOrder' => 20]));

        return $tabs;
    }

    public function testRendersANavOfPills()
    {
        $html = $this->tabs(['label' => '<strong>People</strong>'])->run();

        $this->assertMatchesRegularExpression('#<nav id="[\w-]+" class="nav-scroller" aria-label="People"[^>]*>#', $html);
        $this->assertStringContainsString('<ul class="nav nav-pills">', $html);
        $this->assertMatchesRegularExpression('#<a[^>]*class="active nav-link"[^>]*href="/users"[^>]*aria-current="page"#', $html);
        $this->assertMatchesRegularExpression('#<a[^>]*class="nav-link"[^>]*href="/groups"#', $html);
        $this->assertDoesNotMatchRegularExpression('#href="/groups"[^>]*aria-current#', $html);
    }

    public function testTheSecondaryVariant()
    {
        $html = $this->tabs(['variant' => PillTabs::VARIANT_SECONDARY])->run();

        $this->assertStringContainsString('<ul class="nav nav-pills nav-pills-secondary">', $html);
    }

    public function testASingleTabIsNotRenderedUnlessAsked()
    {
        $tabs = new PillTabsTestTabs();
        $tabs->addEntry(new MenuLink(['id' => 'users', 'label' => 'Users', 'url' => '/users']));
        $this->assertSame('', $tabs->run());

        $tabs = new PillTabsTestTabs(['renderSingleTab' => true]);
        $tabs->addEntry(new MenuLink(['id' => 'users', 'label' => 'Users', 'url' => '/users']));
        $this->assertStringContainsString('nav-pills', $tabs->run());
    }

    public function testThePageHeaderPutsTheTabsIntoItsBodyLabelledByTheTitle()
    {
        $html = (string)PageHeader::title('People')->tabs($this->tabs());

        $this->assertMatchesRegularExpression('#<div class="page-header-body">\s*<nav [^>]*class="nav-scroller" aria-label="People"#', $html);
    }
}

/**
 * A concrete tab menu for {@see PillTabsTest} (the base class is abstract).
 */
class PillTabsTestTabs extends PillTabs
{
}
