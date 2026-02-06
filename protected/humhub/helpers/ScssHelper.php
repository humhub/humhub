<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Exception;
use humhub\components\Theme;
use RuntimeException;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use Yii;

/**
 * @since 1.18
 */
class ScssHelper
{
    /**
     * @param Theme $theme
     * @return string
     */
    public static function getVariableFile(Theme $theme)
    {
        return $theme->getBasePath() . '/scss/variables.scss';
    }


    /**
     * Returns all SCSS variables of a given file
     * @param string $scssFilePath
     * @param bool $removeFlag Removes !default, !global, !optional and !important
     * @return array
     * @throws RuntimeException
     */
    public static function getVariables(string $scssFilePath, bool $removeFlag = true): array
    {
        if (!file_exists($scssFilePath)) {
            return [];
        }

        // Read the SCSS file contents
        $scssContent = file_get_contents($scssFilePath);

        return static::parseVariables($scssContent, $removeFlag);
    }

    /**
     * Returns all SCSS variables of a given SCSS content
     * @param string $scss
     * @param bool $removeFlag Removes !default, !global, !optional and !important
     * @return array
     * @throws RuntimeException
     */
    public static function parseVariables(string $scss, bool $removeFlag = true): array
    {
        try {
            // Extract all variable declarations
            preg_match_all('/\$([a-zA-Z0-9_-]+)\s*:\s*(.+?);/', $scss, $matches, PREG_SET_ORDER);

            $variables = [];
            $resolvedVariables = [];

            // First pass: Collect all variable declarations
            foreach ($matches as $match) {
                $variables[$match[1]] = $match[2];
            }

            // Second pass: Resolve variables to their final values
            foreach ($variables as $name => $value) {
                $value = static::parseValue($value, $removeFlag);

                // Resolve variable references
                $visited = []; // Track to prevent infinite recursion
                while (isset($variables[$value]) && !isset($visited[$value])) {
                    $visited[$value] = true;
                    $value = static::parseValue($variables[$value], $removeFlag);
                }

                $resolvedVariables[$name] = $value;
            }

            return $resolvedVariables;
        } catch (Exception $e) {
            throw new RuntimeException("Error resolving SCSS variables: " . $e->getMessage());
        }
    }

    private static function parseValue(?string $value, bool $removeFlag = true): string
    {
        if (!$value) {
            return '';
        }

        // Remove $ and trim
        $value = trim(ltrim($value, '$'));

        // Remove quotes (but not for values such as `"Noto Sans", sans-serif`)
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = trim($value, '"');
        }

        if ($removeFlag) {
            // Match CSS/SCSS flags at the end of the string such as !important, !default, !optional, !global, etc.
            $value = preg_replace('/\s*![a-zA-Z]+\s*$/', '', $value);
        }

