<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\listing;

use Closure;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * A list (spaces, users, modules, tasks …) that owns its filters: "a list of X, narrowed by
 * filters, in an order". The HTTP API builds it from the request's query parameters
 * ({@see self::build()} parses and validates every one of them), the page gets the definitions
 * of the same filters ({@see self::definitions()}). Paging stays with the caller
 * (`BaseController::handlePagination()`).
 *
 * ```php
 * $query = (new SpaceList())->build(['q' => 'marketing', 'scope' => 'member'], ListContext::forCurrentUser())->query();
 * ```
 *
 * A list defines its {@see self::filters()}, its {@see self::sorts()}, the builder the filters
 * narrow ({@see self::createBuilder()}: the base with the rules that always apply, such as
 * visibility) and what it does after the filters ({@see self::finalize()}: the order, and
 * rules depending on several values).
 *
 * Besides the filters' parameters and `sort`, {@see self::RESERVED_PARAMS} (paging) and
 * `purpose` (validated against {@see self::purposes()}, it fills the context's purpose) are
 * accepted; any other parameter is refused, so a typo is not silently ignored.
 *
 * A filter parameter may be a bracket key (`fields[age]`, e.g. one filter per profile field):
 * {@see self::build()} turns the nested array PHP parses such a query parameter into
 * (`['fields' => ['age' => 'x']]`) back into `fields[age]` (one level; a list such as
 * `ids[]=1&ids[]=2` stays a list), so it is matched, and refused when unknown, by its full key.
 *
 * Modules extend a list on two events:
 *
 * ```php
 * ['class' => SpaceList::class, 'event' => SpaceList::EVENT_INIT, 'callback' => [Events::class, 'onSpaceListInit']],
 * ['class' => SpaceList::class, 'event' => SpaceList::EVENT_BUILD, 'callback' => [Events::class, 'onSpaceListBuild']],
 *
 * // one filter: parameter, validation, restriction and presentation
 * public static function onSpaceListInit(ListEvent $event)
 * {
 *     $event->list->addFilter(new EnumFilter('category', values: Category::labels(), apply: ..., definition: ['label' => ..., 'sortOrder' => 250]));
 * }
 *
 * // a plain restriction, after all filters
 * public static function onSpaceListBuild(ListEvent $event)
 * {
 *     if ($event->context->purpose === SpaceList::PURPOSE_DIRECTORY) {
 *         $event->builder->query()->andWhere(...);
 *     }
 * }
 * ```
 *
 * @since 1.20
 */
abstract class FilterableList extends Component
{
    /**
     * @event ListEvent triggered once per instance on init, with `list` — modules add and
     *        remove filters and sorts
     */
    public const EVENT_INIT = 'init';

    /**
     * @event ListEvent triggered by {@see self::build()} after every filter and the order were
     *        applied, with the `builder`, the `context` and the parsed `values` — for pure
     *        restrictions
     */
    public const EVENT_BUILD = 'build';

    public const SORT_PARAM = 'sort';

    public const PURPOSE_PARAM = 'purpose';

    /**
     * Parameters no filter may claim: the caller's paging.
     */
    public const RESERVED_PARAMS = ['page', 'pageSize'];

    /**
     * @var ListFilter[] by key, in the order they were added
     */
    private array $filters = [];

    /**
     * @var array<string, array{label: string|null, apply: Closure|null}>
     */
    private array $sortOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        foreach ($this->filters() as $filter) {
            $this->addFilter($filter);
        }
        foreach ($this->sorts() as $key => $label) {
            $this->addSort($key, $label);
        }

        $this->trigger(self::EVENT_INIT, new ListEvent(['list' => $this]));
    }

    /**
     * The list's own filters.
     *
     * @return ListFilter[]
     */
    abstract protected function filters(): array;

    /**
     * The list's own sort keys, applied by {@see self::finalize()}.
     *
     * @return array<string, string|null> the label of each key; `null` = accepted, but not
     *         offered in the sort select (e.g. `default`, the order without a sort)
     */
    protected function sorts(): array
    {
        return [];
    }

    /**
     * The purposes the list knows (the `purpose` parameter); empty = any purpose is accepted.
     *
     * @return string[]
     */
    public function purposes(): array
    {
        return [];
    }

    /**
     * The list the filters narrow, with the rules that always apply (visibility).
     */
    abstract protected function createBuilder(ListContext $context): ListBuilder;

    /**
     * What the list does after the filters: the order (`$values['sort']`), and rules that
     * depend on several values. This default applies the chosen sort if it came with its own
     * callback ({@see self::addSort()}).
     *
     * @param array<string, FilterValue> $values
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        $sort = $this->value($values, self::SORT_PARAM);

        if ($sort !== null) {
            $this->applySortCallback($builder, $sort, $context);
        }
    }

    /**
     * Applies the callback the sort `$sort` was added with ({@see self::addSort()}) — also one a
     * module added for a key of the list's own sorts, replacing the list's order for it.
     *
     * @return bool whether the sort has a callback (and it was applied)
     */
    protected function applySortCallback(ListBuilder $builder, string $sort, ListContext $context): bool
    {
        $apply = $this->sortOptions[$sort]['apply'] ?? null;

        if ($apply === null) {
            return false;
        }
        $apply($builder, $context);

        return true;
    }

    /**
     * Adds a filter, or replaces the one with the same key.
     *
     * @throws InvalidConfigException for a key or parameter the list reserves (`sort`,
     *         `purpose`, paging)
     */
    public function addFilter(ListFilter $filter): void
    {
        foreach ($filter->params() as $param) {
            if (in_array($param, [self::SORT_PARAM, self::PURPOSE_PARAM, ...self::RESERVED_PARAMS], true)) {
                throw new InvalidConfigException('The list parameter "' . $param . '" is reserved.');
            }
        }

        $this->filters[$filter->key()] = $filter;
    }

    public function removeFilter(string $key): void
    {
        unset($this->filters[$key]);
    }

    public function getFilter(string $key): ?ListFilter
    {
        return $this->filters[$key] ?? null;
    }

    /**
     * @return ListFilter[] by key
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Adds a sort key, or replaces the one with the same key.
     *
     * @param string|null $label the option of the sort select; `null` = accepted, not offered
     * @param callable|null $apply `fn(ListBuilder $builder, ListContext $context)`, for a sort a
     *        module adds — the list's own sorts are applied by {@see self::finalize()}
     */
    public function addSort(string $key, ?string $label = null, ?callable $apply = null): void
    {
        $this->sortOptions[$key] = ['label' => $label, 'apply' => $apply !== null ? Closure::fromCallable($apply) : null];
    }

    public function removeSort(string $key): void
    {
        unset($this->sortOptions[$key]);
    }

    /**
     * @return array<string, string|null> the label by sort key
     */
    public function getSorts(): array
    {
        return array_map(static fn(array $sort) => $sort['label'], $this->sortOptions);
    }

    /**
     * Parses and validates every parameter, then builds the list: the base
     * ({@see self::createBuilder()}), every filter with a present value in the order the
     * filters were added, {@see self::finalize()} and {@see self::EVENT_BUILD}.
     *
     * @param array<string, mixed> $params the query parameters (from a request) or typed values
     *        (from PHP); empty values are absent; an associative array value is a set of
     *        bracket keys (`['fields' => ['age' => 'x']]` = `fields[age]`)
     * @throws ListValidationException with the messages by parameter — for an invalid value, a
     *         parameter of an unavailable filter and a parameter the list does not know
     */
    public function build(array $params, ListContext $context): ListBuilder
    {
        $errors = [];
        $known = self::RESERVED_PARAMS;
        $params = $this->flattenParams($params);

        $context = $this->parsePurpose($params, $context, $errors);
        $known[] = self::PURPOSE_PARAM;

        $values = [];
        foreach ($this->filters as $key => $filter) {
            $raw = array_intersect_key($params, array_flip($filter->params()));
            $known = array_merge($known, $filter->params());

            if (!$filter->isAvailable($context)) {
                // Empty values are absent here too - only a set value is refused.
                foreach ($raw as $param => $value) {
                    if ($value !== null && $value !== '' && $value !== []) {
                        $errors[$param][] = Yii::t('base', 'This filter is not available.');
                    }
                }
                continue;
            }

            $value = $filter->parse($raw, $context);
            if (!$value->isValid()) {
                $errors = array_merge_recursive($errors, $value->errors);
                continue;
            }
            $values[$key] = $value;
        }

        if ($this->sortOptions !== []) {
            $known[] = self::SORT_PARAM;
            $values[self::SORT_PARAM] = $this->parseSort($params[self::SORT_PARAM] ?? null, $errors);
        }

        foreach (array_keys($params) as $param) {
            if (!in_array((string)$param, $known, true)) {
                $errors[$param][] = Yii::t('base', 'Unknown parameter.');
            }
        }

        if ($errors !== []) {
            throw new ListValidationException($errors);
        }

        $builder = $this->createBuilder($context);

        foreach ($this->filters as $key => $filter) {
            if (isset($values[$key]) && $values[$key]->present) {
                $filter->apply($builder, $values[$key], $context);
            }
        }

        $this->finalize($builder, $values, $context);

        $this->trigger(self::EVENT_BUILD, new ListEvent([
            'list' => $this,
            'builder' => $builder,
            'context' => $context,
            'values' => $values,
        ]));

        return $builder;
    }

    /**
     * The definitions of the available filters that have one, in their `sortOrder`, plus the
     * `sort` select when the list offers sorts — the `filters` prop of a page's `FilterBar`
     * (see {@see FilterDefinition::toArray()}).
     */
    public function definitions(ListContext $context): array
    {
        $definitions = [];
        foreach ($this->filters as $key => $filter) {
            $definition = $filter->isAvailable($context) ? $filter->definition($context) : null;
            if ($definition !== null) {
                $definitions[] = $definition->withKey($key);
            }
        }

        $sort = $this->sortDefinition($context);
        if ($sort !== null) {
            $definitions[] = $sort->withKey(self::SORT_PARAM);
        }

        // Stable: equal sort orders keep the order the filters were added in.
        usort($definitions, static fn(FilterDefinition $a, FilterDefinition $b) => $a->sortOrder <=> $b->sortOrder);

        return array_map(static fn(FilterDefinition $definition) => $definition->toArray(), $definitions);
    }

    /**
     * The sort select: the offered sorts ({@see self::getSorts()} with a label) as options. No
     * option for the order without a sort — the select's label is that state, as "all" is for
     * a filter select.
     */
    protected function sortDefinition(ListContext $context): ?FilterDefinition
    {
        $options = [];
        foreach ($this->getSorts() as $key => $label) {
            if ($label !== null) {
                $options[] = ['value' => (string)$key, 'label' => $label];
            }
        }

        return $options === [] ? null : new FilterDefinition(
            type: 'select',
            label: $this->sortLabel(),
            options: $options,
            sortOrder: 200,
        );
    }

    /**
     * The label of the sort select — a list overrides it to keep its module's translation.
     */
    protected function sortLabel(): string
    {
        return Yii::t('base', 'Sort');
    }

    /**
     * The present value of a filter, `null` if absent.
     *
     * @param array<string, FilterValue> $values
     */
    protected function value(array $values, string $key): mixed
    {
        $value = $values[$key] ?? null;

        return $value !== null && $value->present ? $value->value : null;
    }

    /**
     * PHP parses `fields[age]=x` into `['fields' => ['age' => 'x']]`: one level of associative
     * array parameters back into bracket keys (`fields[age]`), the keys filters claim. A list
     * (`ids[]=1&ids[]=2`) stays the value of its parameter.
     */
    private function flattenParams(array $params): array
    {
        $flat = [];
        foreach ($params as $param => $value) {
            if (is_array($value) && $value !== [] && !array_is_list($value)) {
                foreach ($value as $key => $nested) {
                    $flat[$param . '[' . $key . ']'] = $nested;
                }
            } else {
                $flat[$param] = $value;
            }
        }

        return $flat;
    }

    private function parseSort(mixed $raw, array &$errors): FilterValue
    {
        if ($raw === null || $raw === '') {
            return FilterValue::absent();
        }

        if (!is_string($raw) || !array_key_exists($raw, $this->sortOptions)) {
            $errors[self::SORT_PARAM][] = Yii::t('base', 'Unknown value "{value}".', ['value' => is_string($raw) ? $raw : '']);

            return FilterValue::absent();
        }

        return FilterValue::of($raw);
    }

    /**
     * A `purpose` parameter fills the context's purpose, unless the context has one.
     */
    private function parsePurpose(array $params, ListContext $context, array &$errors): ListContext
    {
        $raw = $params[self::PURPOSE_PARAM] ?? null;

        if ($raw === null || $raw === '') {
            return $context;
        }

        $purposes = $this->purposes();
        if (!is_string($raw) || ($purposes !== [] && !in_array($raw, $purposes, true))) {
            $errors[self::PURPOSE_PARAM][] = Yii::t('base', 'Unknown value "{value}".', ['value' => is_string($raw) ? $raw : '']);

            return $context;
        }

        return $context->purpose === null ? $context->withPurpose($raw) : $context;
    }
}
