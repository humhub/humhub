<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\menu\MenuLink;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * A label directly follows the icon's closing `</i>` tag; the visual gap is the icon's
 * `icon-before-label` margin rather than the icon font's own glyph metrics — which is what silently
 * broke when Font Awesome 4 was replaced by Tabler Icons, since Tabler crops each glyph to its own
 * bounds instead of a fixed per-character advance width — and not a space, which a link underlined
 * in high contrast would underline too.
 */
class ButtonWidgetTest extends HumHubDbTestCase
{
    public function testIconBeforeALabelCarriesTheGap()
    {
        $html = (string)Button::none('Users')->icon('user');

        $this->assertStringContainsString('icon-before-label', $html);
        $this->assertStringContainsString('</i>Users<', $html);
    }

    public function testIconOnlyButtonHasNoTrailingSpace()
    {
        $html = (string)Button::none()->icon('user');

        $this->assertStringContainsString('btn-icon-only', $html);
        $this->assertStringNotContainsString('icon-before-label', $html);
        $this->assertStringEndsWith('</i></button>', $html);
    }

    public function testLabelOnlyButtonIsUnaffected()
    {
        $html = (string)Button::none('Users');

        $this->assertStringContainsString('>Users<', $html);
    }

    public function testLinkAlsoGetsTheGap()
    {
        $html = (string)Link::to('Back')->icon('arrow-left');

        $this->assertStringContainsString('icon-before-label', $html);
        $this->assertStringContainsString('</i>Back<', $html);
    }

    /**
     * MenuLink (used by the left navigation, e.g. the admin menu) renders through Button::none().
     */
    public function testMenuLinkIconAndLabelAreSeparated()
    {
        $link = new MenuLink(['label' => 'Spaces', 'url' => '#', 'icon' => 'dot-circle-o']);

        $html = $link->render();

        $this->assertStringContainsString('icon-before-label', $html);
        $this->assertStringContainsString('</i>Spaces<', $html);
    }
}
