<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\menu\DropdownDivider;
use humhub\widgets\menu\Menu;
use humhub\widgets\menu\MenuLink;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * A menu's entries as data for a client-rendered menu (e.g. the `PageToolbar` `actions`) —
 * {@see Menu::getEntriesData()}.
 *
 * @since 1.20
 */
class MenuEntriesDataTest extends HumHubDbTestCase
{
    private function menu(array $entries): Menu
    {
        $menu = new MenuEntriesDataTestMenu();
        foreach ($entries as $entry) {
            $menu->addEntry($entry);
        }

        return $menu;
    }

    public function testLinkEntriesInSortOrderWithTablerIconsAndTheirOptions()
    {
        $actions = array_column($this->menu([
            new MenuLink([
                'id' => 'export',
                'label' => 'Export',
                'icon' => 'download',
                'url' => '/export',
                'sortOrder' => 200,
                'htmlOptions' => ['target' => '_blank'],
            ]),
            new MenuLink([
                'id' => 'categories',
                'label' => 'Categories',
                'icon' => 'th-list',
                'url' => '/categories',
                'sortOrder' => 50,
                'htmlOptions' => ['data-bs-target' => '#globalModal', 'data-foo' => 'bar'],
            ]),
            new MenuLink([
                'id' => 'create',
                'label' => 'Create',
                'icon' => 'plus',
                'url' => '/create',
                'sortOrder' => 100,
                'htmlOptions' => ['data-action-click' => 'ui.modal.load'],
            ]),
        ])->getEntriesData(), null, 'id');

        $this->assertSame(['categories', 'create', 'export'], array_keys($actions));

        $this->assertSame('layout-list', $actions['categories']['icon'], 'a Font Awesome name is resolved to its Tabler icon');
        $this->assertSame('Categories', $actions['categories']['label']);
        $this->assertTrue($actions['categories']['modal']);
        $this->assertSame(['data-foo' => 'bar'], $actions['categories']['htmlOptions']);

        $this->assertTrue($actions['create']['modal']);
        $this->assertSame('/create', $actions['create']['url']);
        $this->assertArrayNotHasKey('htmlOptions', $actions['create'], 'the modal attributes are the `modal` flag, nothing else is left');

        $this->assertSame('download', $actions['export']['icon']);
        $this->assertFalse($actions['export']['modal']);
        $this->assertSame('/export', $actions['export']['url']);
        $this->assertSame(['target' => '_blank'], $actions['export']['htmlOptions']);
    }

    public function testAModalActionUrlWins()
    {
        $actions = array_column($this->menu([
            new MenuLink([
                'id' => 'import',
                'label' => 'Import',
                'icon' => 'upload',
                'url' => '#',
                'htmlOptions' => ['data-action-click' => 'ui.modal.load', 'data-action-url' => '/import'],
            ]),
        ])->getEntriesData(), null, 'id');

        $this->assertTrue($actions['import']['modal']);
        $this->assertSame('/import', $actions['import']['url']);
    }

    public function testTheVariantComesFromTheButtonClasses()
    {
        $actions = array_column($this->menu([
            new MenuLink(['id' => 'plain', 'label' => 'Plain', 'url' => '/plain']),
            new MenuLink(['id' => 'accent', 'label' => 'Accent', 'url' => '/accent', 'htmlOptions' => ['class' => 'btn-accent']]),
            new MenuLink(['id' => 'primary', 'label' => 'Primary', 'url' => '/primary', 'htmlOptions' => ['class' => 'btn btn-primary btn-sm my-hook']]),
            new MenuLink(['id' => 'unknown', 'label' => 'Unknown', 'url' => '/unknown', 'htmlOptions' => ['class' => 'btn-danger']]),
        ])->getEntriesData(), null, 'id');

        $this->assertSame('secondary', $actions['plain']['variant']);
        $this->assertArrayNotHasKey('htmlOptions', $actions['plain']);

        $this->assertSame('accent', $actions['accent']['variant']);
        $this->assertArrayNotHasKey('htmlOptions', $actions['accent'], 'the button classes become the variant');

        $this->assertSame('primary', $actions['primary']['variant']);
        $this->assertSame(['class' => 'my-hook'], $actions['primary']['htmlOptions'], 'other classes are passed on');

        $this->assertSame('secondary', $actions['unknown']['variant'], 'only the toolbar variants are recognized');
        $this->assertSame(['class' => 'btn-danger'], $actions['unknown']['htmlOptions']);
    }

    public function testHiddenAndNonLinkEntriesAreSkipped()
    {
        $actions = $this->menu([
            new MenuLink(['id' => 'hidden', 'label' => 'Hidden', 'url' => '/hidden', 'isVisible' => false]),
            new DropdownDivider(['sortOrder' => 10]),
            new MenuLink(['id' => 'shown', 'label' => 'Shown', 'url' => '/shown']),
        ])->getEntriesData();

        $this->assertSame(['shown'], array_column($actions, 'id'));
        $this->assertNull($actions[0]['icon']);
    }
}

/**
 * A concrete menu for {@see MenuEntriesDataTest} (the base class is abstract).
 */
class MenuEntriesDataTestMenu extends Menu
{
}
