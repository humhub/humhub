<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PageHeaderActions;
use humhub\widgets\PageHeader;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The page header widget and its actions menu.
 *
 * @since 1.20
 */
class PageHeaderTest extends HumHubDbTestCase
{
    private function actions(array $entries): PageHeaderActions
    {
        $menu = new PageHeaderTestActions();
        foreach ($entries as $entry) {
            $menu->addEntry($entry);
        }

        return $menu;
    }

    public function testRendersTheTitleAsH1LabellingTheSection()
    {
        $html = (string)PageHeader::title('People & more');

        $this->assertMatchesRegularExpression('#<section class="page-header" aria-labelledby="([\w-]+-title)">#', $html);
        preg_match('#aria-labelledby="([\w-]+-title)"#', $html, $matches);
        $this->assertStringContainsString('<h1 id="' . $matches[1] . '" class="page-header-title">People &amp; more</h1>', $html);
        $this->assertStringNotContainsString('page-header-actions', $html);
        $this->assertStringNotContainsString('page-header-body', $html);
        $this->assertStringNotContainsString('page-header-back', $html);
    }

    public function testTheArrayConfigurationRendersTheSame()
    {
        $html = PageHeader::widget(['title' => 'People', 'titleTag' => 'h2']);

        $this->assertMatchesRegularExpression('#<h2 id="[\w-]+-title" class="page-header-title">People</h2>#', $html);
    }

    public function testRendersTheBackArrowBeforeTheTitle()
    {
        $html = (string)PageHeader::title('Users')->backUrl('/admin');

        $this->assertMatchesRegularExpression('#<a class="page-header-back btn btn-ghost btn-icon-only" href="/admin"[^>]*aria-label="Back"#', $html);
        $this->assertLessThan(strpos($html, 'page-header-title'), strpos($html, 'page-header-back'));
    }

    public function testRendersTheToolsThePrimaryActionAndTheMenu()
    {
        $actions = $this->actions([
            new MenuLink(['id' => 'invite', 'label' => 'Invite', 'icon' => 'plus', 'url' => '/invite', 'isPrimary' => true, 'sortOrder' => 10]),
            new MenuLink(['id' => 'settings', 'label' => 'Settings', 'icon' => 'cog', 'url' => '/settings', 'sortOrder' => 20, 'htmlOptions' => ['class' => 'btn-accent']]),
            new MenuLink(['id' => 'hidden', 'label' => 'Hidden', 'icon' => 'eye', 'url' => '/hidden', 'isVisible' => false]),
        ]);
        $menu = new PageHeaderTestMenu();
        $menu->addEntry(new MenuLink(['id' => 'export', 'label' => 'Export', 'icon' => 'download', 'url' => '/export']));

        $html = (string)PageHeader::title('People')->actions($actions)->menu($menu);

        $this->assertStringContainsString('<div class="page-header-actions">', $html);
        $settings = strpos($html, 'href="/settings"');
        $invite = strpos($html, 'href="/invite"');
        $dropdown = strpos($html, 'data-bs-toggle="dropdown"');
        $this->assertNotFalse($settings);
        $this->assertTrue($settings < $invite && $invite < $dropdown, 'tools, then the primary action, then ⋮');

        $this->assertMatchesRegularExpression('#<a class="btn btn-secondary btn-icon-only"[^>]*href="/settings"#', $html, 'a button class of an entry gives way');
        $this->assertMatchesRegularExpression('#<a class="btn btn-accent btn-icon-only"[^>]*href="/invite"[^>]*aria-label="Invite"#', $html);
        $this->assertMatchesRegularExpression('#<button[^>]*class="btn btn-ghost btn-icon-only"[^>]*data-bs-toggle="dropdown"[^>]*aria-label="More actions"#', $html);
        $this->assertMatchesRegularExpression('#<ul class="dropdown-menu dropdown-menu-end">.*href="/export".*</ul>#s', $html);
        $this->assertStringNotContainsString('/hidden', $html);
    }

    public function testShowsOnePrimaryAction()
    {
        $groups = $this->actions([
            new MenuLink(['id' => 'first', 'label' => 'First', 'url' => '/first', 'isPrimary' => true, 'sortOrder' => 10]),
            new MenuLink(['id' => 'second', 'label' => 'Second', 'url' => '/second', 'isPrimary' => true, 'sortOrder' => 20]),
        ])->getToolsAndPrimary();

        $this->assertSame('first', $groups['primary']->getId());
        $this->assertSame(['second'], array_map(static fn(MenuLink $entry) => $entry->getId(), $groups['tools']));
    }

    public function testRendersNoActionsForAnEmptyMenu()
    {
        $html = (string)PageHeader::title('People')->actions($this->actions([]));

        $this->assertStringNotContainsString('page-header-actions', $html);
    }

    public function testRendersTheTabsInTheBody()
    {
        $html = (string)PageHeader::title('People')->tabs(PageHeaderTestTabs::class);

        $this->assertMatchesRegularExpression('#<div class="page-header-body">\s*<nav class="test-tabs">#', $html);
    }
}

/**
 * A concrete actions menu for {@see PageHeaderTest} (the base class is abstract).
 */
class PageHeaderTestActions extends PageHeaderActions
{
}

/**
 * A concrete ⋮ menu for {@see PageHeaderTest} (the base class is abstract).
 */
class PageHeaderTestMenu extends DropdownMenu
{
}

/**
 * A stand-in tab menu for {@see PageHeaderTest}.
 */
class PageHeaderTestTabs extends \humhub\components\Widget
{
    public function run()
    {
        return '<nav class="test-tabs"></nav>';
    }
}
