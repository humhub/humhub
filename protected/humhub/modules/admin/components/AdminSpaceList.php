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
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Yii;

/**
 * The spaces of the administration's space table ({@see SpaceTable}, `GET /api/v2/admin/space`):
 * every space, whatever its visibility, narrowed by a search (`q`: name, description, or the
 * owner's name, username or e-mail), the `visibility` (public or private) and the `status`
 * (active or archived), in the order of a column (`sort`: `name`, `members`, `owner`,
 * `visibility`, `status`, `createdAt`, each also descending with a leading `-`; without, the
 * spaces' own sort order, then by name).
 *
 * @since 1.20
 */
class AdminSpaceList extends FilterableList
{
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = [
        'name' => ['space.name'],
        'members' => ['members'],
        'owner' => ['profile.lastname', 'profile.firstname'],
        'visibility' => ['space.visibility'],
        'status' => ['space.status'],
        'createdAt' => ['space.created_at'],
    ];

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
                    ['like', 'space.name', $keywords],
                    ['like', 'space.description', $keywords],
                    ['like', 'user.username', $keywords],
                    ['like', 'user.email', $keywords],
                    ['like', 'profile.firstname', $keywords],
                    ['like', 'profile.lastname', $keywords],
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.space', 'Search'),
                    'placeholder' => Yii::t('AdminModule.space', 'Name, description or owner'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'visibility',
                values: [
                    self::VISIBILITY_PUBLIC => Yii::t('SpaceModule.base', 'Public'),
                    self::VISIBILITY_PRIVATE => Yii::t('SpaceModule.base', 'Private'),
                ],
                apply: static fn(QueryListBuilder $list, string $visibility) => $list->query()->andWhere([
                    'space.visibility' => $visibility === self::VISIBILITY_PRIVATE
                        ? Space::VISIBILITY_NONE
                        : [Space::VISIBILITY_REGISTERED_ONLY, Space::VISIBILITY_ALL],
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.space', 'Visibility'),
                    'sortOrder' => 200,
                ],
            ),
            new EnumFilter(
                'status',
                values: [
                    self::STATUS_ACTIVE => Yii::t('AdminModule.space', 'Active'),
                    self::STATUS_ARCHIVED => Yii::t('SpaceModule.base', 'Archived'),
                ],
                apply: static fn(QueryListBuilder $list, string $status) => $list->query()->andWhere([
                    'space.status' => $status === self::STATUS_ARCHIVED ? Space::STATUS_ARCHIVED : Space::STATUS_ENABLED,
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.space', 'Status'),
                    'sortOrder' => 300,
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
        $members = Membership::find()
            ->select('COUNT(*)')
            ->where('[[space_membership.space_id]] = [[space.id]]')
            ->andWhere(['space_membership.status' => Membership::STATUS_MEMBER]);

        return new QueryListBuilder(Space::find()
            ->joinWith(['ownerUser', 'ownerUser.profile'])
            ->select(['space.*', 'members' => $members]));
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
            $builder->query()->orderBy(['space.sort_order' => SORT_ASC, 'space.name' => SORT_ASC, 'space.id' => SORT_ASC]);
            return;
        }

        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'space.id');
    }
}
