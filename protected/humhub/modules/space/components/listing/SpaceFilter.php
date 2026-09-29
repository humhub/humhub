<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components\listing;

use humhub\components\listing\FilterDefinition;
use humhub\components\listing\filters\ConfigurableFilter;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\space\components\SpaceList;
use Yii;

/**
 * One or several spaces (default parameter `spaceId`) — a list's "Space" filter: the value is
 * the id (or ids) of a space the caller may see, presented as the `space` filter type of the
 * `FilterBar` (a space search with suggestions from `GET /api/v2/space?purpose=picker`, the
 * space module's `SpaceFilterControl`).
 *
 * ```php
 * new SpaceFilter('spaceId', column: 'contentcontainer.pk', definition: ['label' => Yii::t('SpaceModule.base', 'Space'), 'props' => ['scope' => 'member'], 'sortOrder' => 110]);
 * ```
 *
 * Repeated (`spaceId[]=1&spaceId[]=2`) or comma-separated (`spaceId=1,2`), at most
 * {@see IdsFilter::MAX_IDS} of them, parsed by an {@see IdsFilter} for the same key. Every id
 * must be one the {@see SpaceList} shows the caller as a picker: every space the caller may see
 * ({@see SpaceList::PURPOSE_PICKER}, {@see SpaceList::SCOPE_ALL}), in the same `purpose=picker`
 * context the control's suggestions are searched in, so a module restricting one restricts the
 * other. An archived space is left out too, like the directory does by default: the picker does
 * not offer it either. Any other id, and an id of nobody, is "Space not found.", so a caller
 * cannot probe which spaces exist; what is no id at all is refused as such. The value is `int[]`
 * (unique, sorted). With `multiple: false` exactly one id is accepted, and the value is that id
 * (an int). Not available to guests ({@see self::isAvailable()}).
 *
 * Applied by the `apply` callback (`fn(ListBuilder $list, int|int[] $value, ListContext $context)`),
 * or, on a {@see QueryListBuilder}, as `column IN (<ids>)` (or `column = <id>` with
 * `multiple: false`). The definition's `type` is always `space` and its `multiple` always the
 * constructor's, whatever the definition says — the value is a shape only this control
 * produces —, its label defaults to "Space". A control setting of its own (the space picker
 * control's `scope`, say) travels in the definition's `props`, read by the control verbatim
 * ({@see \humhub\components\listing\FilterDefinition}) — not `options`, which is the core
 * select/picker/tags choice list.
 *
 * @since 1.20
 */
class SpaceFilter extends ConfigurableFilter
{
    public const TYPE = 'space';

    public function __construct(
        string $key = 'spaceId',
        protected readonly ?string $column = null,
        ?callable $apply = null,
        ?array $definition = null,
        protected readonly bool $multiple = true,
    ) {
        parent::__construct($key, $apply, $definition);
    }

    /**
     * Not for guests: `GET /api/v2/space` has no guest access — a guest gets no definition, and
     * a guest's value is refused.
     *
     * @inheritdoc
     */
    public function isAvailable(ListContext $context): bool
    {
        return $context->user !== null;
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
            $id = is_int($value) || is_string($value)
                ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;

            if ($id === false) {
                return $this->invalid(Yii::t('yii', '{attribute} must be an integer.', ['attribute' => $this->key]));
            }

            if (!$this->areListed([$id], $context)) {
                return $this->invalid(Yii::t('SpaceModule.base', 'Space not found.'));
            }

            return FilterValue::of($id);
        }

        // Repeated or comma-separated, at most IdsFilter::MAX_IDS, its own errors included: an
        // IdsFilter for the same key does the parsing, this only sorts and checks visibility.
        $ids = (new IdsFilter($this->key))->parse($raw, $context);
        if (!$ids->present) {
            // Absent, or invalid (bad format, too many) - either way, IdsFilter's own value.
            return $ids;
        }

        $values = $ids->value;
        sort($values);

        if (!$this->areListed($values, $context)) {
            return $this->invalid(Yii::t('SpaceModule.base', 'Space not found.'));
        }

        return FilterValue::of($values);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        if ($this->applyCallback($list, $value->value, $context) || $this->column === null || !$list instanceof QueryListBuilder) {
            return;
        }

        $list->query()->andWhere([$this->column => $value->value]);
    }

    /**
     * @inheritdoc
     */
    public function definition(ListContext $context): ?FilterDefinition
    {
        if ($this->definition === null) {
            return null;
        }

        // The type and multiple are the filter's, whatever the definition says: a definition
        // cannot turn the filter into a control that sends a shape the parser does not accept.
        return new FilterDefinition(...array_merge(
            $this->defaultDefinition($context),
            $this->definition,
            ['type' => self::TYPE, 'multiple' => $this->multiple],
        ));
    }

    /**
     * @inheritdoc
     */
    protected function defaultDefinition(ListContext $context): array
    {
        return ['type' => self::TYPE, 'label' => Yii::t('SpaceModule.base', 'Space'), 'multiple' => $this->multiple];
    }

    /**
     * Whether the space list of a picker shows the context's user every one of `$ids` — built as
     * the list itself, so the rules of the suggestions (visibility, blocked spaces, the
     * restrictions modules add) are the rules of the value, never a copy of them.
     *
     * The context is the one the control's suggestions are searched in (`GET /api/v2/space`,
     * `ListContext::forCurrentUser(SpaceList::PURPOSE_PICKER)`), with the widest scope
     * ({@see SpaceList::SCOPE_ALL}): the caller and the picker purpose, without the host list's
     * purpose and container — a module restricting those must not refuse a space the control
     * offered. The control's own suggestions may narrow further by `scope` (the definition's
     * `props.scope`, `member` by default) — always a subset of `all` — so anything it could
     * suggest still validates here.
     */
    private function areListed(array $ids, ListContext $context): bool
    {
        // Counted by distinct space.id: a module's own EVENT_BUILD restriction may join a
        // one-to-many relation, whose duplicate rows must not multiply a valid id into a refusal.
        $count = (new SpaceList())
            ->build(['ids' => $ids, 'scope' => SpaceList::SCOPE_ALL], new ListContext($context->user, SpaceList::PURPOSE_PICKER))
            ->query()
            ->count('DISTINCT space.id');

        return (int)$count === count($ids);
    }
}
