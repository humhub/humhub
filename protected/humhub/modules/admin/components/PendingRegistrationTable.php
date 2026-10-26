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
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\Invite;
use Yii;

/**
 * The pending registrations (Administration › Users › Pending registrations,
 * `admin\widgets\PendingRegistrationTable`, rows from `GET /api/v2/admin/invite`): the e-mail
 * address (with the name of a sign-up), who invited, the source and when, with "Resend
 * invitation" and "Delete", both also in bulk.
 *
 * Created through the DI container, so a module may replace it or change columns and actions on
 * {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class PendingRegistrationTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new PendingRegistrationList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('email', ColumnDefinition::TYPE_MAIN, Yii::t('AdminModule.user', 'Email'), sort: 'email', sortOrder: 100),
            new ColumnDefinition('invitedBy', ColumnDefinition::TYPE_TEXT, Yii::t('AdminModule.user', 'Invited by'), priority: 2, sortOrder: 200),
            new ColumnDefinition('source', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.user', 'Source'), priority: 1, sort: 'source', filter: 'source', sortOrder: 300),
            new ColumnDefinition('language', ColumnDefinition::TYPE_TEXT, Yii::t('AdminModule.user', 'Language'), priority: 3, sortOrder: 350, visible: false, narrow: true),
            new ColumnDefinition('createdAt', ColumnDefinition::TYPE_DATE, Yii::t('AdminModule.user', 'Created'), priority: 2, sort: 'createdAt', sortOrder: 400),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        return [
            new RowAction(
                'resend',
                Yii::t('AdminModule.user', 'Resend invitation'),
                RowAction::KIND_REQUEST,
                static fn(Invite $invite) => ['/admin/api/invite/resend', 'id' => $invite->id],
                icon: 'send',
                confirm: Yii::t('AdminModule.user', 'Resend invitation?'),
                sortOrder: 100,
                bulkUrl: ['/admin/api/invite/bulk', 'action' => 'resend'],
            ),
            new RowAction(
                'delete',
                Yii::t('base', 'Delete'),
                RowAction::KIND_REQUEST,
                static fn(Invite $invite) => ['/admin/api/invite/delete', 'id' => $invite->id],
                icon: 'trash',
                confirm: Yii::t('AdminModule.user', 'Delete pending registrations?'),
                destructive: true,
                sortOrder: 100,
                bulkUrl: ['/admin/api/invite/bulk', 'action' => 'delete'],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Invite $model */
        $name = trim($model->firstname . ' ' . $model->lastname);
        $sources = $model->getAllowedSources();

        return [
            'email' => ['text' => $model->email, 'subtitle' => $name !== '' ? $name : null],
            'invitedBy' => $model->originator !== null ? ['text' => $model->originator->displayName] : null,
            'source' => [['text' => $sources[$model->source] ?? $model->source, 'value' => $model->source]],
            'language' => $model->language ? ['text' => $model->language] : null,
            'createdAt' => $model->created_at ? [
                'value' => Yii::$app->formatter->asDatetime($model->created_at, 'php:c'),
                'text' => Yii::$app->formatter->asDate($model->created_at),
                'title' => Yii::$app->formatter->asDatetime($model->created_at),
            ] : null,
        ];
    }

    /**
     * Whether the viewer may see the pending registrations: those who may manage users or groups.
     */
    public function canAccess(): bool
    {
        return Yii::$app->user->can([ManageUsers::class, ManageGroups::class]);
    }
}
