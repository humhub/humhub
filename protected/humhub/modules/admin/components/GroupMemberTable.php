<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\FilterableList;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\admin\Module;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use Yii;

/**
 * The members of a group (Administration › Groups › Members, `admin\widgets\GroupMemberTable`,
 * rows from `GET /api/v2/admin/group/<id>/member`): name, role (manager or member) and since
 * when, with the actions the viewer may take on each — view the profile, make or unmake a group
 * manager (those who may manage groups, not in the administrators' group) and remove from the
 * group (those who may change the members, while the user is in another group as well).
 *
 * A row is a {@see GroupUser}; its id is the user's id. Created through the DI container with the
 * group, so a module may replace it or change columns and actions on {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class GroupMemberTable extends DataTable
{
    /**
     * @var Group the group whose members are listed
     */
    public Group $group;

    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new GroupMemberList(['group' => $this->group]);
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('UserModule.base', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('role', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.user', 'Role'), priority: 1, sort: 'role', filter: 'role', sortOrder: 200, narrow: true),
            new ColumnDefinition('memberSince', ColumnDefinition::TYPE_DATE, Yii::t('AdminModule.user', 'Member since'), priority: 2, sort: 'memberSince', sortOrder: 300),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        $member = fn(string $route) => fn(GroupUser $groupUser) => [$route, 'id' => $this->group->id, 'userId' => $groupUser->user_id];

        return [
            new RowAction(
                'view',
                Yii::t('AdminModule.user', 'View profile'),
                RowAction::KIND_LINK,
                static fn(GroupUser $groupUser) => $groupUser->user->getUrl(),
                icon: 'eye',
                visible: static fn(GroupUser $groupUser) => (int)$groupUser->user->status === User::STATUS_ENABLED,
                sortOrder: 100,
            ),
            new RowAction(
                'manager',
                Yii::t('AdminModule.user', 'Make group manager'),
                RowAction::KIND_REQUEST,
                $member('/admin/api/group-member/manager'),
                icon: 'shield',
                confirm: Yii::t('AdminModule.user', 'Make this user a manager of the group?'),
                visible: fn(GroupUser $groupUser) => !$groupUser->is_group_manager && $this->canChangeManagers(),
                sortOrder: 200,
            ),
            new RowAction(
                'member',
                Yii::t('AdminModule.user', 'Remove group manager role'),
                RowAction::KIND_REQUEST,
                $member('/admin/api/group-member/member'),
                icon: 'shield-off',
                confirm: Yii::t('AdminModule.user', 'Take the group manager role from this user?'),
                visible: fn(GroupUser $groupUser) => $groupUser->is_group_manager && $this->canChangeManagers(),
                sortOrder: 200,
            ),
            new RowAction(
                'remove',
                Yii::t('AdminModule.user', 'Remove from group'),
                RowAction::KIND_REQUEST,
                $member('/admin/api/group-member/remove'),
                icon: 'user-minus',
                confirm: Yii::t('AdminModule.user', 'Remove this user from the group?'),
                destructive: true,
                visible: fn(GroupUser $groupUser) => $this->canRemove($groupUser),
                sortOrder: 100,
                bulkUrl: ['/admin/api/group-member/bulk-remove', 'id' => $this->group->id],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var GroupUser $model */
        $user = $model->user;

        return [
            'name' => [
                'text' => $user->displayName,
                'subtitle' => $user->displayNameSub ?: $user->email,
                'image' => $user->getProfileImage()->getUrl(),
                'url' => (int)$user->status === User::STATUS_ENABLED ? $user->getUrl() : null,
            ],
            'role' => [$model->is_group_manager
                ? ['text' => Yii::t('AdminModule.user', 'Manager'), 'value' => GroupMemberList::ROLE_MANAGER, 'tone' => 'info']
                : ['text' => Yii::t('AdminModule.user', 'Member'), 'value' => GroupMemberList::ROLE_MEMBER]],
            'memberSince' => $model->created_at ? [
                'value' => Yii::$app->formatter->asDatetime($model->created_at, 'php:c'),
                'text' => Yii::$app->formatter->asDate($model->created_at),
                'title' => Yii::$app->formatter->asDatetime($model->created_at),
            ] : null,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function rowId($model): int|string
    {
        return (int)$model->user_id;
    }

    /**
     * Whether the viewer may see the members: those who may manage the group (the group
     * administration, or one of its managers).
     */
    public function canAccess(): bool
    {
        return $this->group->canManage();
    }

    /**
     * Whether the viewer may make and unmake group managers: those who may manage groups, not in
     * the administrators' group.
     */
    public function canChangeManagers(): bool
    {
        return !$this->group->is_admin_group && Yii::$app->user->can(ManageGroups::class);
    }

    /**
     * Whether the viewer may add and remove members: the group administration, and the group
     * managers unless `groupManagerCannotModifyMembers` is set.
     */
    public function canModifyMembers(): bool
    {
        if (Yii::$app->user->isAdmin() || Yii::$app->user->can(ManageGroups::class)) {
            return true;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('admin');

        return Yii::$app->user->isGroupManager() && !$module->groupManagerCannotModifyMembers;
    }

    /**
     * Whether the viewer may remove the member: a user stays in at least one group.
     */
    public function canRemove(GroupUser $groupUser): bool
    {
        return $this->canModifyMembers()
            && GroupUser::find()->where(['user_id' => $groupUser->user_id])->count() > 1;
    }
}
