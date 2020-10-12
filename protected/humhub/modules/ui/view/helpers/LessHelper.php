<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\helpers;

use humhub\modules\ui\view\components\Theme;

/**
 * LessHelper
 *
 * @since 1.3
 */
class LessHelper
{
    /**
     * @param Theme $theme
     * @return string
     */
    public static function getVariableFile(Theme $theme)
    {
        return $theme->getBasePath() . '/less/variables.less';
    }


    /**
     * Updates variables of a given file
     *
     * @param array $variables
     * @param $file
     */
    public static function updateVariables($variables, $file)
    {
        $content = file_get_contents($file);
        foreach ($variables as $key => $value) {
            // Try to update
            $count = 0;

            $firstChar = substr($value, 0, 1);
            if ($firstChar != '#' && !is_numeric($firstChar)) {
                $value = '"' . $value . '"';
            }

            $content = preg_replace('/@' . $key . ':\s?(.*?);/', '@' . $key . ': ' . $value . ";", $content, -1, $count);
            if ($count == 0) {
                $content .= "\n@" . $key . ": " . $value . ";";
            }
        }

        file_put_contents($file, $content);
    }


    /**
     * Returns all less variables of a given file
     *
     * @param $lessFile
     * @return array
     */
    public static function parseLessVariables($lessFile)
    {
        if (file_exists($lessFile)) {
            $variables = [];
            preg_match_all('/^@(.*?):\s?"?(.*?)"?;/m', file_get_contents($lessFile), $regexResult, PREG_SET_ORDER);
            foreach ($regexResult as $regexHit) {
                $variables[$regexHit[1]] = $regexHit[2];
            }
            return LessHelper::updateLinkedLessVariables($variables);
        }

        return [];
    }


    /**
     * Update values of less variables if they use value from another less variables, for example:
     *     @firstColor: #fff;
     *     @secondColor: @firstColor;
     *     @thirdColor: @secondColor;
     *
     * @param array $variables
     * @since 1.7
     * @return array $variables
     */
    public static function updateLinkedLessVariables($variables)
    {
        if (!is_array($variables)) {
            return [];
        }

        foreach ($variables as $name => $value) {
            if (substr($value, 0, 1) != '@') {
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
