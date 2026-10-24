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
use Yii;

/**
 * A flag: `1` or `0` (from PHP also `true`/`false`); empty is absent. Applied by the `apply`
 * callback (`fn(ListBuilder $list, bool $value, ListContext $context)`) with either value the
 * caller sent. The definition defaults to a `checkbox`.
 *
 * @since 1.20
 */
class BoolFilter extends ConfigurableFilter
{
    /**
     * @inheritdoc
     */
    public function parse(array $raw, ListContext $context): FilterValue
    {
        $value = $raw[$this->key] ?? null;

        if ($value === null || $value === '') {
            return FilterValue::absent();
        }

        if (is_bool($value)) {
            return FilterValue::of($value);
        }

        if (in_array($value, ['1', 1], true)) {
            return FilterValue::of(true);
        }

        if (in_array($value, ['0', 0], true)) {
            return FilterValue::of(false);
        }

        return $this->invalid(Yii::t('yii', '{attribute} must be either "{true}" or "{false}".', [
            'attribute' => $this->key,
            'true' => '1',
            'false' => '0',
        ]));
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        $this->applyCallback($list, $value->value, $context);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDefinition(ListContext $context): array
    {
        return ['type' => 'checkbox'];
    }
}
