<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\models;

use yii\db\ActiveQuery;

abstract class QueryFilter extends Filter
{
    /**
     * @var ActiveQuery
     */
    public $query;
}
