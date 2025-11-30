<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2018-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit\helpers;

use Codeception\Test\Unit;
use humhub\helpers\ScssHelper;
use Yii;

class ScssHelperTest extends Unit
{
    public function testParseScssVariablesFile()
    {
        $file = Yii::getAlias('@webroot-static/scss/variables.scss');
        $accent = ScssHelper::getVariable($file, 'accent');

        $this->assertEquals('#21A1B3', $accent);
    }

    public function testSimpleVariables()
    {
        $scss = <<<'SCSS'
$color: red;
$font-size: 16px;
$margin: 10px 20px;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$color: red;', $customVariables);
        $this->assertStringContainsString('$font-size: 16px;', $customVariables);
        $this->assertStringContainsString('$margin: 10px 20px;', $customVariables);
        $this->assertEmpty($customMaps);
        $this->assertEmpty($otherCustomScss);
    }

    public function testMaps()
    {
        $scss = <<<'SCSS'
$theme-colors: (
  "primary": #007bff,
  "secondary": #6c757d
) !default;

$spacing: (
  sm: 8px,
  md: 16px,
  lg: 24px
);

$map1: (a: 1);
$map2: (b: 2);
$merged: map-merge($map1, $map2);
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertEmpty($customVariables);
        $this->assertStringContainsString('$theme-colors:', $customMaps);
        $this->assertStringContainsString('$spacing:', $customMaps);
        $this->assertStringContainsString('$map1:', $customMaps);
        $this->assertStringContainsString('$map2:', $customMaps);
        $this->assertStringContainsString('$merged: map-merge', $customMaps);
        $this->assertEmpty($otherCustomScss);
    }

    public function testMixedContent()
    {
        $scss = <<<'SCSS'
$primary: blue;

.button {
  color: $primary;
  padding: 10px;
}

$colors: (
  red: #ff0000,
  green: #00ff00
);

#header {
  background: white;
}
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$primary: blue;', $customVariables);
        $this->assertStringContainsString('$colors:', $customMaps);
        $this->assertStringContainsString('.button', $otherCustomScss);
        $this->assertStringContainsString('#header', $otherCustomScss);
    }

    public function testIfElseBlocksGoToOtherScss()
    {
        $scss = <<<'SCSS'
$base-color: red;
$enable-dark-mode: true;

@if $enable-dark-mode {
  $theme-colors: (
    "dark": #000
  );

  .dark-theme {
    background: black;
  }
} @else {
  $theme-colors: (
    "light": #fff
  );
}

$another-var: green;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Top-level variables should be extracted
        $this->assertStringContainsString('$base-color: red;', $customVariables);
        $this->assertStringContainsString('$another-var: green;', $customVariables);
        $this->assertStringContainsString('$enable-dark-mode: true;', $customVariables);

        // Variables inside @if blocks ARE extracted by the parser
        $this->assertStringContainsString('$theme-colors:', $customMaps);

        // @if/@else control structures (without the variables) go to other_scss
        $this->assertStringContainsString('@if $enable-dark-mode', $otherCustomScss);
        $this->assertStringContainsString('@else', $otherCustomScss);
        $this->assertStringContainsString('.dark-theme', $otherCustomScss);
    }

    public function testMixinsAndFunctionsGoToOtherScss()
    {
        $scss = <<<'SCSS'
$global-padding: 20px;

@mixin button-styles {
  $local-color: blue;
  padding: 10px;
  color: $local-color;
}

@function calculate-rem($px) {
  $base: 16px;
  @return $px / $base * 1rem;
}

$spacing: 10px;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Top-level variables extracted
        $this->assertStringContainsString('$global-padding: 20px;', $customVariables);
        $this->assertStringContainsString('$spacing: 10px;', $customVariables);

        // Mixins and functions go to other_scss
        $this->assertStringContainsString('@mixin button-styles', $otherCustomScss);
        $this->assertStringContainsString('@function calculate-rem', $otherCustomScss);
    }

    public function testLoopsGoToOtherScss()
    {
        $scss = <<<'SCSS'
$columns: 12;

@for $i from 1 through 12 {
  $width: 100% / 12 * $i;
  .col-#{$i} {
    width: $width;
  }
}

@each $color in "red", "green", "blue" {
  .bg-#{$color} {
    background: $color;
  }
}

$count: 0;
@while $count < 5 {
  $count: $count + 1;
}

