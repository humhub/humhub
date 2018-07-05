<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;


use humhub\modules\stream\models\StreamQuery;
use humhub\modules\ui\filter\models\QueryFilter;

abstract class StreamQueryFilter extends QueryFilter
{
    /**
     * @var StreamQuery
     */
    public $streamQuery;

    public $autoLoad = self::AUTO_LOAD_GET;
}
