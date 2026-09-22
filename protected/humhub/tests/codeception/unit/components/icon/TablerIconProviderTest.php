<?php

namespace humhub\tests\codeception\unit\components\icon;

use humhub\components\icon\IconFactory;
use humhub\components\icon\LegacyIconMap;
use humhub\components\icon\TablerIconProvider;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\Icon;
use tests\codeception\_support\HumHubDbTestCase;

class TablerIconProviderTest extends HumHubDbTestCase
{
    public function testTablerIsTheDefaultProvider()
    {
        $this->assertInstanceOf(TablerIconProvider::class, IconFactory::getInstance()->getProvider());
        $this->assertInstanceOf(TablerIconProvider::class, IconFactory::$fallbackProvider);
    }

    public function testRendersTablerMarkup()
    {
        $this->assertEquals('<i class="ti ti-pencil" aria-hidden="true"></i>', (string)Icon::get('pencil'));
    }

    public function testRendersOptions()
    {
        $html = (string)Icon::get('pencil')->size(Icon::SIZE_LG)->fixedWith()->right()->color('danger')->class('extra');
        $this->assertStringContainsString('class="extra ti ti-pencil icon-lg icon-fw icon-pull-right"', $html);
        $this->assertStringContainsString('style="color: var(--bs-danger);"', $html);
    }

    public function testRendersTooltipWithAccessibleLabel()
    {
        $html = (string)Icon::get('pencil')->tooltip('Edit');
        $this->assertStringContainsString('data-bs-title="Edit"', $html);
        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('<span class="visually-hidden">Edit</span>', $html);
    }

    public function testResolvesFontAwesomeNames()
    {
        $this->assertStringContainsString('ti ti-settings', (string)Icon::get('cog'));
        $this->assertStringContainsString('ti ti-settings', (string)Icon::get('fa-cog'));
        $this->assertStringContainsString('ti ti-map-pin-filled', (string)Icon::get('map-marker'));
        $this->assertStringContainsString('ti ti-thumb-up-filled', (string)Icon::get('thumbs-up'));
        $this->assertStringContainsString('ti ti-star"', (string)Icon::get('star-o'));
    }

    /**
     * A name both libraries know renders the Tabler icon: Tabler names are the canonical ones, and
     * the alias `delete` resolves to `trash`, which must not turn into `trash-filled`.
     */
    public function testTablerNameWinsOverLegacyName()
    {
        $this->assertStringContainsString('ti ti-star"', (string)Icon::get('star'));
        $this->assertStringContainsString('ti ti-trash"', (string)Icon::get('trash'));
        $this->assertStringContainsString('ti ti-user"', (string)Icon::get('user'));
        $this->assertEquals('star', TablerIconProvider::resolveLegacyName('star'));
        $this->assertEquals('star-filled', LegacyIconMap::resolve('star'));
    }

    public function testStripsTablerPrefix()
    {
        $this->assertStringContainsString('ti ti-pencil"', (string)Icon::get('ti-pencil'));
    }

    public function testAppliesAliasMap()
    {
        $this->assertStringContainsString('ti ti-trash"', (string)Icon::get('delete'));
        $this->assertStringContainsString('ti ti-x"', (string)Icon::get('remove'));
    }

    public function testUnknownNameIsRenderedAsGiven()
    {
        $this->assertStringContainsString('ti ti-no-such-icon', (string)Icon::get('no-such-icon'));
    }

    public function testFontAwesomeProviderStillRendersOnRequest()
    {
        $this->assertStringContainsString('fa fa-cog', (string)Icon::get('cog', ['lib' => 'fa']));
    }

    public function testNamesComeFromThePackage()
    {
        $names = Icon::getNames();
        $this->assertGreaterThan(5000, count($names));
        $this->assertContains('pencil', $names);
        $this->assertContains('star-filled', $names);
        $this->assertTrue(TablerIconProvider::hasName('star-filled'));
        $this->assertFalse(TablerIconProvider::hasName('cog'));
    }

    public function testButtonIconExtractsNameFromLegacyHtml()
    {
        $this->assertStringContainsString('ti ti-map-pin', (string)Badge::none('x')->icon('<i class="fa fa-map-pin"></i>'));
        $this->assertStringContainsString('ti ti-map-pin', (string)Badge::none('x')->icon('<i class="ti ti-map-pin"></i>'));
        $this->assertStringContainsString('ti ti-map-pin-filled', (string)Badge::none('x')->icon('fa-map-marker'));
    }
}
