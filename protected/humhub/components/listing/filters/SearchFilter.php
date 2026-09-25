<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing\filters;

use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use Yii;

/**
 * A free-text parameter — the list's search (`q`), or any other single string (the
 * marketplace's `id`). The value is trimmed; an empty one is absent.
 *
 * Applied by the `apply` callback (`fn(ListBuilder $list, string $value, ListContext $context)`),
 * or, on a {@see QueryListBuilder}, as a `LIKE` over `columns`; with neither, the value is only
 * read by the list itself (in its `finalize()`).
 *
 * ```php
 * new SearchFilter('q', columns: ['task.title', 'task.description'], definition: ['label' => Yii::t('TasksModule.base', 'Search'), 'sortOrder' => 100]);
 * ```
 *
 * @since 1.20
 */
class SearchFilter extends ConfigurableFilter
{
    /**
     * @param string[] $columns searched with `LIKE` (any of them matching) when there is no
     *        `apply` callback
     */
    public function __construct(
        string $key = 'q',
        protected readonly array $columns = [],
        ?callable $apply = null,
        ?array $definition = null,
    ) {
        parent::__construct($key, $apply, $definition);
    }

    /**
     * @inheritdoc
     */
    public function parse(array $raw, ListContext $context): FilterValue
    {
        $value = $raw[$this->key] ?? null;

        if ($value === null) {
            return FilterValue::absent();
        }

        if (!is_string($value)) {
            return $this->invalid(Yii::t('yii', '{attribute} must be a string.', ['attribute' => $this->key]));
        }

        $value = trim($value);

        return $value === '' ? FilterValue::absent() : FilterValue::of($value);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        if ($this->applyCallback($list, $value->value, $context) || $this->columns === [] || !$list instanceof QueryListBuilder) {
            return;
        }

        $condition = ['or'];
        foreach ($this->columns as $column) {
            $condition[] = ['like', $column, $value->value];
        }
        $list->query()->andWhere($condition);
    }
}