        return $value;
    }

    /**
     * Extract top-level variables, maps, and other code from a SCSS code
     *
     * LIMITATIONS:
     * - Only extracts TOP-LEVEL variable declarations ($var: value;):
     *   - Does NOT extract variables inside @if/@else/@for/@while/@each blocks
     *   - Does NOT extract variables inside @mixin/@function definitions
     *   - Does NOT extract variables inside nested selectors
     * - Removes all comments before parsing
     *
     * @throws SassException if syntax error in SCSS
     * @throws RuntimeException if SCSS is malformed
     */
    public static function extractVariablesAndMaps(?string $scss): array
    {
        $scss = trim((string)$scss);
        if (!$scss) {
            return ['', '', ''];
        }

        // Throw SassException if syntax error in SCSS
        $compiler = new Compiler();
        $compiler->compileString($scss);

        $lines = explode("\n", $scss);
        $variables = [];
        $maps = [];
        $otherScss = [];
        $index = 0;

        while ($index < count($lines)) {
            $line = $lines[$index];
            $trimmed = trim($line);

            // Skip empty lines
            if (empty($trimmed)) {
                $index++;
                continue;
            }

            // Check if line starts with $ (variable declaration)
            if (preg_match('/^\$[\w-]+\s*:/', $trimmed)) {
                // Extract complete statement (may span multiple lines)
                $statement = self::extractVariableStatement($lines, $index);

                // Determine if it's a map (contains parentheses) or simple variable
                if (self::isMap($statement['content'])) {
                    $maps[] = $statement['content'];
                } else {
                    $variables[] = $statement['content'];
                }

                $index = $statement['nextIndex'];
            } else {
                // Everything else
                $otherScss[] = $line;
                $index++;
            }
        }

        return [
            implode("\n", $variables),
            implode("\n", $maps),
            implode("\n", $otherScss),
        ];
    }

    /**
     * Check if a variable declaration is a map (contains parentheses)
     * @throws RuntimeException if SCSS is malformed
     */
    private static function isMap(string $content): bool
    {
        // Maps are always multi-line in SCSS
        if (strpos($content, "\n") === false) {
            return false;
        }

        $inString = false;
        $stringDelimiter = null;

        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];

            // Handle strings
            if (($char === '"' || $char === "'") && ($i === 0 || $content[$i - 1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringDelimiter = $char;
                } elseif ($char === $stringDelimiter) {
                    $inString = false;
                }
                continue;
            }

            // Look for opening parenthesis outside of strings
            if (!$inString && $char === '(') {
                return true;
            }
        }

        // Check for unclosed strings
        if ($inString) {
            throw new RuntimeException('Unclosed string in SCSS content');
        }

        return false;
    }

    /**
     * Extract a complete variable statement that may span multiple lines
     * Reads until balanced parentheses and semicolon are found
     * @throws RuntimeException if SCSS is malformed
     */
    private static function extractVariableStatement(array $lines, int $startIndex): array
    {
        $content = [];
        $parenLevel = 0;
        $inString = false;
        $stringDelimiter = null;

        for ($i = $startIndex; $i < count($lines); $i++) {
            $line = $lines[$i];
            $content[] = $line;

            // Parse each character
            for ($j = 0; $j < strlen($line); $j++) {
                $char = $line[$j];

                // Handle string delimiters
                if (($char === '"' || $char === "'") && ($j === 0 || $line[$j - 1] !== '\\')) {
                    if (!$inString) {
                        $inString = true;
                        $stringDelimiter = $char;
                    } elseif ($char === $stringDelimiter) {
                        $inString = false;
                    }
                    continue;
                }

                // Skip everything inside strings
                if ($inString) {
                    continue;
                }

                // Track parentheses
                if ($char === '(') {
                    $parenLevel++;
                } elseif ($char === ')') {
                    $parenLevel--;
                    if ($parenLevel < 0) {
                        throw new RuntimeException('Unbalanced parentheses in SCSS');
                    }
                }
            }

            // Statement complete when parentheses balanced and semicolon found
            if ($parenLevel === 0 && strpos($line, ';') !== false) {
                return [
                    'content' => implode("\n", $content),
                    'nextIndex' => $i + 1,
                ];
            }
        }

        // Check for unclosed strings or unbalanced parentheses
        if ($inString) {
            throw new RuntimeException('Unclosed string in SCSS variable declaration');
        }
        if ($parenLevel !== 0) {
            throw new RuntimeException('Unbalanced parentheses in SCSS variable declaration');
        }

        // Fallback if no semicolon found
        throw new RuntimeException('Missing semicolon in SCSS variable declaration');
    }

    public static function getVariable(string $scssFilePath, string $variableName): ?string
    {
        $variables = static::getVariables($scssFilePath);
        return $variables[$variableName] ?? null;
    }


    /**
     * Update values of SCSS variables if they use value from another SCSS variables, for example:
     * @firstColor: #fff;
     * @secondColor: @firstColor;
     * @thirdColor: @secondColor;
     *
     * @param array $variables
     * @return array $variables
     * @since 1.7
     */
    public static function updateLinkedScssVariables($variables)
    {
        if (!is_array($variables)) {
            return [];
        }

        foreach ($variables as $name => $value) {
            if (!str_starts_with((string)$value, '$')) {
                continue;
            }
            $linkedVarName = substr((string)$value, 1);
            if (isset($variables[$linkedVarName])) {
                $variables[$name] = $variables[$linkedVarName];
            }
        }

        return $variables;
    }


    /**
     * Returns black or white text color based on color contrast.
     * Similar to Bootstrap's color-contrast() Sass function.
     *
     * @param string|null $color Hex, RGBA/RGB, or Sass variable
     * @param float|null $minContrast
     * @return string|null Hex color ('#FFFFFF' or '#000000')
     */
    public static function getColorContrast(?string $color, ?float $minContrast = null): ?string
    {
        if ($minContrast === null) {
            $minContrast = (float) Yii::$app->view->theme->variable('min-contrast-ratio', 3);
        }

        $color = trim((string) $color);

        if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
            // Not #xxxxxx format
            if (preg_match('/^#[0-9A-F]{3}$/i', $color)) {
                // #xxx format
                $color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
            } elseif (preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)/', $color, $m)) {
                // RGBA/RGB
                $color = sprintf("#%02x%02x%02x", $m[1], $m[2], $m[3]);
            } elseif ($color) {
                // Sass variable
                $color = Yii::$app->view->theme->variable($color);
            }
        }

        if (!$color) {
            return null;
        }

        $hex = ltrim($color, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Calculate relative luminance
        $r = $r <= 0.03928 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = $g <= 0.03928 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = $b <= 0.03928 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;
        $lum = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        // Calculate contrast with white and black
        $contrastWhite = (max($lum, 1) + 0.05) / (min($lum, 1) + 0.05);

        return $contrastWhite >= $minContrast ? '#FFFFFF' : '#000000';
    }
}
