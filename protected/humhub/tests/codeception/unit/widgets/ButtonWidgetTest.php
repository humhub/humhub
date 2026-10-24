<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\menu\MenuLink;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * A label directly follows the icon's closing `</i>` tag with no separating markup; the visual gap
 * relies entirely on an explicit space, inserted here, rather than on the icon font's own glyph
 * metrics — which is what silently broke when Font Awesome 4 was replaced by Tabler Icons, since
 * Tabler crops each glyph to its own bounds instead of a fixed per-character advance width.
 */
class ButtonWidgetTest extends HumHubDbTestCase
{
    public function testIconAndLabelAreSeparatedBySpace()
    {
        $html = (string)Button::none('Users')->icon('user');

        $this->assertStringContainsString('</i> Users<', $html);
    }

    public function testIconOnlyButtonHasNoTrailingSpace()
    {
        $html = (string)Button::none()->icon('user');

        $this->assertStringContainsString('btn-icon-only', $html);
        $this->assertStringEndsWith('</i></button>', $html);
    }

    public function testLabelOnlyButtonIsUnaffected()
    {
        $html = (string)Button::none('Users');

        $this->assertStringContainsString('>Users<', $html);
    }

    public function testLinkAlsoGetsTheSpace()
    {
        $html = (string)Link::to('Back')->icon('arrow-left');

        $this->assertStringContainsString('</i> Back<', $html);
    }

    /**
     * MenuLink (used by the left navigation, e.g. the admin menu) renders through Button::none().
     */
    public function testMenuLinkIconAndLabelAreSeparated()
    {
        $link = new MenuLink(['label' => 'Spaces', 'url' => '#', 'icon' => 'dot-circle-o']);

        $this->assertStringContainsString('</i> Spaces<', $link->render());
    }
}
