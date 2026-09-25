<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use yii\base\Event;

/**
 * The event of {@see FilterableList::EVENT_INIT} (only `list` is set: add or remove filters and
 * sorts) and {@see FilterableList::EVENT_BUILD} (everything is set: restrict the `builder`).
 *
 * @since 1.20
 */
class ListEvent extends Event
{
    public ?FilterableList $list = null;

    /**
     * @var ListBuilder|null the list, every filter and the order applied — a handler narrows it
     *      further, it does not widen it
     */
    public ?ListBuilder $builder = null;

    public ?ListContext $context = null;

    /**
     * @var array<string, FilterValue> the parsed values of the available filters (and `sort`),
     *      by key
     */
    public array $values = [];

    /**
     * The value of a filter the caller sent, `null` if absent.
     */
    public function value(string $key): mixed
    {
        $value = $this->values[$key] ?? null;

        return $value !== null && $value->present ? $value->value : null;
    }
}
