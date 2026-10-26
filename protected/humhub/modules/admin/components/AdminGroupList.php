<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\admin\models\GroupSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use Yii;
use yii\db\Expression;

/**
 * The groups of the administration's group table ({@see GroupTable}, `GET /api/v2/admin/group`),
 * narrowed by a search (`q`: name or description) and the `type` (normal or subgroup), in the
 * order of a column (`sort`: `name`, `members`, `managers`, `type`, each also descending with a
 * leading `-`; without, the normal groups before the subgroups, then by name). Without the
 * permission to manage groups, a group manager sees the groups they manage.
 *
 * @since 1.20
 */
class AdminGroupList extends FilterableList
{
    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = ['name', 'members', 'managers', 'type'];

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            new SearchFilter(
                'q',
                apply: static fn(QueryListBuilder $list, string $keywords) => $list->query()->andWhere([
                    'OR',
                    ['like', 'grp.name', $keywords],
                    ['like', 'grp.description', $keywords],
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Search'),
                    'placeholder' => Yii::t('AdminModule.user', 'Name or description'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'type',
                values: EditGroupForm::getTypeOptions(),
                apply: static fn(QueryListBuilder $list, string $type) => $list->query()->andWhere(
                    $type === EditGroupForm::TYPE_NORMAL ? ['grp.parent_group_id' => null] : ['not', ['grp.parent_group_id' => null]],
                ),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Type'),
                    'sortOrder' => 200,
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
        foreach (self::SORT_COLUMNS as $key) {
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
        // `group` is a reserved word: the table goes by `grp`
        $count = static fn(bool $managers) => GroupUser::find()
            ->select('COUNT(*)')
            ->where('[[group_user.group_id]] = [[grp.id]]')
            ->andFilterWhere(['group_user.is_group_manager' => $managers ? 1 : null]);

        $query = Group::find()->alias('grp')->select([
            'grp.*',
            'members' => $count(false),
            'managers' => $count(true),
        ]);

        if (!Yii::$app->user->can(ManageGroups::class)) {
            $managed = Group::find()
                ->select(Group::tableName() . '.id')
                ->leftJoin(GroupUser::tableName(), GroupUser::tableName() . '.group_id = ' . Group::tableName() . '.id')
                ->where(GroupSearch::getGroupManagerQueryCondition());
            $query->andWhere(['grp.id' => $managed]);
        }

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

        $key = ltrim((string)$sort, '-');
        if (!in_array($key, self::SORT_COLUMNS, true)) {
            $builder->query()->orderBy([new Expression('[[grp.parent_group_id]] IS NOT NULL'), 'grp.name' => SORT_ASC]);
            return;
        }

        $columns = match ($key) {
            'name' => ['grp.name'],
            'type' => [new Expression('[[grp.parent_group_id]] IS NOT NULL')],
            default => [$key],
        };
        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'grp.id');
    }
}
