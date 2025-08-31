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
     */
    public static function getVariables(string $scssFilePath, bool $removeFlag = true): array
    {
        if (!file_exists($scssFilePath)) {
            return [];
        }

        try {
            // Read the SCSS file contents
            $scssContent = file_get_contents($scssFilePath);

            // Extract all variable declarations
            preg_match_all('/\$([a-zA-Z0-9_-]+)\s*:\s*(.+?);/', $scssContent, $matches, PREG_SET_ORDER);

            $variables = [];
            $resolvedVariables = [];

            // First pass: Collect all variable declarations
            foreach ($matches as $match) {
                $variableName = $match[1];
                $variableValue = trim($match[2]);
                $variables[$variableName] = trim($variableValue, '"');
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
        $value = trim(trim(ltrim($value, '$'), '"'));

        if ($removeFlag) {
            // Match CSS/SCSS flags at the end of the string such as !important, !default, !optional, !global, etc.
            $value = preg_replace('/\s*![a-zA-Z]+\s*$/', '', $value);
        }

        return $value;
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
}
