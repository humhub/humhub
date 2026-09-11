<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\models\filter;

use yii\db\ActiveQuery;

abstract class QueryFilter extends Filter
{
    /**
     * @var ActiveQuery
     */
    public $query;
}
