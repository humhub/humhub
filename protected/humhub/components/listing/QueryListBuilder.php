<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use yii\db\ActiveQuery;
use yii\db\Expression;

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

    /**
     * Orders by the given columns (column names, or {@see Expression}s for computed values),
     * empty values last in both directions, then by `$tieBreaker` in the same direction, so equal
     * rows keep a stable order across pages (docs/ui/data-table.md, "Sorting").
     *
     * @param array<string|Expression> $columns
     */
    public function sortBy(array $columns, bool $descending, string $tieBreaker): static
    {
        $direction = $descending ? SORT_DESC : SORT_ASC;
        $order = [];
        foreach ($columns as $column) {
            if ($column instanceof Expression) {
                $order[] = new Expression($column->expression . ($descending ? ' DESC' : ' ASC'), $column->params);
                continue;
            }
            $order[] = new Expression('[[' . $column . ']] IS NULL');
            $order[$column] = $direction;
        }
        $order[$tieBreaker] = $direction;
        $this->query->orderBy($order);

        return $this;
    }
}
