<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing\filters;

use Closure;
use humhub\components\listing\FilterDefinition;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListFilter;

/**
 * The common part of the ready filters: a key, an `apply` callback and an optional
 * presentation.
 *
 * - `apply`: `fn(ListBuilder $list, mixed $value, ListContext $context): void`, called with the
 *   parsed value (a string, a bool, a list …) when the caller sent the filter
 * - `definition`: the named arguments of {@see FilterDefinition} (`label`, `placeholder`,
 *   `sortOrder`, `placement`, `optionsUrl`, `hidden`, …) over the filter's own defaults (its
 *   `type`, the options of an {@see EnumFilter}); `null` = a filter without UI
 *
 * @since 1.20
 */
abstract class ConfigurableFilter extends ListFilter
{
    protected ?Closure $apply;

    public function __construct(
        protected readonly string $key,
        ?callable $apply = null,
        protected readonly ?array $definition = null,
    ) {
        $this->apply = $apply !== null ? Closure::fromCallable($apply) : null;
    }

    /**
     * @inheritdoc
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function definition(ListContext $context): ?FilterDefinition
    {
        if ($this->definition === null) {
            return null;
        }

        return new FilterDefinition(...array_merge($this->defaultDefinition($context), $this->definition));
    }

    /**
     * The named arguments of the definition the filter brings itself.
     */
    protected function defaultDefinition(ListContext $context): array
    {
        return ['type' => 'text'];
    }

    /**
     * Runs the `apply` callback, if there is one.
     *
     * @return bool whether there was one
     */
    protected function applyCallback(ListBuilder $list, mixed $value, ListContext $context): bool
    {
        if ($this->apply === null) {
            return false;
        }

        ($this->apply)($list, $value, $context);

        return true;
    }
}
