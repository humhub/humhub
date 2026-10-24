<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\marketplace;

use humhub\modules\marketplace\widgets\MarketplaceBrowser;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Json;

class MarketplaceBrowserWidgetTest extends HumHubDbTestCase
{
    public function testRendersTheIslandWithItsPlaceholderAndFilterProps(): void
    {
        $html = MarketplaceBrowser::widget();

        $this->assertStringContainsString('<marketplace-browser', $html);
        $this->assertStringContainsString('</marketplace-browser>', $html);
        $this->assertStringContainsString('Marketplace', $html);
        $this->assertStringContainsString('class="c-page-toolbar"', $html);
        $this->assertStringContainsString('class="c-page-toolbar__title"', $html);
        $this->assertStringContainsString('c-card-grid__cell c-card-grid__cell--skeleton', $html);
        $this->assertStringContainsString('class="c-card-skeleton"', $html);
        $this->assertStringNotContainsString('Find all the modules', $html);

        $this->assertMatchesRegularExpression('/\sprops="([^"]+)"/', $html, 'the complex props are JSON-encoded into a props attribute');
        preg_match('/\sprops="([^"]+)"/', $html, $matches);
        $props = Json::decode(html_entity_decode($matches[1], ENT_QUOTES));

        $this->assertArrayHasKey('filters', $props);
        $this->assertNotEmpty($props['filters']);
        $this->assertContains('q', array_column($props['filters'], 'key'));
        $this->assertContains('categoryId', array_column($props['filters'], 'key'));

        $this->assertArrayHasKey('settings', $props);
        $this->assertArrayHasKey('includeCommunityModules', $props['settings']);

        $this->assertArrayHasKey('urls', $props);
        $this->assertArrayHasKey('moduleAdministration', $props['urls']);
    }
}
