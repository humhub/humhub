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
 * Record ids, repeated (`ids[]=1&ids[]=2`) or comma-separated (`ids=1,2`), at most `max` of
 * them — "only these" (`ids`) or, with `exclude`, "not these" (`exclude`). A client names the
 * records it displays, not every record there is. No UI.
 *
 * Applied by the `apply` callback (`fn(ListBuilder $list, int[] $ids, ListContext $context)`),
 * or, on a {@see QueryListBuilder}, on `column`. Ids never widen what the list shows.
 *
 * @since 1.20
 */
class IdsFilter extends ConfigurableFilter
{
    public const MAX_IDS = 100;

    public function __construct(
        string $key,
        protected readonly ?string $column = null,
        protected readonly bool $exclude = false,
        ?callable $apply = null,
        protected readonly int $max = self::MAX_IDS,
    ) {
        parent::__construct($key, $apply);
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

        $values = is_array($value) ? $value : explode(',', (string)$value);

        $ids = [];
        foreach ($values as $item) {
            $item = is_scalar($item) ? trim((string)$item) : '';
            if ($item === '') {
                continue;
            }

            $id = filter_var($item, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                return $this->invalid(Yii::t('yii', '{attribute} must be an integer.', ['attribute' => $this->key]));
            }
            $ids[] = $id;
        }

        $ids = array_values(array_unique($ids));
        if (count($ids) > $this->max) {
            return $this->invalid(Yii::t('base', 'At most {count} ids can be named.', ['count' => $this->max]));
        }

        return $ids === [] ? FilterValue::absent() : FilterValue::of($ids);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        if ($this->applyCallback($list, $value->value, $context) || $this->column === null || !$list instanceof QueryListBuilder) {
            return;
        }

        $list->query()->andWhere($this->exclude ? ['not in', $this->column, $value->value] : [$this->column => $value->value]);
    }
}
