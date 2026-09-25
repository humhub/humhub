<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\filter;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * The filters of a page, as definitions the Vue `FilterBar` renders — inside a `CardDirectory`
 * (marketplace, later people and spaces) or on its own in a `PageToolbar` (module pages such as
 * files). The data-driven successor of the HTML-rendering {@see \humhub\widgets\DirectoryFilters}.
 *
 * A definition: `type` (`text`, `select`, `tags`, `checkbox`), `label`, and as needed
 * `placeholder`, `options` (`[{value, label, params?}]` — an option with `params` sends those
 * request parameters instead of its value, e.g. a "Status" option meaning `archived=1`), `optionsUrl` (a select loads further options
 * from an endpoint answering `{results: [{id, name, count?}]}`), `multiple` (tags), `default`,
 * `hidden` (URL-synced and sent, never rendered — context a link carries in, dropped as soon as
 * the user changes a visible filter), `wide`, `sortOrder`. The key is the query parameter the
 * value travels in, on the page URL and to the endpoint.
 *
 * Modules add or remove filters on {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
abstract class FilterSet extends Component
{
    public const EVENT_INIT = 'init';

    public const TYPES = ['text', 'select', 'tags', 'checkbox'];

    private array $filters = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initDefaultFilters();
        $this->trigger(self::EVENT_INIT);
    }

    abstract protected function initDefaultFilters(): void;

    /**
     * @throws InvalidConfigException for an unknown `type`
     */
    public function addFilter(string $key, array $definition): void
    {
        if (!in_array($definition['type'] ?? null, self::TYPES, true)) {
            throw new InvalidConfigException('Filter "' . $key . '" needs a type of: ' . implode(', ', self::TYPES));
        }

        $this->filters[$key] = $definition;
    }

    public function removeFilter(string $key): void
    {
        unset($this->filters[$key]);
    }

    public function getFilter(string $key): ?array
    {
        return $this->filters[$key] ?? null;
    }

    /**
     * The definitions in display order, each carrying its `key`, without `sortOrder`.
     */
    public function toArray(): array
    {
        $filters = [];
        foreach ($this->filters as $key => $definition) {
            $filters[] = $definition + ['key' => $key, 'sortOrder' => 1000];
        }

        ArrayHelper::multisort($filters, 'sortOrder');

        return array_map(static function (array $filter) {
            unset($filter['sortOrder']);
            return $filter;
        }, $filters);
    }
}
