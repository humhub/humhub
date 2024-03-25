<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\modules\content\models\Content;
use yii\data\Pagination;

/**
 * SearchResultSet
 *
 * @author luke
 */
class ResultSet
{
    /**
     * @var Content[]
     */
    public $results = [];

    /**
     * @var Pagination
     */
    public $pagination;
}
