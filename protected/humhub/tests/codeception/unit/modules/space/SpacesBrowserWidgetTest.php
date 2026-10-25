<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\modules\space\widgets\SpacesBrowser;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Json;

/**
 * The spaces browser page as a `<spaces-browser>` island.
 *
 * @since 1.20
 */
class SpacesBrowserWidgetTest extends HumHubDbTestCase
{
    /**
     * @inheritdoc
     */
    protected $fixtureConfig = ['default'];

    private function props(string $html): array
    {
        $this->assertMatchesRegularExpression('/\sprops="([^"]+)"/', $html, 'the complex props are JSON-encoded into a props attribute');
        preg_match('/\sprops="([^"]+)"/', $html, $matches);

        return Json::decode(html_entity_decode($matches[1], ENT_QUOTES));
    }

    public function testRendersTheIslandWithItsPlaceholder(): void
    {
        $this->becomeUser('Admin');

        $html = SpacesBrowser::widget();

        $this->assertStringContainsString('<spaces-browser', $html);
        $this->assertStringContainsString('</spaces-browser>', $html);
        $this->assertStringContainsString('class="page-header"', $html);
        $this->assertMatchesRegularExpression('#<h1 id="[\w-]+-title" class="page-header-title">Spaces</h1>#', $html);
        $this->assertStringContainsString('title="Create Space"', $html, 'the page header actions stand in the placeholder already');
        $this->assertSame(12, substr_count($html, 'class="card entity-card entity-card-skeleton space-card-skeleton placeholder-glow"'));
        $this->assertStringNotContainsString('card-panel', $html);
    }

    public function testProps(): void
    {
        $this->becomeUser('Admin');

        $props = $this->props(SpacesBrowser::widget());

        $this->assertSame(['q', 'sort', 'scope'], array_column($props['filters'], 'key'));

        $this->assertSame(['create-space-button'], array_column($props['actions'], 'id'));
        $this->assertTrue($props['actions'][0]['modal']);
        $this->assertTrue($props['actions'][0]['primary']);

        $this->assertSame('btn btn-primary', $props['buttons']['buttonClass']);
        $this->assertSame('btn btn-light', $props['buttons']['memberClass']);
        $this->assertSame('btn btn-accent', $props['buttons']['followClass']);
        $this->assertSame('btn btn-light', $props['buttons']['followingClass']);

        $this->assertStringContainsString('ti-check', $props['icons']['check']);
        $this->assertArrayHasKey('clock', $props['icons']);
        $this->assertArrayHasKey('user', $props['icons']);
    }

    public function testNoCreateActionWithoutThePermission(): void
    {
        $this->becomeUser('User3');

        $html = SpacesBrowser::widget();

        $this->assertSame([], array_column($this->props($html)['actions'], 'id'));
        $this->assertStringNotContainsString('title="Create Space"', $html);
        $this->assertStringNotContainsString('page-header-actions', $html);
    }
}
