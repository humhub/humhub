<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\helpers;

use humhub\helpers\ScssHelper;
use ScssPhp\ScssPhp\Compiler;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The functions of `resources/scss/_functions.scss`
 */
class ScssFunctionsTest extends HumHubDbTestCase
{
    public function testReadableKeepsAColorThatReads()
    {
        $this->assertSame('#435f6f', $this->readable('#435f6f', '#ededed'));
    }

    public function testReadableDarkensOnALightBackground()
    {
        $color = $this->readable('#21a1b3', '#ededed');

        $this->assertSame('#187683', $color);
        $this->assertGreaterThanOrEqual(4.5, ScssHelper::getContrastRatio($color, '#ededed'));
    }

    public function testReadableLightensOnADarkBackground()
    {
        $color = $this->readable('#1700c7', '#393939');

        $this->assertGreaterThanOrEqual(4.5, ScssHelper::getContrastRatio($color, '#393939'));
        $this->assertGreaterThan(ScssHelper::getRelativeLuminance('#1700c7'), ScssHelper::getRelativeLuminance($color));
    }

    public function testReadableTakesTheRatio()
    {
        $this->assertGreaterThanOrEqual(7, ScssHelper::getContrastRatio($this->readable('#21a1b3', '#ffffff', 7), '#ffffff'));
    }

    public function testReadableLeavesWhatIsNotAColor()
    {
        $this->assertSame('var(--bs-accent)', $this->readable('var(--bs-accent)', '#ffffff'));
    }

    private function readable(string $color, string $background, ?float $ratio = null): string
    {
        $compiler = new Compiler();
        $compiler->setImportPaths(Yii::getAlias('@vendor/twbs/bootstrap/scss'));
        $css = $compiler->compileString(
            '@import "' . Yii::getAlias('@vendor/twbs/bootstrap/scss/functions') . '", "'
            . Yii::getAlias('@humhub/resources/scss/functions') . '";'
            . '$result: hh-readable(' . $color . ', ' . $background . ($ratio ? ', ' . $ratio : '') . ');'
            . 'a { color: #{if(type-of($result) == color, ie-hex-str($result), $result)}; }',
        )->getCss();

        preg_match('/color: (.*);/', $css, $match);

        return preg_match('/^#FF([0-9A-F]{6})$/i', $match[1], $hex) ? '#' . strtolower($hex[1]) : $match[1];
    }
}
