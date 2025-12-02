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
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertEmpty($customVariables);
        $this->assertStringContainsString('$theme-colors:', $customMaps);
        $this->assertStringContainsString('$spacing:', $customMaps);
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

    public function testImportUrlStatements()
    {
        $scss = <<<'SCSS'
@import url("https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap");
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');
@import url(https://example.com/styles.css);

$primary: blue;

$colors: (
  red: #ff0000
);

.button {
  color: $primary;
}
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Variables should be extracted
        $this->assertStringContainsString('$primary: blue;', $customVariables);

        // Maps should be extracted
        $this->assertStringContainsString('$colors:', $customMaps);

        // @import statements should go to otherScss, not be treated as maps
        $this->assertStringContainsString('@import url("https://fonts.googleapis.com/css2?family=Noto+Sans', $otherCustomScss);
        $this->assertStringContainsString("@import url('https://fonts.googleapis.com/css2?family=Roboto", $otherCustomScss);
        $this->assertStringContainsString('@import url(https://example.com/styles.css)', $otherCustomScss);
        $this->assertStringContainsString('.button', $otherCustomScss);

        // Make sure imports are NOT in maps
        $this->assertStringNotContainsString('@import', $customMaps);
    }


    public function testMediaQueries()
    {
        $scss = <<<'SCSS'
$breakpoint: 768px;

@media (min-width: $breakpoint) {
  $mobile-padding: 10px;
  .container {
    padding: $mobile-padding;
  }
}

@media screen and (max-width: 600px) {
  .sidebar {
    display: none;
  }
}

$desktop-width: 1200px;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$breakpoint: 768px;', $customVariables);
        $this->assertStringContainsString('$desktop-width: 1200px;', $customVariables);
        $this->assertStringContainsString('@media (min-width: $breakpoint)', $otherCustomScss);
        $this->assertStringContainsString('@media screen and', $otherCustomScss);
    }

    public function testKeyframesAndFontFace()
    {
        $scss = <<<'SCSS'
$animation-duration: 2s;

@keyframes slideIn {
  from {
    transform: translateX(-100%);
  }
  to {
    transform: translateX(0);
  }
}

@font-face {
  font-family: "CustomFont";
  src: url("font.woff2") format("woff2");
}

$font-stack: "CustomFont", sans-serif;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$animation-duration: 2s;', $customVariables);
        $this->assertStringContainsString('$font-stack:', $customVariables);
        $this->assertStringContainsString('@keyframes slideIn', $otherCustomScss);
        $this->assertStringContainsString('@font-face', $otherCustomScss);
    }

    public function testCharsetAndNamespace()
    {
        $scss = <<<'SCSS'
@charset "UTF-8";
@namespace svg url(http://www.w3.org/2000/svg);

$encoding: "utf-8";

.icon {
  background: url("data:image/svg+xml;charset=utf-8,...");
}
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$encoding: "utf-8";', $customVariables);
        $this->assertStringContainsString('@charset "UTF-8"', $otherCustomScss);
        $this->assertStringContainsString('@namespace svg', $otherCustomScss);
        $this->assertStringContainsString('.icon', $otherCustomScss);
    }

    public function testSupportsQuery()
    {
        $scss = <<<'SCSS'
$use-grid: true;

@supports (display: grid) {
  .container {
    display: grid;
  }
}

@supports not (display: flex) {
  .fallback {
    display: block;
  }
}

$fallback-display: block;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$use-grid: true;', $customVariables);
        $this->assertStringContainsString('$fallback-display: block;', $customVariables);
        $this->assertStringContainsString('@supports (display: grid)', $otherCustomScss);
        $this->assertStringContainsString('@supports not (display: flex)', $otherCustomScss);
    }

    public function testUrlFunctionsInVariables()
    {
        $scss = <<<'SCSS'
$bg-image: url("images/background.jpg");
$icon: url('data:image/svg+xml;utf8,<svg>...</svg>');
$font: url(https://example.com/font.woff2);

$colors: (
  primary: #007bff,
  bg: url("pattern.png")
);
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // url() in variable values should still be treated as variables
        $this->assertStringContainsString('$bg-image: url("images/background.jpg");', $customVariables);
        $this->assertStringContainsString('$icon: url(', $customVariables);
        $this->assertStringContainsString('$font: url(https://example.com/font.woff2);', $customVariables);

        // url() in maps should still be treated as maps
        $this->assertStringContainsString('$colors:', $customMaps);
        $this->assertStringContainsString('url("pattern.png")', $customMaps);
    }

    public function testCalcAndOtherFunctions()
    {
        $scss = <<<'SCSS'
$spacing: calc(100% - 20px);
$opacity: rgba(0, 0, 0, 0.5);
$gradient: linear-gradient(to right, red, blue);
$transform: rotate(45deg) translate(10px, 20px);

$complex-map: (
  width: calc(100vw - 2rem),
  color: rgba(255, 0, 0, 0.8),
  background: linear-gradient(45deg, #fff, #000)
);
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$spacing: calc(', $customVariables);
        $this->assertStringContainsString('$opacity: rgba(', $customVariables);
        $this->assertStringContainsString('$gradient: linear-gradient(', $customVariables);
        $this->assertStringContainsString('$transform: rotate(', $customVariables);
        $this->assertStringContainsString('$complex-map:', $customMaps);
    }

    public function testInterpolationAndEscaping()
    {
        $scss = <<<'SCSS'
$name: "button";
$selector: ".#{$name}-primary";
$content: "String with \"quotes\" and (parens)";
$url: "https://example.com?param=value&other=#{$name}";

.#{$name} {
  content: $content;
}
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        $this->assertStringContainsString('$name: "button";', $customVariables);
        $this->assertStringContainsString('$selector:', $customVariables);
        $this->assertStringContainsString('$content:', $customVariables);
        $this->assertStringContainsString('$url:', $customVariables);
        $this->assertStringContainsString('.#{$name}', $otherCustomScss);
    }

    public function testComplexRealWorldScenario()
    {
        $scss = <<<'SCSS'
@import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap");

// Theme configuration
$enable-dark-mode: true;
$base-font-size: 16px;

// Color palette
$colors: (
  "primary": #007bff,
  "secondary": #6c757d,
  "success": #28a745
) !default;

// Spacing system
$spacer: 1rem;
$spacing: (
  0: 0,
  1: $spacer * 0.25,
  2: $spacer * 0.5,
  3: $spacer,
  4: $spacer * 1.5,
  5: $spacer * 3
);

// Mixins
@mixin responsive($breakpoint) {
  @media (min-width: $breakpoint) {
    @content;
  }
}

// Animations
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

// Utilities
.container {
  max-width: 1200px;
  margin: 0 auto;
}

$animation-duration: 0.3s;
SCSS;

        [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps($scss);

        // Top-level variables
        $this->assertStringContainsString('$enable-dark-mode: true;', $customVariables);
        $this->assertStringContainsString('$base-font-size: 16px;', $customVariables);
        $this->assertStringContainsString('$spacer: 1rem;', $customVariables);
        $this->assertStringContainsString('$animation-duration: 0.3s;', $customVariables);

        // Maps
        $this->assertStringContainsString('$colors:', $customMaps);
        $this->assertStringContainsString('$spacing:', $customMaps);

        // Other SCSS
        $this->assertStringContainsString('@import url("https://fonts.googleapis.com', $otherCustomScss);
        $this->assertStringContainsString('@mixin responsive', $otherCustomScss);
        $this->assertStringContainsString('@keyframes fadeIn', $otherCustomScss);
        $this->assertStringContainsString('.container', $otherCustomScss);

        // Verify imports are not in maps
        $this->assertStringNotContainsString('@import', $customMaps);
        $this->assertStringNotContainsString('@use', $customMaps);
    }
}
