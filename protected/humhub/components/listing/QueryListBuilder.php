<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use yii\db\ActiveQuery;

/**
 * A database list: the filters narrow the query (`$list->query()->andWhere(...)`), the caller
 * pages and runs it.
 *
 * @since 1.20
 */
class QueryListBuilder implements ListBuilder
{
    public function __construct(private readonly ActiveQuery $query)
    {
    }

    public function query(): ActiveQuery
    {
        return $this->query;
    }
}
