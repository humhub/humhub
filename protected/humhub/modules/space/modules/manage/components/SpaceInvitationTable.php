<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\components;

use humhub\components\listing\FilterableList;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * The open invitations of a space (Space › Settings › Members › Pending invites,
 * `manage\widgets\SpaceInvitationTable`, rows from `GET /api/v2/space/<id>/invitation`): the
 * invited user, who invited them and when, with "Cancel invitation" (also in bulk).
 *
 * A row is a {@see Membership} of the status invited; its id is the user's id. Created through
 * the DI container with the space, so a module may replace it or change columns and actions on
 * {@see self::EVENT_INIT}.
 *
 * @since 1.20
 */
class SpaceInvitationTable extends DataTable
{
    /**
     * @var Space the space whose invitations are listed
     */
    public Space $space;

    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new SpaceMemberList(['space' => $this->space, 'status' => Membership::STATUS_INVITED]);
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('SpaceModule.manage', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('invitedBy', ColumnDefinition::TYPE_TEXT, Yii::t('SpaceModule.manage', 'Invited by'), priority: 2, sortOrder: 200),
            new ColumnDefinition('invitedAt', ColumnDefinition::TYPE_DATE, Yii::t('SpaceModule.manage', 'Invited'), priority: 1, sort: 'memberSince', sortOrder: 300),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        return [
            new RowAction(
                'view',
                Yii::t('SpaceModule.manage', 'View profile'),
                RowAction::KIND_LINK,
                static fn(Membership $membership) => $membership->user->getUrl(),
                icon: 'eye',
                visible: static fn(Membership $membership) => (int)$membership->user->status === User::STATUS_ENABLED,
                sortOrder: 100,
            ),
            new RowAction(
                'cancel',
                Yii::t('SpaceModule.manage', 'Cancel invitation'),
                RowAction::KIND_REQUEST,
                fn(Membership $membership) => ['/space/api/invitation/cancel', 'id' => $this->space->id, 'userId' => $membership->user_id],
                icon: 'x',
                confirm: Yii::t('SpaceModule.manage', 'Cancel the invitation?'),
                destructive: true,
                sortOrder: 100,
                bulkUrl: ['/space/api/invitation/bulk-cancel', 'id' => $this->space->id],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Membership $model */
        return [
            'name' => SpaceMemberTable::nameCell($model->user),
            'invitedBy' => $model->originator !== null ? ['text' => $model->originator->displayName] : null,
            'invitedAt' => $model->created_at ? SpaceMemberTable::dateCell($model->created_at) : null,
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
     * Whether the viewer may see the invitations: the space's administrators.
     */
    public function canAccess(): bool
    {
        return (bool)$this->space->isAdmin();
    }
}
