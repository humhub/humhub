<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\menu\MenuLink;
use humhub\widgets\SidebarCard;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The card of a sidebar widget, its ⋮ menu and its collapse option.
 *
 * @since 1.20
 */
class SidebarCardTest extends HumHubDbTestCase
{
    private function render(array $config, string $content = '<p class="content">Hello</p>'): string
    {
        ob_start();
        SidebarCard::begin($config);
        echo $content;
        SidebarCard::end();

        return ob_get_clean();
    }

    public function testRendersASectionLabelledByItsH2Title()
    {
        $html = $this->render(['title' => 'Members & guests', 'options' => ['id' => 'members-card']]);

        $this->assertMatchesRegularExpression('#<section id="members-card" class="card content-card sidebar-card" aria-labelledby="([\w-]+-title)"#', $html);
        preg_match('#aria-labelledby="([\w-]+-title)"#', $html, $matches);
        $this->assertStringContainsString('<h2 id="' . $matches[1] . '" class="card-title">Members &amp; guests</h2>', $html);
        $this->assertMatchesRegularExpression('#<div id="([\w-]+)" class="card-body collapse show">\s*<p class="content">Hello</p>\s*</div>#', $html);
    }

    public function testATranslationWithEmphasisIsNotEncodedOnRequest()
    {
        $html = $this->render(['title' => '<strong>Space</strong> members', 'encodeTitle' => false]);

        $this->assertStringContainsString('class="card-title"><strong>Space</strong> members</h2>', $html);
    }

    public function testTheMenuEndsWithTheCollapseEntryForTheBody()
    {
        $html = $this->render([
            'title' => 'Members',
            'collapseId' => 'members-body',
            'menu' => [new MenuLink(['label' => 'Show as List', 'url' => '/list'])],
        ]);

        $this->assertStringContainsString('data-ui-widget="ui.sidebarCard.SidebarCard"', $html);
        $this->assertStringContainsString('<div id="members-body" class="card-body collapse show">', $html);
        $this->assertMatchesRegularExpression('#<div class="card-header-actions"><div [^>]*class="[^"]*dropdown#', $html);
        $list = strpos($html, 'Show as List');
        $collapse = strpos($html, 'sidebar-card-collapse');
        $this->assertNotFalse($list);
        $this->assertGreaterThan($list, $collapse);
        $this->assertMatchesRegularExpression('#<a [^>]*href="\#members-body"[^>]*>#', $html);
        $this->assertStringContainsString('aria-controls="members-body"', $html);
        $this->assertStringContainsString('data-bs-toggle="collapse"', $html);
    }

    public function testACardThatCannotCollapseHasNoMenuWithoutEntries()
    {
        $html = $this->render(['title' => 'Warning', 'collapsible' => false]);

        $this->assertStringNotContainsString('card-header-actions', $html);
        $this->assertStringNotContainsString('data-ui-widget', $html);
        $this->assertMatchesRegularExpression('#<div class="card-body">\s*<p class="content">Hello</p>#', $html);
    }

    public function testATitleActionIsAButtonAroundTheTitle()
    {
        $html = $this->render(['title' => 'Members', 'titleAction' => ['data-action-click' => 'ui.modal.load']]);

        $this->assertMatchesRegularExpression('#<h2 [^>]*class="card-title"><button type="button" class="card-title-action" data-action-click="ui.modal.load">Members</button></h2>#', $html);
    }
}
