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
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\models\Group;
use Yii;
use yii\helpers\Url;

/**
 * The administration's group table (Administration › Users › Groups): the columns, row actions
 * and the list ({@see AdminGroupList}) of `admin\widgets\GroupTable` and
 * `GET /api/v2/admin/group`. Created through the DI container, so a module may replace it.
 *
 * @since 1.20
 */
class GroupTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new AdminGroupList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('AdminModule.user', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('members', ColumnDefinition::TYPE_NUMBER, Yii::t('AdminModule.user', 'Members'), priority: 1, sort: 'members', sortOrder: 200),
            new ColumnDefinition('managers', ColumnDefinition::TYPE_NUMBER, Yii::t('AdminModule.user', 'Managers'), priority: 2, sort: 'managers', sortOrder: 300),
            new ColumnDefinition('type', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.user', 'Type'), priority: 1, sort: 'type', filter: 'type', sortOrder: 400),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        return [
            new RowAction(
                'edit',
                Yii::t('AdminModule.user', 'Settings'),
                RowAction::KIND_LINK,
                static fn(Group $group) => ['/admin/group/edit', 'id' => $group->id],
                icon: 'pencil',
                visible: fn(Group $group) => $this->canManage($group),
                sortOrder: 100,
            ),
            new RowAction(
                'members',
                Yii::t('AdminModule.user', 'Members'),
                RowAction::KIND_LINK,
                static fn(Group $group) => ['/admin/group/manage-group-users', 'id' => $group->id],
                icon: 'users',
                visible: fn(Group $group) => $this->canManage($group),
                sortOrder: 200,
            ),
            new RowAction(
                'permissions',
                Yii::t('AdminModule.user', 'Permissions'),
                RowAction::KIND_LINK,
                static fn(Group $group) => ['/admin/group/manage-permissions', 'id' => $group->id],
                icon: 'lock',
                visible: fn(Group $group) => $this->canManage($group) && Yii::$app->user->can(ManageGroups::class),
                sortOrder: 300,
            ),
            new RowAction(
                'delete',
                Yii::t('base', 'Delete'),
                RowAction::KIND_POST,
                static fn(Group $group) => ['/admin/group/delete', 'id' => $group->id],
                icon: 'trash',
                confirm: Yii::t('AdminModule.user', 'Are you really sure? Users who are not assigned to another group are automatically assigned to the default group.'),
                destructive: true,
                visible: fn(Group $group) => $this->canManage($group) && $group->canDelete(),
                sortOrder: 100,
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Group $model */
        // Yii::t for the names and descriptions of the default groups
        return [
            'name' => [
                'text' => Yii::t('AdminModule.base', $model->name),
                'subtitle' => $model->description ? Yii::t('AdminModule.base', $model->description) : null,
                'url' => $this->canManage($model) ? Url::to(['/admin/group/edit', 'id' => $model->id]) : null,
            ],
            'members' => ['text' => (string)$model->getGroupUsers()->count()],
            'managers' => ['text' => (string)$model->getManager()->count()],
            'type' => array_values(array_filter([
                [
                    'text' => $model->getTypeTitle(),
                    'value' => $model->parent_group_id === null ? EditGroupForm::TYPE_NORMAL : EditGroupForm::TYPE_SUBGROUP,
                ],
                $model->is_default_group ? ['text' => Yii::t('AdminModule.user', 'Default'), 'tone' => 'success'] : null,
                $model->is_protected ? ['text' => Yii::t('AdminModule.user', 'Protected'), 'tone' => 'warning'] : null,
            ])),
        ];
    }

    /**
     * Whether the viewer may see the table: administrators, those who may manage groups and group
     * managers (who see their groups), as the web group administration.
     */
    public function canAccess(): bool
    {
        return Yii::$app->user->isAdmin()
            || Yii::$app->user->can(ManageGroups::class)
            || Yii::$app->user->isGroupManager();
    }

    /**
     * A group the viewer may manage: settings, members, delete. The administrators' group only
     * for System Administrators.
     */
    public function canManage(Group $group): bool
    {
        return $group->canManage() && (!$group->is_admin_group || Yii::$app->user->isAdmin());
    }
}
