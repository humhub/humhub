<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\table;

use Closure;

/**
 * A column of a {@see DataTable}: what the `DataTable` Vue component shows in it and how — see
 * docs/ui/data-table.md, "Column types".
 *
 * - `main`: image, linked name and subtitle; always visible
 * - `chip`: one or several neutral badges (`[{text, value}]`), each a button setting the column's
 *   `filter` when it has one; or a status badge (`{text, tone}`)
 * - `number`, `date`, `text`: one value (`{text}`; a date also `{value, title}`)
 *
 * The cells come from the table ({@see DataTable::cells()}); a column a module adds brings its own
 * cell with `value` (`fn($model): ?array`). A column other than the main one is optional: shown
 * by default (`visible`) or on request, in the table's column chooser.
 *
 * @since 1.20
 */
class ColumnDefinition
{
    public const TYPE_MAIN = 'main';
    public const TYPE_CHIP = 'chip';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DATE = 'date';
    public const TYPE_TEXT = 'text';

    public readonly ?Closure $value;

    /**
     * @param int $priority 1 = most important; when the table gets too narrow, the column with
     *        the highest number folds into the row details first (the main column never does)
     * @param string|null $sort the sort key of the table's list (`key`, `-key` descending), when
     *        the column is sortable
     * @param string|null $filter the filter key a chip of this column sets when pressed
     * @param callable|null $value `fn($model): ?array` the cell, for a column a module adds
     * @param bool $visible whether the column is shown until the user chooses the columns
     */
    public function __construct(
        public readonly string $key,
        public readonly string $type,
        public readonly string $label,
        public readonly int $priority = 1,
        public readonly ?string $sort = null,
        public readonly ?string $filter = null,
        public readonly int $sortOrder = 1000,
        ?callable $value = null,
        public readonly bool $visible = true,
    ) {
        $this->value = $value !== null ? Closure::fromCallable($value) : null;
    }

    /**
     * The definition the `DataTable` component takes (its `columns` prop).
     */
    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'priority' => $this->priority,
            'sort' => $this->sort,
            'filter' => $this->filter,
            'visible' => $this->visible ? null : false,
        ], static fn($item) => $item !== null);
    }
}
