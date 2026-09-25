<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing\filters;

use Closure;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use yii\base\InvalidConfigException;

/**
 * A value out of a set — a select, or with `multiple` a list of values (repeated,
 * `status[]=a&status[]=b`, or comma-separated, `status=a,b`) that the list matches any of.
 *
 * The set is either fixed (`values`, each with the label of its select option; a `null` label
 * is accepted but not offered, e.g. `all`) or open (`pattern`, a regular expression each value
 * must match — for values a remote source defines, such as the marketplace's use cases, whose
 * options then come from an `optionsUrl`). Anything else answers "Unknown value".
 *
 * Applied by the `apply` callback (`fn(ListBuilder $list, string|string[] $value,
 * ListContext $context)`); a single-valued filter may map single values to their own callback
 * instead (`applyMap`: `value => fn(ListBuilder $list, ListContext $context)`) — the spaces'
 * "Archived" status is such a value.
 *
 * ```php
 * new EnumFilter('priority', values: ['high' => Yii::t(…, 'High'), 'low' => Yii::t(…, 'Low')],
 *     apply: static fn(QueryListBuilder $list, string $priority) => $list->query()->andWhere(['task.priority' => $priority]),
 *     definition: ['label' => Yii::t(…, 'Priority'), 'sortOrder' => 300]);
 * ```
 *
 * The definition defaults to a `select` with the labelled values as options.
 *
 * @since 1.20
 */
class EnumFilter extends ConfigurableFilter
{
    /**
     * @var array<string, Closure>
     */
    protected array $applyMap = [];

    /**
     * @param array<string|int, string|null> $values the accepted values with their option labels
     * @param array<string, callable> $applyMap single values applied by their own callback
     * @throws InvalidConfigException without `values` and `pattern`
     */
    public function __construct(
        string $key,
        protected readonly array $values = [],
        ?callable $apply = null,
        array $applyMap = [],
        protected readonly bool $multiple = false,
        protected readonly ?string $pattern = null,
        ?array $definition = null,
    ) {
        if ($values === [] && $pattern === null) {
            throw new InvalidConfigException('The filter "' . $key . '" needs values or a pattern.');
        }

        parent::__construct($key, $apply, $definition);

        foreach ($applyMap as $value => $callback) {
            $this->applyMap[(string)$value] = Closure::fromCallable($callback);
        }
    }

    /**
     * @return string[] the accepted values of a fixed set
     */
    public function getValues(): array
    {
        return array_map('strval', array_keys($this->values));
    }

    /**
     * @inheritdoc
     */
    public function parse(array $raw, ListContext $context): FilterValue
    {
        $value = $raw[$this->key] ?? null;

        if ($value === null || $value === '' || $value === []) {
            return FilterValue::absent();
        }

        if (!$this->multiple) {
            if ((!is_string($value) && !is_int($value)) || !$this->accepts((string)$value)) {
                return $this->invalid($this->unknownValue($value));
            }

            return FilterValue::of((string)$value);
        }

        $values = is_array($value) ? $value : explode(',', (string)$value);
        $values = array_values(array_unique(array_filter(
            array_map(static fn($item) => is_scalar($item) ? trim((string)$item) : '', $values),
            static fn(string $item) => $item !== '',
        )));

        $messages = [];
        foreach ($values as $item) {
            if (!$this->accepts($item)) {
                $messages[] = $this->unknownValue($item);
            }
        }

        if ($messages !== []) {
            return FilterValue::invalid([$this->key => $messages]);
        }

        return $values === [] ? FilterValue::absent() : FilterValue::of($values);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        if (!$this->multiple && isset($this->applyMap[$value->value])) {
            ($this->applyMap[$value->value])($list, $context);

            return;
        }

        $this->applyCallback($list, $value->value, $context);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDefinition(ListContext $context): array
    {
        $options = [];
        foreach ($this->values as $value => $label) {
            if ($label !== null) {
                $options[] = ['value' => (string)$value, 'label' => $label];
            }
        }

        return array_filter(['type' => 'select', 'options' => $options !== [] ? $options : null], static fn($item) => $item !== null);
    }

    protected function accepts(string $value): bool
    {
        if ($this->values !== []) {
            return in_array($value, $this->getValues(), true);
        }

        return preg_match($this->pattern, $value) === 1;
    }
}
