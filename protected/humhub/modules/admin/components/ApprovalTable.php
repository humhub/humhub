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
use humhub\modules\admin\models\forms\ApproveUserForm;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use Yii;

/**
 * The registrations awaiting approval (Administration › Users › Pending approvals,
 * `admin\widgets\ApprovalTable`, rows from `GET /api/v2/admin/approval`): name and e-mail, the
 * groups, who invited and when, on request the messages sent and each profile field asked at
 * the registration; with edit, and approve, send a message and decline (each a dialog with the
 * message to send), in bulk too (approve and decline with the default texts, a message written in
 * a dialog).
 *
 * Created through the DI container, so a module may replace it or change columns and actions on
 * {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class ApprovalTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new AdminApprovalList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        $columns = [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('UserModule.base', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('groups', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.user', 'Groups'), priority: 2, sortOrder: 200),
            new ColumnDefinition('invitedBy', ColumnDefinition::TYPE_TEXT, Yii::t('AdminModule.user', 'Invited by'), priority: 3, sortOrder: 300, visible: false),
            new ColumnDefinition('messages', ColumnDefinition::TYPE_NUMBER, Yii::t('AdminModule.user', 'Messages sent'), priority: 3, sortOrder: 350, visible: false, narrow: true),
            new ColumnDefinition('createdAt', ColumnDefinition::TYPE_DATE, Yii::t('AdminModule.user', 'Registered'), priority: 1, sort: 'createdAt', sortOrder: 400),
        ];

        // The profile fields asked at the registration, on request
        $sortOrder = 500;
        foreach (self::registrationFields() as $field) {
            $columns[] = new ColumnDefinition(
                'profile_' . $field->internal_name,
                ColumnDefinition::TYPE_TEXT,
                Yii::t($field->getTranslationCategory(), $field->title),
                priority: 3,
                sortOrder: $sortOrder++,
                value: static function (User $user) use ($field) {
                    $value = trim(strip_tags((string)$field->getUserValue($user, false, false)));
                    return $value === '' ? null : ['text' => $value, 'title' => $value];
                },
                visible: false,
            );
        }

        return $columns;
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        $bulkUrl = static fn(string $action) => ['/admin/api/approval/bulk', 'action' => $action];

        return [
            new RowAction(
                'approve',
                Yii::t('AdminModule.user', 'Approve'),
                RowAction::KIND_MODAL,
                static fn(User $user) => ['/admin/approval/approve', 'id' => $user->id],
                icon: 'check',
                sortOrder: 100,
                bulkUrl: $bulkUrl('approve'),
            ),
            new RowAction(
                'message',
                Yii::t('AdminModule.user', 'Send a message'),
                RowAction::KIND_MODAL,
                static fn(User $user) => ['/admin/approval/send-message', 'id' => $user->id],
                icon: 'send',
                sortOrder: 200,
                // The message to write first
                bulkUrl: ['/admin/approval/bulk-message'],
                bulkModal: true,
            ),
            new RowAction(
                'edit',
                Yii::t('AdminModule.user', 'Edit'),
                RowAction::KIND_LINK,
                static fn(User $user) => ['/admin/user/edit', 'id' => $user->id],
                icon: 'pencil',
                visible: static fn() => Yii::$app->user->can(ManageUsers::class),
                sortOrder: 300,
            ),
            new RowAction(
                'decline',
                Yii::t('AdminModule.user', 'Decline'),
                RowAction::KIND_MODAL,
                static fn(User $user) => ['/admin/approval/decline', 'id' => $user->id],
                icon: 'x',
                destructive: true,
                sortOrder: 100,
                bulkUrl: $bulkUrl('decline'),
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var User $model */
        $messages = ApproveUserForm::getNumberMessageSent($model->id);

        return [
            'name' => [
                'text' => $model->displayName,
                'subtitle' => $model->email,
                'image' => $model->getProfileImage()->getUrl(),
            ],
            'groups' => array_map(static fn($group) => ['text' => $group->name], $model->groups),
            'invitedBy' => $model->originator !== null ? ['text' => $model->originator->displayName] : null,
            'messages' => ['text' => (string)$messages],
            'createdAt' => $model->created_at ? [
                'value' => Yii::$app->formatter->asDatetime($model->created_at, 'php:c'),
                'text' => Yii::$app->formatter->asDate($model->created_at),
                'title' => Yii::$app->formatter->asDatetime($model->created_at),
            ] : null,
        ];
    }

    /**
     * Whether the viewer may see the registrations awaiting approval: those who may manage users
     * (also group managers, for the users of their groups).
     */
    public function canAccess(): bool
    {
        return (bool)Yii::$app->user->getIdentity()?->canManageUsers();
    }

    /**
     * @return ProfileField[] the profile fields asked at the registration, but the name
     */
    private static function registrationFields(): array
    {
        return ProfileField::find()
            ->joinWith('category')
            ->where(['profile_field.show_at_registration' => true])
            ->andWhere(['not', ['profile_field.internal_name' => ['firstname', 'lastname']]])
            ->orderBy(['profile_field_category.sort_order' => SORT_ASC, 'profile_field.sort_order' => SORT_ASC])
            ->all();
    }
}
