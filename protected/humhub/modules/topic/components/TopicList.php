<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\components;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\space\components\SpaceList;
use humhub\modules\topic\components\listing\TopicContainerFilter;
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;

/**
 * The topics a caller may see, searched, named or of one container — `GET /api/v2/topic/picker`
 * is built with it, the suggestions and the URL values of a topic filter:
 *
 * ```php
 * $query = (new TopicList())->build(['q' => 'news', 'containerId' => 5], ListContext::forCurrentUser())->query();
 * ```
 *
 * Parameters: `q` (a part of the name, at most {@see self::MAX_SEARCH_LENGTH} characters),
 * `ids` (repeated or comma-separated topic ids, at most {@see self::MAX_IDS}) and
 * `containerId` (a content container id: that container's topics and the global ones, the
 * choice of a topic filter inside a space or profile, {@see TopicContainerFilter}). Ordered by
 * name, the id breaking ties — no `sort`.
 *
 * Only topics ({@see Topic}: `module_id` `topic`) — no content tag of another module. What is
 * visible is the list's base, so no filter brings back a topic the caller may not see: a
 * global topic (`contentcontainer_id` NULL, the one kind {@see Topic::convertToGlobal()} makes),
 * the topics of every space the space list shows the caller as a picker
 * ({@see self::readableContainers()}: visibility, blocked spaces and what modules restrict for
 * the picker purpose; archived spaces included — their content keeps its topics) and those of
 * the caller's own profile. Other users' profile topics are never listed: whether a profile's
 * content may be read depends on friendship and profile visibility, which has no query form to
 * build a list on, and a profile's topics are its owner's own labels.
 *
 * Without a user (a guest) the same rules give the global topics and those of the spaces the
 * space list shows without a user; the API endpoint itself is for logged-in users only.
 *
 * @since 1.20
 */
class TopicList extends FilterableList
{
    /**
     * The longest search (`q`) the list takes — the length of a topic's name.
     */
    public const MAX_SEARCH_LENGTH = 100;

    /**
     * The most topic ids (`ids`) the list takes: a picker's page — what a topic filter's value
     * may name ({@see \humhub\modules\topic\components\listing\TopicFilter::MAX_IDS}) and
     * `GET /api/v2/topic/picker` resolves on one page.
     */
    public const MAX_IDS = 20;

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            new SearchFilter('q', columns: ['content_tag.name'], maxLength: self::MAX_SEARCH_LENGTH),
            new IdsFilter('ids', column: 'content_tag.id', max: self::MAX_IDS),
            new TopicContainerFilter('containerId'),
        ];
    }

    /**
     * @inheritdoc
     * @return QueryListBuilder over the {@see Topic} query
     */
    public function build(array $params, ListContext $context): QueryListBuilder
    {
        /** @var QueryListBuilder $builder */
        $builder = parent::build($params, $context);

        return $builder;
    }

    /**
     * @inheritdoc
     */
    protected function createBuilder(ListContext $context): ListBuilder
    {
        // Topic::find() narrows to the topic module's tags; its own order (global first, the
        // sort order) is the topic menu's, not a list's.
        $query = Topic::find()->orderBy(null);
        $query->andWhere(['or',
            ['content_tag.contentcontainer_id' => null],
            self::readableContainers('content_tag.contentcontainer_id', $context->user),
        ]);

        return new QueryListBuilder($query);
    }

    /**
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var QueryListBuilder $builder */
        $builder->query()->orderBy(['content_tag.name' => SORT_ASC, 'content_tag.id' => SORT_ASC]);
    }

    /**
     * The condition "`$column` is a content container whose topics `$user` may see": a space the
     * space list shows them as a picker — built as that list (scope `all`, and the archived
     * spaces the scope leaves out), so its rules are never copied — or their own profile.
     *
     * The same rule decides which `containerId` the list takes ({@see TopicContainerFilter}).
     */
    public static function readableContainers(string $column, ?User $user): array
    {
        $context = new ListContext($user, SpaceList::PURPOSE_PICKER);
        $spaces = static fn(string $scope) => (new SpaceList())
            ->build(['scope' => $scope], $context)
            ->query()
            ->select('space.contentcontainer_id')
            ->orderBy(null);

        // Built twice: the `all` scope leaves archived spaces out (as the directory does) and no
        // scope lists both, yet an archived space's content — its files, say — keeps its topics.
        $condition = ['or',
            ['in', $column, $spaces(SpaceList::SCOPE_ALL)],
            ['in', $column, $spaces(SpaceList::SCOPE_ARCHIVED)],
        ];

        if ($user !== null) {
            $condition[] = [$column => $user->contentcontainer_id];
        }

        return $condition;
    }
}
