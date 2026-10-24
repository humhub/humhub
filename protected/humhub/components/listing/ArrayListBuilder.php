<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

/**
 * A list of items that are not in the database (the marketplace's modules from humhub.com):
 * the filters narrow the array, the caller pages it. Keys are kept throughout.
 *
 * @since 1.20
 */
class ArrayListBuilder implements ListBuilder
{
    public function __construct(private array $items = [])
    {
    }

    public function items(): array
    {
        return $this->items;
    }

    public function setItems(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Keeps the items the predicate (`fn($item): bool`) accepts.
     */
    public function filter(callable $predicate): static
    {
        $this->items = array_filter($this->items, $predicate);

        return $this;
    }

    /**
     * Orders the items (`fn($a, $b): int`); items the comparison finds equal keep their order.
     */
    public function sort(callable $comparison): static
    {
        uasort($this->items, $comparison);

        return $this;
    }
}
