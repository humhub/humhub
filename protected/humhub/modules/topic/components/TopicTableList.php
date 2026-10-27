<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\components;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\topic\models\Topic;
use Yii;

/**
 * The topics of a topic table ({@see TopicTable}): the global topics, or those of a space or a
 * user, narrowed by a search (`q`: name), in the order of a column (`sort`: `name`, `sortOrder`,
 * `contents`, each also descending with a leading `-`; without, by sort order, then by name).
 * Unlike {@see TopicList}, the topics to manage, not those the caller may see.
 *
 * @since 1.20
 */
class TopicTableList extends FilterableList
{
    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = [
        'name' => ['content_tag.name'],
        'sortOrder' => ['content_tag.sort_order'],
        'contents' => ['contents'],
    ];

    /**
     * @var ContentContainerActiveRecord|null the space or user whose topics are listed; the
     * global topics without
     */
    public ?ContentContainerActiveRecord $container = null;

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            new SearchFilter(
                'q',
                apply: static fn(QueryListBuilder $list, string $keywords) => $list->query()->andWhere(['like', 'content_tag.name', $keywords]),
                definition: [
                    'label' => Yii::t('TopicModule.base', 'Search'),
                    'placeholder' => Yii::t('TopicModule.base', 'Name'),
                    'sortOrder' => 100,
                ],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function sorts(): array
    {
        // Accepted, not offered in a sort select: the table's column headers sort.
        $sorts = [];
        foreach (array_keys(self::SORT_COLUMNS) as $key) {
            $sorts[$key] = null;
            $sorts['-' . $key] = null;
        }

        return $sorts;
    }

    /**
     * @inheritdoc
     */
    protected function createBuilder(ListContext $context): ListBuilder
    {
        $contents = ContentTagRelation::find()
            ->select('COUNT(*)')
            ->where('[[content_tag_relation.tag_id]] = [[content_tag.id]]');

        $query = Topic::find()
            ->addSelect(['contents' => $contents])
            ->andWhere(['content_tag.contentcontainer_id' => $this->container?->contentcontainer_id]);

        return new QueryListBuilder($query);
    }

    /**
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var QueryListBuilder $builder */
        $sort = $this->value($values, self::SORT_PARAM);
        if ($sort !== null && $this->applySortCallback($builder, $sort, $context)) {
            return;
        }

        $columns = self::SORT_COLUMNS[ltrim((string)$sort, '-')] ?? null;
        if ($columns === null) {
            $builder->query()->orderBy(['content_tag.sort_order' => SORT_ASC, 'content_tag.name' => SORT_ASC, 'content_tag.id' => SORT_ASC]);
            return;
        }

        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'content_tag.id');
    }
}
