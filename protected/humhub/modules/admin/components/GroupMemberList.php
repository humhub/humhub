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
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use Yii;

/**
 * The members of a group in the administration's member table ({@see GroupMemberTable},
 * `GET /api/v2/admin/group/<id>/member`): the {@see GroupUser} records of the group's users
 * (without deleted accounts), narrowed by a search (`q`: name, e-mail, username or id) and the
 * `role` (`manager` or `member`), in the order of a column (`sort`: `name`, `role`,
 * `memberSince`, each also descending with a leading `-`; without, the managers first, then by
 * name).
 *
 * @since 1.20
 */
class GroupMemberList extends FilterableList
{
    public const ROLE_MANAGER = 'manager';
    public const ROLE_MEMBER = 'member';

    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = [
        'name' => ['profile.lastname', 'profile.firstname'],
        'role' => ['group_user.is_group_manager'],
        'memberSince' => ['group_user.created_at'],
    ];

    /**
     * @var Group the group whose members are listed
     */
    public Group $group;

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
                    ['=', 'user.id', $keywords],
                    ['like', 'user.username', $keywords],
                    ['like', 'user.email', $keywords],
                    ['like', 'profile.firstname', $keywords],
                    ['like', 'profile.lastname', $keywords],
                    ['like', 'concat(profile.firstname, " ", profile.lastname)', $keywords],
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Search'),
                    'placeholder' => Yii::t('AdminModule.user', 'Name or email'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'role',
                values: self::roles(),
                apply: static fn(QueryListBuilder $list, string $role) => $list->query()->andWhere([
                    'group_user.is_group_manager' => $role === self::ROLE_MANAGER ? 1 : 0,
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Role'),
                    'sortOrder' => 200,
                ],
            ),
        ];
    }

    /**
     * @return array<string, string> the roles' titles by value
     */
    public static function roles(): array
    {
        return [
            self::ROLE_MANAGER => Yii::t('AdminModule.user', 'Manager'),
            self::ROLE_MEMBER => Yii::t('AdminModule.user', 'Member'),
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
        return new QueryListBuilder(GroupUser::find()
            ->joinWith(['user', 'user.profile'])
            ->where(['group_user.group_id' => $this->group->id])
            ->andWhere(['user.status' => [User::STATUS_ENABLED, User::STATUS_DEACTIVATED]]));
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
            $builder->query()->orderBy([
                'group_user.is_group_manager' => SORT_DESC,
                'profile.lastname' => SORT_ASC,
                'profile.firstname' => SORT_ASC,
                'group_user.id' => SORT_ASC,
            ]);
            return;
        }

        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'group_user.id');
    }
}
