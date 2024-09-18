<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\helpers;

use humhub\modules\ui\view\components\Theme;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;

/**
 * @since 1.17
 */
class ScssHelper
{
    /**
     * @param Theme $theme
     * @return string
     */
    public static function getVariableFile(Theme $theme)
    {
        return $theme->getBasePath() . '/scss/_variables.scss';
    }


    /**
     * Returns all SCSS variables of a given file
     * @throws \Exception
     */
    public static function getVariables(string $scssFile): array
    {
        if (file_exists($scssFile)) {
            $compiler = new Compiler();
            try {
                $compiler->compileFile($scssFile);
            } catch (SassException $e) {
                throw new \Exception('Error while compiling SCSS file: ' . $e->getMessage());
            }
            return $compiler->getVariables();
        }

        return [];
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
