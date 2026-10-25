<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\FilterableList;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\user\models\User;
use humhub\modules\user\source\UserSourceCollection;
use Yii;

/**
 * The administration's user table (Administration › Users, `admin\widgets\UserTable`, rows from
 * `GET /api/v2/admin/user`): the users of {@see AdminUserList} with their name, groups, last login
 * and registration, and the actions the viewer may take on each — those the user list's action
 * column offered before (edit, enable / disable, delete, impersonate, view profile).
 *
 * Modules add, replace or remove columns and actions on {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class UserTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new AdminUserList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        $columns = [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('UserModule.base', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('groups', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.user', 'Groups'), priority: 2, filter: 'group', sortOrder: 200),
            new ColumnDefinition('lastLogin', ColumnDefinition::TYPE_DATE, Yii::t('AdminModule.user', 'Last login'), priority: 1, sort: 'lastLogin', sortOrder: 300),
            new ColumnDefinition('createdAt', ColumnDefinition::TYPE_DATE, Yii::t('AdminModule.user', 'Registered'), priority: 3, sort: 'createdAt', sortOrder: 400),
        ];

        // The source of an account (e.g. LDAP), as the name column showed it to administrators
        if (Yii::$app->user->isAdmin()) {
            $columns[] = new ColumnDefinition('source', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.user', 'Source'), priority: 3, sortOrder: 250);
        }

        return $columns;
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        return [
            new RowAction(
                'edit',
                Yii::t('base', 'Edit'),
                RowAction::KIND_LINK,
                static fn(User $user) => ['/admin/user/edit', 'id' => $user->id],
                icon: 'pencil',
                visible: static fn(User $user) => $user->status != User::STATUS_SOFT_DELETED && self::canManage($user),
                sortOrder: 100,
            ),
            new RowAction(
                'enable',
                Yii::t('AdminModule.user', 'Enable'),
                RowAction::KIND_REQUEST,
                static fn(User $user) => ['/admin/api/user/enable', 'id' => $user->id],
                confirm: Yii::t('AdminModule.user', 'Are you really sure that you want to enable this user?'),
                visible: static fn(User $user) => $user->status == User::STATUS_DEACTIVATED && self::canManage($user),
                sortOrder: 200,
            ),
            new RowAction(
                'disable',
                Yii::t('AdminModule.user', 'Disable'),
                RowAction::KIND_REQUEST,
                static fn(User $user) => ['/admin/api/user/disable', 'id' => $user->id],
                confirm: Yii::t('AdminModule.user', 'Are you really sure that you want to disable this user?'),
                visible: static fn(User $user) => $user->status == User::STATUS_ENABLED && self::canManage($user),
                sortOrder: 200,
            ),
            new RowAction(
                'impersonate',
                Yii::t('AdminModule.user', 'Impersonate'),
                RowAction::KIND_POST,
                static fn(User $user) => ['/admin/user/impersonate', 'id' => $user->id],
                confirm: Yii::t('AdminModule.user', 'Are you really sure that you want to impersonate this user?'),
                visible: static fn(User $user) => $user->status == User::STATUS_ENABLED
                    && Yii::$app->user->impersonation->canStart($user),
                sortOrder: 300,
            ),
            new RowAction(
                'profile',
                Yii::t('AdminModule.user', 'View profile'),
                RowAction::KIND_LINK,
                static fn(User $user) => $user->getUrl(),
                visible: static fn(User $user) => $user->status == User::STATUS_ENABLED,
                sortOrder: 400,
            ),
            // The confirmation is the delete page's own form.
            new RowAction(
                'delete',
                Yii::t('base', 'Delete'),
                RowAction::KIND_LINK,
                static fn(User $user) => ['/admin/user/delete', 'id' => $user->id],
                destructive: true,
                visible: static fn(User $user) => $user->status != User::STATUS_SOFT_DELETED
                    && self::canManage($user) && !$user->isCurrentUser(),
                sortOrder: 100,
            ),
            new RowAction(
                'delete-permanently',
                Yii::t('AdminModule.user', 'Permanently delete'),
                RowAction::KIND_LINK,
                static fn(User $user) => ['/admin/user/delete', 'id' => $user->id],
                destructive: true,
                visible: static fn(User $user) => $user->status == User::STATUS_SOFT_DELETED,
                sortOrder: 100,
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var User $model */
        return [
            'name' => [
                'text' => $model->displayName,
                'subtitle' => $model->email,
                'image' => $model->getProfileImage()->getUrl(),
                'url' => $model->status == User::STATUS_ENABLED ? $model->getUrl() : null,
            ],
            'groups' => array_map(static fn($group) => ['text' => $group->name, 'value' => (string)$group->id], $model->groups),
            'lastLogin' => $model->last_login
                ? self::dateCell($model->last_login)
                : ['text' => Yii::t('AdminModule.user', 'never')],
            'createdAt' => $model->created_at ? self::dateCell($model->created_at) : null,
            'source' => [['text' => self::sourceTitle($model->user_source)]],
        ];
    }

    /**
     * A user the viewer may manage: anyone, except a System Administrator for a viewer who is
     * none (the web actions answer 403 then).
     */
    public static function canManage(User $user): bool
    {
        return Yii::$app->user->isAdmin() || !$user->isSystemAdmin();
    }

    private static function dateCell(string $value): array
    {
        return [
            'value' => Yii::$app->formatter->asDatetime($value, 'php:c'),
            'text' => Yii::$app->formatter->asDate($value),
            'title' => Yii::$app->formatter->asDatetime($value),
        ];
    }

    private static function sourceTitle(?string $source): string
    {
        /** @var UserSourceCollection $collection */
        $collection = Yii::$app->userSourceCollection;

        return $source !== null && $collection->hasUserSource($source)
            ? $collection->getUserSource($source)->getTitle()
            : (string)$source;
    }
}
