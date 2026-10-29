<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
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
use humhub\modules\user\models\User;
use humhub\modules\user\source\UserSourceCollection;
use Yii;
use yii\db\Query;

/**
 * The users of the administration's user table ({@see UserTable}, `GET /api/v2/admin/user`):
 * every account, whatever its visibility, narrowed by a search (`q`: name, e-mail, username or
 * exact id), the `status` (enabled, deactivated or deleted; without, the enabled and
 * deactivated accounts), a `group` and, for System Administrators, a `source`, in the
 * order of a column (`sort`: `name`, `email`, `lastLogin`, `createdAt`, `source`, `status`, each also descending
 * with a leading `-`; without, the newest account first).
 *
 * A module restricting the users an administrator sees (e.g. to the groups they manage) narrows
 * the list on {@see self::EVENT_BUILD}.
 *
 * @since 1.20
 */
class AdminUserList extends FilterableList
{
    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DEACTIVATED = 'deactivated';
    public const STATUS_DELETED = 'deleted';

    private const STATUS_VALUES = [
        self::STATUS_ENABLED => User::STATUS_ENABLED,
        self::STATUS_DEACTIVATED => User::STATUS_DEACTIVATED,
        self::STATUS_DELETED => User::STATUS_SOFT_DELETED,
    ];

    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = [
        'name' => ['profile.lastname', 'profile.firstname'],
        'email' => ['user.email'],
        'lastLogin' => ['user.last_login'],
        'createdAt' => ['user.created_at'],
        'source' => ['user.user_source'],
        'status' => ['user.status'],
    ];

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            new SearchFilter(
                'q',
                apply: static fn(QueryListBuilder $list, string $keywords) => self::search($list, $keywords),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Search'),
                    'placeholder' => Yii::t('AdminModule.user', 'Name, email or username'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'status',
                values: [
                    self::STATUS_ENABLED => Yii::t('AdminModule.user', 'Enabled'),
                    self::STATUS_DEACTIVATED => Yii::t('AdminModule.user', 'Deactivated'),
                    self::STATUS_DELETED => Yii::t('AdminModule.user', 'Deleted'),
                ],
                // Applied by finalize(): without a value, the active and the deactivated accounts.
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Status'),
                    'sortOrder' => 200,
                ],
            ),
            new EnumFilter(
                'group',
                values: $this->groupOptions(),
                apply: static fn(QueryListBuilder $list, string $groupId) => $list->query()
                    ->andWhere(['user.id' => (new Query())
                        ->select('user_id')
                        ->from('group_user')
                        ->where(['group_id' => (int)$groupId])]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Group'),
                    'sortOrder' => 300,
                ],
            ),
            // The source of an account (e.g. LDAP), shown to administrators only like its column
            ...(Yii::$app->user->isAdmin() ? [new EnumFilter(
                'source',
                values: $this->sourceOptions(),
                apply: static fn(QueryListBuilder $list, string $source) => $list->query()
                    ->andWhere(['user.user_source' => $source]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Source'),
                    'sortOrder' => 400,
                ],
            )] : []),
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
        return new QueryListBuilder(User::find()->joinWith('profile'));
    }

    /**
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var QueryListBuilder $builder */
        // The deleted accounts only on request
        $status = $this->value($values, 'status');
        $builder->query()->andWhere(['user.status' => $status !== null
            ? self::STATUS_VALUES[$status]
            : [User::STATUS_ENABLED, User::STATUS_DEACTIVATED]]);

        $sort = $this->value($values, self::SORT_PARAM);
        if ($sort !== null && $this->applySortCallback($builder, $sort, $context)) {
            return;
        }

        $descending = $sort !== null && str_starts_with($sort, '-');
        $columns = self::SORT_COLUMNS[ltrim((string)$sort, '-')] ?? null;

        if ($columns === null) {
            $builder->query()->orderBy(['user.id' => SORT_DESC]);
            return;
        }

        $builder->sortBy($columns, $descending, 'user.id');
    }

    private static function search(QueryListBuilder $list, string $keywords): void
    {
        $list->query()->andWhere([
            'OR',
            ['=', 'user.id', $keywords],
            ['like', 'user.username', $keywords],
            ['like', 'user.email', $keywords],
            ['like', 'profile.firstname', $keywords],
            ['like', 'profile.lastname', $keywords],
            ['like', 'concat(profile.firstname, " ", profile.lastname)', $keywords],
            ['like', 'concat(profile.lastname, " ", profile.firstname)', $keywords],
        ]);
    }

    /**
     * The configured user sources and those accounts still name, by id.
     */
    private function sourceOptions(): array
    {
        /** @var UserSourceCollection $collection */
        $collection = Yii::$app->userSourceCollection;

        $options = [];
        foreach ($collection->getUserSources() as $id => $source) {
            $options[$id] = $source->getTitle();
        }
        foreach (User::find()->select('user_source')->distinct()->column() as $id) {
            if ($id !== null && $id !== '' && !isset($options[$id])) {
                $options[$id] = $id;
            }
        }

        return $options;
    }

    /**
     * @return array<int, string> the group names by id
     */
    private function groupOptions(): array
    {
        $options = [];
        foreach (Group::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all() as $group) {
            $options[$group->id] = $group->name;
        }

        // An EnumFilter needs values; without groups, a value no group has is never valid.
        return $options !== [] ? $options : [0 => null];
    }
}
