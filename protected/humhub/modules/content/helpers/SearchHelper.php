<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\helpers;

use yii\base\BaseObject;

/**
 * SearchHelper
 *
 * @since 1.2.3
 * @author Luke
 */
class SearchHelper extends BaseObject
{
    /**
     * Checks if given text matches a search query.
     *
     * @param string $query
     * @param string $text
     * @return bool
     */
    public static function matchQuery($query, $text)
    {
        if ($text === null) {
            return false;
        }

        foreach (explode(' ', $query) as $keyword) {
            if ($keyword !== '' && strpos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }


}
