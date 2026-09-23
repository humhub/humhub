<?php

namespace humhub\tests\codeception\unit\components\icon;

use humhub\components\icon\LegacyIconMap;
use humhub\components\icon\TablerIconProvider;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * `css/icon-filled.css` and `css/icon-legacy.css` are derived from the installed Tabler package and
 * from LegacyIconMap. This test rebuilds both from those sources and compares; after a package update
 * or a map change, regenerate them with
 *
 *     REGENERATE_ICON_CSS=1 codecept run unit components/icon/IconCssSyncTest
 */
class IconCssSyncTest extends HumHubDbTestCase
{
    public function testFilledCssIsInSync()
    {
        $this->assertCssFile('icon-filled.css', self::buildFilledCss());
    }

    public function testLegacyCssIsInSync()
    {
        $this->assertCssFile('icon-legacy.css', self::buildLegacyCss());
    }

    public function testFilledFontIsTheOneFromThePackage()
    {
        $this->assertEquals(
            hash_file('sha256', TablerIconProvider::getDistPath() . '/fonts/tabler-icons-filled.woff2'),
            hash_file('sha256', Yii::getAlias('@humhub/resources/fonts/tabler-icons-filled.woff2')),
            'resources/fonts/tabler-icons-filled.woff2 differs from the package — copy it over',
        );
    }

    private function assertCssFile(string $file, string $expected): void
    {
        $path = Yii::getAlias('@humhub/resources/css/' . $file);

        if (getenv('REGENERATE_ICON_CSS')) {
            file_put_contents($path, $expected);
        }

        $this->assertStringEqualsFile($path, $expected, "$file is out of sync, see the class docblock");
    }

    public static function buildFilledCss(): string
    {
        $version = TablerIconProvider::getPackageVersion();
        $css = self::header("Tabler Icons $version — filled variants as ti-<name>-filled", 'tabler-icons-filled.scss')
            . '@font-face{font-family:"tabler-icons-filled";font-style:normal;font-weight:400;font-display:block;'
            . 'src:url("../fonts/tabler-icons-filled.woff2?v' . $version . '") format("woff2")}' . "\n";

        foreach (TablerIconProvider::getCodepoints()['filled'] as $name => $codepoint) {
            $css .= '.ti-' . $name . '-filled:before{font-family:"tabler-icons-filled";content:"\\' . $codepoint . '"}' . "\n";
        }

        return $css;
    }

    public static function buildLegacyCss(): string
    {
        $codepoints = TablerIconProvider::getCodepoints();
        $css = self::header('Font Awesome 4 compatibility layer — renders fa fa-<name> markup with Tabler glyphs. Removed in 1.22', 'LegacyIconMap')
            . '.fa{font-family:"tabler-icons" !important;speak:none;font-style:normal;font-weight:normal;font-variant:normal;'
            . 'text-transform:none;line-height:1;display:inline-block;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}' . "\n"
            . '.fa-fw{width:1.25em;text-align:center}' . "\n"
            . '.fa-xs{font-size:.75em}.fa-sm{font-size:.875em}.fa-lg{font-size:1.33333em;line-height:.75em;vertical-align:-.0667em}' . "\n"
            . '.fa-2x{font-size:2em}.fa-3x{font-size:3em}.fa-4x{font-size:4em}.fa-5x{font-size:5em}.fa-6x{font-size:6em}'
            . '.fa-7x{font-size:7em}.fa-8x{font-size:8em}.fa-9x{font-size:9em}.fa-10x{font-size:10em}' . "\n"
            . '.fa-pull-left{float:left;margin-right:.3em}.fa-pull-right{float:right;margin-left:.3em}' . "\n"
            . '.fa-spin{animation:fa-spin 2s infinite linear}@keyframes fa-spin{0%{transform:rotate(0)}100%{transform:rotate(359deg)}}' . "\n";

        foreach (LegacyIconMap::MAP as $name => $target) {
            if ($target === null) {
                continue;
            }
            if (str_ends_with($target, TablerIconProvider::FILLED_SUFFIX)) {
                $codepoint = $codepoints['filled'][substr($target, 0, -strlen(TablerIconProvider::FILLED_SUFFIX))];
                $css .= '.fa-' . $name . ':before{font-family:"tabler-icons-filled";content:"\\' . $codepoint . '"}' . "\n";
            } else {
                $css .= '.fa-' . $name . ':before{content:"\\' . $codepoints['outline'][$target] . '"}' . "\n";
            }
        }

        return $css;
    }

    private static function header(string $title, string $source): string
    {
        return "/*!\n * $title\n * Generated from $source by IconCssSyncTest — do not edit, regenerate with\n"
            . " * REGENERATE_ICON_CSS=1 codecept run unit components/icon/IconCssSyncTest\n"
            . " * Tabler Icons: MIT, https://github.com/tabler/tabler-icons/blob/main/LICENSE\n */\n";
    }
}
