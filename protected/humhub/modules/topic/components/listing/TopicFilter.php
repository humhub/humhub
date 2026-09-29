<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\components\listing;

use humhub\components\listing\FilterDefinition;
use humhub\components\listing\filters\ConfigurableFilter;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\topic\components\TopicList;
use Yii;

/**
 * One or several topics (default parameter `topicId`) — a content list's "Topic" filter: the
 * value is the ids of topics the caller may see, presented as the `topic` filter type of the
 * `FilterBar` (a topic search with suggestions from `GET /api/v2/topic/picker`, the topic
 * module's `TopicFilterControl`).
 *
 * ```php
 * new TopicFilter('topicId', definition: ['sortOrder' => 120], containerId: $space->contentcontainer_id);
 * ```
 *
 * Repeated (`topicId[]=1&topicId[]=2`) or comma-separated (`topicId=1,2`), at most
 * {@see self::MAX_IDS} of them — the most `ids` the picker endpoint resolves, so the control can
 * always name a chosen set —, parsed by an {@see IdsFilter} for the same key. Every id must be
 * one the {@see TopicList} shows the caller: a global topic, one of a space the caller may see
 * or of their own profile — with a `containerId` only that container's topics and the global
 * ones, the same choice the control's suggestions are searched in. Without a `containerId` the
 * context's container ({@see ListContext::$container}, the space or profile a list lives in) is
 * that container; an explicit `containerId` wins, and without either every visible topic
 * counts. Any other id, and an id of no topic, is "Topic not found.", so a caller cannot probe
 * which topics exist; what is no id at all is refused as such. The value is `int[]` (unique,
 * sorted) — always several: a content has any number of topics. Not available to guests
 * ({@see self::isAvailable()}).
 *
 * Applied by the `apply` callback (`fn(ListBuilder $list, int[] $value, ListContext $context)`),
 * or, on a {@see QueryListBuilder} over content (a query that has the `content` table, as every
 * content query — {@see \humhub\modules\content\components\ActiveQueryContent} — has), as
 * `content.id IN (<the content of any of the topics>)` — content with ANY of the chosen topics
 * matches (OR), a subquery on {@see ContentTagRelation}, so a content with several of them is
 * not listed twice. The definition's `type` is always `topic` and its `multiple` always `true`,
 * whatever the definition says — the value is a shape only this control produces —, its label
 * defaults to "Topic". The container (the `containerId`, else the context's) travels to the
 * control in the definition's `props` (`props.containerId`, merged with the definition's own
 * props — {@see FilterDefinition}), so it suggests what this filter accepts.
 *
 * A page whose filter bar has a `topic` filter needs the topic module's Vue bundle, which
 * registers the control: `TopicVueAsset` in its asset bundle's `$depends`, as a page with a
 * `user` filter lists `UserVueAsset`.
 *
 * @since 1.20
 */
class TopicFilter extends ConfigurableFilter
{
    public const TYPE = 'topic';

    /**
     * The most ids the filter takes: the most the {@see TopicList} takes, so the ids
     * `GET /api/v2/topic/picker` resolves on one page.
     */
    public const MAX_IDS = TopicList::MAX_IDS;

    public function __construct(
        string $key = 'topicId',
        ?callable $apply = null,
        ?array $definition = null,
        protected readonly ?int $containerId = null,
    ) {
        parent::__construct($key, $apply, $definition);
    }

    /**
     * Not for guests: `GET /api/v2/topic/picker` has no guest access — a guest gets no
     * definition, and a guest's value is refused.
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
        // Repeated or comma-separated, at most MAX_IDS, its own errors included: an IdsFilter
        // for the same key does the parsing, this only sorts and checks visibility.
        $ids = (new IdsFilter($this->key, max: self::MAX_IDS))->parse($raw, $context);
        if (!$ids->present) {
            // Absent, or invalid (bad format, too many) - either way, IdsFilter's own value.
            return $ids;
        }

        $values = $ids->value;
        sort($values);

        if (!$this->areListed($values, $this->containerIdOf($context), $context)) {
            return $this->invalid(Yii::t('TopicModule.base', 'Topic not found.'));
        }

        return FilterValue::of($values);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        if ($this->applyCallback($list, $value->value, $context) || !$list instanceof QueryListBuilder) {
            return;
        }

        $list->query()->andWhere(['content.id' => ContentTagRelation::find()
            ->select('content_tag_relation.content_id')
            ->where(['content_tag_relation.tag_id' => $value->value])]);
    }

    /**
     * @inheritdoc
     */
    public function definition(ListContext $context): ?FilterDefinition
    {
        if ($this->definition === null) {
            return null;
        }

        $props = $this->definition['props'] ?? [];
        $containerId = $this->containerIdOf($context);
        if ($containerId !== null) {
            $props['containerId'] = $containerId;
        }

        // The type and multiple are the filter's, whatever the definition says: a definition
        // cannot turn the filter into a control that sends a shape the parser does not accept.
        return new FilterDefinition(...array_merge(
            $this->defaultDefinition($context),
            $this->definition,
            ['type' => self::TYPE, 'multiple' => true, 'props' => $props],
        ));
    }

    /**
     * @inheritdoc
     */
    protected function defaultDefinition(ListContext $context): array
    {
        return ['type' => self::TYPE, 'label' => Yii::t('TopicModule.base', 'Topic'), 'multiple' => true];
    }

    /**
     * The container whose topics (and the global ones) the filter offers and takes: its own
     * `containerId`, else the context's container (the space or profile the list lives in),
     * else none — every topic the caller may see.
     */
    private function containerIdOf(ListContext $context): ?int
    {
        if ($this->containerId !== null) {
            return $this->containerId;
        }

        return $context->container !== null ? (int)$context->container->contentcontainer_id : null;
    }

    /**
     * Whether the topic list shows the context's user every one of `$ids` — built as the list
     * itself (with the filter's container, {@see self::containerIdOf()}), so the rules of the
     * suggestions are the rules of the value, never a copy of them. A container the caller may
     * not read lists nothing.
     */
    private function areListed(array $ids, ?int $containerId, ListContext $context): bool
    {
        $params = ['ids' => $ids];
        if ($containerId !== null) {
            $params['containerId'] = $containerId;
        }

        try {
            $query = (new TopicList())->build($params, new ListContext($context->user))->query();
        } catch (ListValidationException) {
            return false;
        }

        // Counted by distinct id: a module's own restriction may join a one-to-many relation.
        return (int)$query->orderBy(null)->count('DISTINCT content_tag.id') === count($ids);
    }
}
