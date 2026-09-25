<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use yii\base\InvalidConfigException;

/**
 * How a filter is presented — what the Vue `FilterBar` renders, handed to a page as the
 * `filters` prop by {@see FilterableList::definitions()}.
 *
 * - `type`: the filter type of the kit's registry — `text` (a search field), `select`, `tags`,
 *   `checkbox`, or a type a module registers itself
 * - `label`, `placeholder`
 * - `options` (`[{value, label}]`) and/or `optionsUrl` (a select loads further options from an
 *   endpoint answering `{results: [{id, name, count?}]}`)
 * - `multiple` (tags), `default`, `wide`
 * - `hidden`: URL-synced and sent, never rendered — context a link carries in (the
 *   marketplace's `id`), dropped as soon as a visible filter changes
 * - `placement`: `primary` (the filter row, the default) or `panel` (behind "Show filters")
 * - `sortOrder`: the position among the list's definitions, not part of {@see self::toArray()}
 *
 * The key is the filter's ({@see ListFilter::key()}): the query parameter the value travels in,
 * on the page URL and to the endpoint. {@see FilterableList::definitions()} sets it.
 *
 * @since 1.20
 */
final class FilterDefinition
{
    public const PLACEMENT_PRIMARY = 'primary';
    public const PLACEMENT_PANEL = 'panel';

    /**
     * @param array{value: string, label: string}[]|null $options
     * @throws InvalidConfigException for an empty type or an unknown placement
     */
    public function __construct(
        public readonly string $type,
        public readonly ?string $label = null,
        public readonly ?string $placeholder = null,
        public readonly ?array $options = null,
        public readonly ?string $optionsUrl = null,
        public readonly ?bool $multiple = null,
        public readonly mixed $default = null,
        public readonly ?bool $hidden = null,
        public readonly ?bool $wide = null,
        public readonly string $placement = self::PLACEMENT_PRIMARY,
        public readonly int $sortOrder = 1000,
        public readonly ?string $key = null,
    ) {
        if ($type === '') {
            throw new InvalidConfigException('A filter definition needs a type.');
        }
        if (!in_array($placement, [self::PLACEMENT_PRIMARY, self::PLACEMENT_PANEL], true)) {
            throw new InvalidConfigException('Unknown filter placement "' . $placement . '".');
        }
    }

    public function withKey(string $key): self
    {
        return new self(
            $this->type,
            $this->label,
            $this->placeholder,
            $this->options,
            $this->optionsUrl,
            $this->multiple,
            $this->default,
            $this->hidden,
            $this->wide,
            $this->placement,
            $this->sortOrder,
            $key,
        );
    }

    /**
     * The definition as the kit reads it: `{key, type, label?, placeholder?, options?,
     * optionsUrl?, multiple?, default?, hidden?, wide?, placement}` in this order, without the
     * keys that are `null`.
     */
    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'options' => $this->options,
            'optionsUrl' => $this->optionsUrl,
            'multiple' => $this->multiple,
            'default' => $this->default,
            'hidden' => $this->hidden,
            'wide' => $this->wide,
            'placement' => $this->placement,
        ], static fn($value) => $value !== null);
    }
}
