<?php

namespace tests\codeception\unit;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use tests\codeception\_support\HumHubDbTestCase;

class ThemeHelperTest extends HumHubDbTestCase
{
    public function testBuildCss()
    {
        $theme = ThemeHelper::getThemeByName('HumHub');
        $this->assertInstanceOf(Theme::class, $theme);
        $this->assertTrue(ThemeHelper::buildCss($theme));
    }
}