$base: 16px;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Top-level variables extracted
        $this->assertStringContainsString('$columns: 12;', $customVariables);
        $this->assertStringContainsString('$base: 16px;', $customVariables);
        $this->assertStringContainsString('$count: 0;', $customVariables);

        // Loops go to other_scss
        $this->assertStringContainsString('@for $i', $otherCustomScss);
        $this->assertStringContainsString('@each $color', $otherCustomScss);
        $this->assertStringContainsString('@while $count', $otherCustomScss);
    }

    public function testComplexNestedMaps()
    {
        $scss = <<<'SCSS'
$border-color: gray;

$theme: (
  colors: (
    primary: (
      base: #007bff,
      light: #cce5ff,
      dark: #004085
    ),
    secondary: (
      base: #6c757d
    )
  ),
  spacing: (
    small: 8px,
    large: 24px
  )
) !default;

.container {
  .nested {
    $local: red;
    color: $local;
  }
}
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$border-color: gray;', $customVariables);
        $this->assertStringContainsString('$theme:', $customMaps);
        $this->assertStringContainsString('.container', $otherCustomScss);
    }

    public function testCommentsAreRemoved()
    {
        $scss = <<<'SCSS'
// This is a single line comment
$color: red;

/* Multi-line comment
   with multiple lines
   of text */
$size: 16px;

$map: (
  // Inline comment
  key: value /* another comment */
);

/* Comment */ $commented: blue;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$color: red;', $customVariables);
        $this->assertStringContainsString('$size: 16px;', $customVariables);
        $this->assertStringContainsString('$commented: blue;', $customVariables);
        $this->assertStringContainsString('$map:', $customMaps);

        // Comments should be removed
        $this->assertStringNotContainsString('//', $customVariables);
        $this->assertStringNotContainsString('/*', $customVariables);
    }

    public function testMultilineDeclarations()
    {
        $scss = <<<'SCSS'
$complex-map: (
  level1: (
    level2: (
      level3: value
    ),
    another: test
  ),
  simple: value
);

$arg1: 10px;
$arg2: 20px;
$arg3: 30px;
$function-call: some-function(
  $arg1,
  $arg2,
  $arg3
);

$long-value: "very long string " +
  "concatenated " +
  "across multiple lines";
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$complex-map:', $customMaps);
        $this->assertStringContainsString('level3: value', $customMaps);
        $this->assertStringContainsString('$function-call:', $customMaps);
        $this->assertStringContainsString('$long-value:', $customVariables);
    }

    public function testEdgeCasesWithSpecialCharacters()
    {
        $scss = <<<'SCSS'
$quoted-paren: "value with (parentheses)";
$string-with-semicolon: "value; with semicolon";
$escaped-quote: "value with \" escaped quote";

$map-with-strings: (
  "key(1)": "value;1",
  "key(2)": "value;2"
);
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Strings with parentheses should still be variables, not maps
        $this->assertStringContainsString('$quoted-paren:', $customVariables);
        $this->assertStringContainsString('$string-with-semicolon:', $customVariables);
        $this->assertStringContainsString('$escaped-quote:', $customVariables);

        // But actual maps should be detected
        $this->assertStringContainsString('$map-with-strings:', $customMaps);
    }

    public function testReconstructionPreservesAllContent()
    {
        $scss = <<<'SCSS'
$var1: red;
$map1: (key: value);
.selector { color: blue; }
$condition: true;
@if $condition { $var2: green; }
$var3: yellow;
@mixin test { $var4: purple; }
$a: (x: 1);
$b: (y: 2);
$map2: map-merge($a, $b);
#id { background: white; }
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Reconstruct
        $reconstructed = trim(
            $customVariables . "\n"
            . $customMaps . "\n"
            . $otherCustomScss,
        );

        // Normalize both
        $scssNormalized = preg_replace('/\/\*.*?\*\//s', '', $scss);
        $scssNormalized = preg_replace('/\/\/.*$/m', '', $scssNormalized);
        $scssNormalized = preg_replace('/\n\s*\n+/', "\n", trim($scssNormalized));

        $reconstructedNormalized = preg_replace('/\n\s*\n+/', "\n", trim($reconstructed));

        // Count lines
        $originalLines = count(array_filter(explode("\n", $scssNormalized)));
        $reconstructedLines = count(array_filter(explode("\n", $reconstructedNormalized)));

        $this->assertEquals(
            $originalLines,
            $reconstructedLines,
            'Reconstructed SCSS should have the same number of lines as original',
        );

        // Check that all content is preserved (order may differ)
        $this->assertStringContainsString('$var1: red;', $reconstructed);
        $this->assertStringContainsString('$var3: yellow;', $reconstructed);
        $this->assertStringContainsString('$map1:', $reconstructed);
        $this->assertStringContainsString('$map2:', $reconstructed);
        $this->assertStringContainsString('.selector', $reconstructed);
        $this->assertStringContainsString('@if $condition', $reconstructed);
        $this->assertStringContainsString('@mixin test', $reconstructed);
        $this->assertStringContainsString('#id', $reconstructed);
    }

    public function testEmptyInput()
    {
        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps('');

        $this->assertEmpty($customVariables);
        $this->assertEmpty($customMaps);
        $this->assertEmpty($otherCustomScss);
    }

    public function testOnlyComments()
    {
        $scss = <<<'SCSS'
// Just comments
/* Nothing else */
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertEmpty($customVariables);
        $this->assertEmpty($customMaps);
        $this->assertEmpty($otherCustomScss);
    }

    public function testRealWorldBootstrapExample()
    {
        $scss = <<<'SCSS'
$primary: #007bff;
$secondary: #6c757d;
$success: #28a745;
$info: #17a2b8;
$warning: #ffc107;
$danger: #dc3545;
$light: #f8f9fa;
$dark: #343a40;
$accent: #6610f2;
$accent-dark: #520dc2;
$enable-dark-mode: false;

$theme-colors: (
  "primary": $primary,
  "secondary": $secondary,
  "success": $success,
  "info": $info,
  "warning": $warning,
  "danger": $danger,
  "light": $light,
  "dark": $dark
) !default;

$theme-colors: map-merge($theme-colors, (
  "accent": $accent
));

$theme-colors-dark: (
  "primary": darken($primary, 10%)
);

@if $enable-dark-mode {
  $theme-colors-dark: map-merge($theme-colors-dark, (
    "accent": $accent-dark
  ));
}

.btn {
  padding: 0.375rem 0.75rem;
  font-size: 1rem;
}
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Variables
        $this->assertStringContainsString('$primary: #007bff;', $customVariables);
        $this->assertStringContainsString('$secondary: #6c757d;', $customVariables);
        $this->assertStringContainsString('$enable-dark-mode: false;', $customVariables);

        // Maps
        $this->assertStringContainsString('$theme-colors:', $customMaps);
        $this->assertStringContainsString('map-merge', $customMaps);

        // Other
        $this->assertStringContainsString('@if $enable-dark-mode', $otherCustomScss);
        $this->assertStringContainsString('.btn', $otherCustomScss);
    }
}
