<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\modules\space\widgets\SpaceDirectory;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Json;

/**
 * The spaces directory page as a `<space-directory>` island.
 *
 * @since 1.20
 */
class SpaceDirectoryWidgetTest extends HumHubDbTestCase
{
    private function props(string $html): array
    {
        $this->assertMatchesRegularExpression('/\sprops="([^"]+)"/', $html, 'the complex props are JSON-encoded into a props attribute');
        preg_match('/\sprops="([^"]+)"/', $html, $matches);

        return Json::decode(html_entity_decode($matches[1], ENT_QUOTES));
    }

    public function testRendersTheIslandWithItsPlaceholder(): void
    {
        $this->becomeUser('Admin');

        $html = SpaceDirectory::widget();

        $this->assertStringContainsString('<space-directory', $html);
        $this->assertStringContainsString('</space-directory>', $html);
        $this->assertStringContainsString('class="c-page-toolbar"', $html);
        $this->assertStringContainsString('<h1 id="space-directory-placeholder-title" class="c-page-toolbar__title">Spaces</h1>', $html);
        $this->assertStringContainsString('title="Create Space"', $html, 'the toolbar actions stand in the placeholder already');
        $this->assertSame(12, substr_count($html, 'class="c-card-skeleton c-space-card-skeleton"'));
        $this->assertStringNotContainsString('card-panel', $html);
    }

    public function testProps(): void
    {
        $this->becomeUser('Admin');

        $props = $this->props(SpaceDirectory::widget());

        $this->assertSame(['q', 'sort', 'scope'], array_column($props['filters'], 'key'));

        $this->assertSame(['create-space-button'], array_column($props['actions'], 'id'));
        $this->assertTrue($props['actions'][0]['modal']);
        $this->assertSame('accent', $props['actions'][0]['variant']);

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

        $html = SpaceDirectory::widget();

        $this->assertSame([], array_column($this->props($html)['actions'], 'id'));
        $this->assertStringNotContainsString('title="Create Space"', $html);
        $this->assertStringNotContainsString('c-page-toolbar__actions', $html);
    }
}
