<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\libs;

use yii\base\Object;

/**
 * SearchHelper
 *
 * @since 1.2.3
 * @author Luke
 */
class SearchHelper extends Object
{

    /**
     * Checks if given text matches a search query.
     * 
     * @param string $query
     * @param string $text
     */
    public static function matchQuery($query, $text)
    {
        foreach (explode(" ", $query) as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

}
