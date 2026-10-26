<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\components;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\db\Expression;

/**
 * The members of a space in its member table ({@see SpaceMemberTable},
 * `GET /api/v2/space/<id>/member`): the {@see Membership} records of the space's members
 * (without deleted accounts; the invited users or the applicants with {@see self::$status}), narrowed by a search (`q`: name, e-mail,
 * username or id) and the `role` (`admin`, `moderator` or `member`), in the order of a column
 * (`sort`: `name`, `role`, `memberSince`, `lastVisit`, each also descending with a leading `-`;
 * without, the administrators first, then the moderators and the members, each by name).
 *
 * @since 1.20
 */
class SpaceMemberList extends FilterableList
{
    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = ['name', 'role', 'memberSince', 'lastVisit'];

    /**
     * @var Space the space whose members are listed
     */
    public Space $space;

    /**
     * @var int the memberships listed: the members (default), the invited users
     * ({@see Membership::STATUS_INVITED}) or the applicants ({@see Membership::STATUS_APPLICANT})
     */
    public int $status = Membership::STATUS_MEMBER;

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        $filters = [
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
                    'label' => Yii::t('SpaceModule.manage', 'Search'),
                    'placeholder' => Yii::t('SpaceModule.manage', 'Name or email'),
                    'sortOrder' => 100,
                ],
            ),
        ];

        // A role only the members have
        if ($this->status !== Membership::STATUS_MEMBER) {
            return $filters;
        }

        $filters[] = new EnumFilter(
            'role',
            values: self::roles(),
            apply: static fn(QueryListBuilder $list, string $role) => $list->query()->andWhere(['space_membership.group_id' => $role]),
            definition: [
                'label' => Yii::t('SpaceModule.manage', 'Role'),
                'sortOrder' => 200,
            ],
        );

        return $filters;
    }

    /**
     * @return array<string, string> the roles a member may have, their titles by value
     */
    public static function roles(): array
    {
        return [
            Space::USERGROUP_ADMIN => Yii::t('SpaceModule.manage', 'Administrator'),
            Space::USERGROUP_MODERATOR => Yii::t('SpaceModule.manage', 'Moderator'),
            Space::USERGROUP_MEMBER => Yii::t('SpaceModule.manage', 'Member'),
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
        return new QueryListBuilder(Membership::find()
            ->joinWith(['user', 'user.profile'])
            ->where([
                'space_membership.space_id' => $this->space->id,
                'space_membership.status' => $this->status,
            ])
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

        // The roles in their order of rank
        $rank = new Expression('FIELD([[space_membership.group_id]], :admin, :moderator, :member)', [
            ':admin' => Space::USERGROUP_ADMIN,
            ':moderator' => Space::USERGROUP_MODERATOR,
            ':member' => Space::USERGROUP_MEMBER,
        ]);

        $key = ltrim((string)$sort, '-');
        if (!in_array($key, self::SORT_COLUMNS, true)) {
            $builder->query()->orderBy([$rank, 'profile.lastname' => SORT_ASC, 'profile.firstname' => SORT_ASC, 'space_membership.user_id' => SORT_ASC]);
            return;
        }

        $columns = match ($key) {
            'name' => ['profile.lastname', 'profile.firstname'],
            'role' => [$rank],
            'memberSince' => ['space_membership.created_at'],
            'lastVisit' => ['space_membership.last_visit'],
        };
        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'space_membership.user_id');
    }
}
