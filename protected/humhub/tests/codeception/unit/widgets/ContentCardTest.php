<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\ContentCard;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The content card widget and its actions menu.
 *
 * @since 1.20
 */
class ContentCardTest extends HumHubDbTestCase
{
    private function render(array $config, string $content = '<p class="content">Hello</p>'): string
    {
        ob_start();
        ContentCard::begin($config);
        echo $content;
        ContentCard::end();

        return ob_get_clean();
    }

    public function testRendersASectionLabelledByItsH2Title()
    {
        $html = $this->render(['title' => 'Users & groups']);

        $this->assertMatchesRegularExpression('#<section class="card content-card" aria-labelledby="([\w-]+-title)">#', $html);
        preg_match('#aria-labelledby="([\w-]+-title)"#', $html, $matches);
        $this->assertStringContainsString('<h2 id="' . $matches[1] . '" class="card-title">Users &amp; groups</h2>', $html);
        $this->assertMatchesRegularExpression('#<div class="card-body">\s*<p class="content">Hello</p>\s*</div>#', $html);
        $this->assertStringNotContainsString('card-header-actions', $html);
        $this->assertStringNotContainsString('card-footer', $html);
    }

    public function testRendersTheDescriptionAndSecondaryTabs()
    {
        $tabs = new ContentCardTestTabs();
        $tabs->addEntry(new MenuLink(['label' => 'General', 'url' => '/general', 'isActive' => true, 'sortOrder' => 10]));
        $tabs->addEntry(new MenuLink(['label' => 'LDAP', 'url' => '/ldap', 'sortOrder' => 20]));

        $html = $this->render(['title' => 'User Settings', 'description' => 'How people sign up.', 'tabs' => $tabs]);

        $this->assertMatchesRegularExpression('#<div class="card-heading">\s*<h2 [^>]*class="card-title">User Settings</h2>\s*<p class="card-subtitle">How people sign up.</p>#', $html);
        $this->assertMatchesRegularExpression('#<div class="card-tabs"><nav [^>]*class="nav-scroller" aria-label="User Settings">#', $html);
        $this->assertSame(PillTabs::VARIANT_SECONDARY, $tabs->variant);
        $this->assertLessThan(strpos($html, 'card-body'), strpos($html, 'card-tabs'));
    }

    public function testTellsTheLayoutThatTheViewBringsItsOwnCard()
    {
        $view = Yii::$app->view;
        unset($view->params[ContentCard::class]);
        $this->assertFalse(ContentCard::isRendered($view));

        $this->render(['title' => 'Users']);

        $this->assertTrue(ContentCard::isRendered($view));
    }

    public function testRendersTheBackArrowOfASubPageBeforeTheTitle()
    {
        $html = $this->render(['title' => 'Edit user', 'backUrl' => '/admin/user', 'backLabel' => 'Back to overview']);

        $this->assertMatchesRegularExpression('#<a class="card-back btn btn-ghost btn-icon-only" href="/admin/user" title="Back to overview" aria-label="Back to overview">#', $html);
        $this->assertLessThan(strpos($html, 'card-title'), strpos($html, 'card-back'));
    }

    public function testRendersBadgesNextToTheTitle()
    {
        $html = $this->render(['title' => 'Administrators', 'badges' => '<span class="badge">Admin</span>']);

        $this->assertMatchesRegularExpression('#<div class="card-title-line">\s*<h2 [^>]*>Administrators</h2>\s*<span class="badge">Admin</span>#', $html);
    }

    public function testIsAPlainDivWithoutTitleAndActions()
    {
        $html = $this->render(['footer' => '<span class="pager"></span>']);

        $this->assertMatchesRegularExpression('#^\s*<div class="card content-card">#', $html);
        $this->assertStringNotContainsString('card-header', $html);
        $this->assertStringContainsString('<div class="card-footer"><span class="pager"></span></div>', $html);
    }

    public function testRendersTheActionsWithGhostTools()
    {
        $actions = new ContentCardTestActions();
        $actions->addEntry(new MenuLink(['id' => 'add', 'label' => 'Add user', 'icon' => 'plus', 'url' => '/add', 'isPrimary' => true, 'sortOrder' => 10]));
        $actions->addEntry(new MenuLink(['id' => 'columns', 'label' => 'Columns', 'icon' => 'columns', 'url' => '/columns', 'sortOrder' => 20]));

        $html = $this->render(['title' => 'Users', 'actions' => $actions]);

        $this->assertStringContainsString('<div class="card-header-actions">', $html);
        $this->assertMatchesRegularExpression('#<a class="btn btn-ghost btn-icon-only"[^>]*href="/columns"#', $html);
        $this->assertMatchesRegularExpression('#<a class="btn btn-accent btn-icon-only"[^>]*href="/add"#', $html);
        $this->assertLessThan(strpos($html, 'href="/add"'), strpos($html, 'href="/columns"'));
    }
}

/**
 * A concrete actions menu for {@see ContentCardTest} (the base class is abstract).
 */
class ContentCardTestActions extends CardHeaderActions
{
}

/**
 * Concrete tabs for {@see ContentCardTest}.
 */
class ContentCardTestTabs extends PillTabs
{
}
