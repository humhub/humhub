<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Exception;
use RuntimeException;

/**
 * @since 1.18
 */
class ScssHelper
{
    /**
     * Returns all SCSS variables of a given file
     * @param string $scssFilePath
     * @return array
     * @throws RuntimeException
     */
    public static function getVariables(array|string $scssFilePaths): array
    {
        if (is_string($scssFilePaths)) {
            $scssFilePaths = [$scssFilePaths];
        }

        try {
            // Read the SCSS file contents
            $scssContent = '';
            foreach ($scssFilePaths as $scssFilePath) {
                if (file_exists($scssFilePath)) {
                    $scssContent .= file_get_contents($scssFilePath);
                }
            }
            if (!$scssContent) {
                return [];
            }

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

                // Remove $ and trim
                $value = ltrim($value, '$');

                // Track to prevent infinite recursion
                $visited = [];

                // Resolve variable references
                while (isset($variables[$value]) && !isset($visited[$value])) {
                    $visited[$value] = true;
                    $value = $variables[$value];
                    $value = trim(ltrim($value, '$'), '"');
                }

                $resolvedVariables[$name] = $value;
            }

            return $resolvedVariables;
        } catch (Exception $e) {
            throw new RuntimeException("Error resolving SCSS variables: " . $e->getMessage());
        }
    }

    public static function getVariable(array|string $scssFilePaths, string $variableName): ?string
    {
        $variables = static::getVariables($scssFilePaths);
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
            if (!str_starts_with($value, '$')) {
                continue;
            }
            $linkedVarName = substr($value, 1);
            if (isset($variables[$linkedVarName])) {
                $variables[$name] = $variables[$linkedVarName];
            }
        }

        return $variables;
    }
}
